<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Encapsulates a web server to install virtual host into
 */
class WebServer
{
    private $operatingSystemFamily;
    private $documentRoot;
    private $name;
    private $virtualHostsFile;
    private $executablePath;

    /**
     * @param OperatingSystemFamily $operatingSystem
     * @param string $documentRoot
     * @throws \Exception
     */
    public function __construct(int $operatingSystem, string $documentRoot)
    {
        $this->operatingSystemFamily = $operatingSystem;
        $this->documentRoot = $documentRoot;
        $this->setName();
        $this->setExecutablePath();
        $this->setVirtualHostsFile();
    }

    /**
     * Registers virtualhost for current web server
     *
     * @param VirtualHost $virtualHost
     * @return void
     * @throws \Exception
     */
    public function addVirtualHost(VirtualHost $virtualHost)
    {
        $configurationFile = "";
        if ($this->name != "nginx") {
            $configurationFile = dirname(__DIR__, 2) . "/configuration/apache2.conf";
        } else {
            $configurationFile = dirname(__DIR__, 2) . "/configuration/nginx.conf";
        }
        $body = str_replace(
            [
                "(DOCUMENT_ROOT)",
                "(HOSTNAME)",
                "(WEBSITE)",
                "(ENVIRONMENT)"
            ],
            [
                str_replace(DIRECTORY_SEPARATOR, "/", $this->documentRoot),
                $virtualHost->getHostName(),
                $virtualHost->getSiteName(),
                $virtualHost->getDevelopmentEnvironment()
            ],
            file_get_contents($configurationFile)
        );

        // sets development environment for NGINX
        if ($this->name == "nginx") {
            $fpm = new PhpFpm($this->operatingSystemFamily);
            $body = str_replace("(SOCKET_FILE)", $fpm->getSocketFile(), $body);
            $socketConfig = $fpm->getConfigurationFile();
            $configurationFileBody = file_get_contents($socketConfig);
            if (strpos($configurationFileBody, "env[ENVIRONMENT]")===false) {
                file_put_contents(
                    $socketConfig,
                    $configurationFileBody . "\nenv[ENVIRONMENT] = " . $virtualHost->getDevelopmentEnvironment()
                );
                if ($this->operatingSystemFamily == OperatingSystemFamily::LINUX) {
                    $this->executeCommand("systemctl restart ".$fpm->getServiceName());
                } else {
                    $this->executeCommand("brew services restart ".$fpm->getServiceName());
                }
            }
        }

        // writes virtual host
        if (substr_compare($this->virtualHostsFile, "/", -1) === 0) {
            file_put_contents($this->virtualHostsFile, "\n\n" . $body, FILE_APPEND);
        } else {
            $fileName = $this->virtualHostsFile . $virtualHost->getSiteName().".conf";
            file_put_contents($fileName, $body);
            if ($this->name == "apache2") {
                $this->executeCommand("a2ensite " . $virtualHost->getSiteName());
            } else if (strpos($fileName, "/sites-available/")!==false) {
                $this->executeCommand("ln -s " . $fileName . " " . str_replace("/sites-available/", "/sites-enabled/", $fileName));
            }
        }
    }

    /**
     * Restarts current web server (in order to recognize newly created virtualhost)
     *
     * @return void
     */
    public function restart(): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::WINDOWS) {
            if (strpos($this->executablePath, "wamp")!==false) {
                $this->executeCommand($this->executablePath . " -k restart -n wampapache64");
            } else {
                echo "Please restart apache manually in your XAMPP control panel!\n";
            }
        } else if ($this->operatingSystemFamily == OperatingSystemFamily::MAC) {
            if (strpos($this->virtualHostsFile, "/homebrew/")!==false) {
                echo "To restart webserver run this command (as normal user): brew services restart " . $this->name."\n";
            } else if ($this->name == "httpd") {
                $this->executeCommand("apachectl graceful");
            } else {
                $this->executeCommand("nginx -s reload");
            }
        } else {
            $this->executeCommand("systemctl restart " . $this->executablePath);
        }
    }

    /**
     * Sets path to web server executable (if windows) or web server process name (if linux/mac)
     *
     * @return void
     * @throws \Exception
     */
    private function setExecutablePath(): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::WINDOWS) {
            $this->executablePath = str_replace("/", DIRECTORY_SEPARATOR, $this->locate(dirname($this->documentRoot), "httpd.exe"));
            if (!$this->executablePath) {
                throw new \Exception("Web server could not be detected: httpd.exe!");
            }
        } else {
            $this->executablePath = $this->name;
        }
    }

    /**
     * Detects web server process name (apache2/httpd, nginx)
     *
     * @return void
     * @throws \Exception
     */
    private function setName(): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::WINDOWS) {
            $this->name = "apache2";
        } else {
            $results = $this->executeCommand("lsof -i TCP:80");
            if (strpos($results, "nginx")!==false) {
                $this->name = "nginx";
            } else if (strpos($results, "apache2")!==false) {
                $this->name = "apache2";
            } else if (strpos($results, "httpd")!==false) {
                $this->name = "httpd";
            } else {
                throw new \Exception("No known web server is running right now!");
            }
        }
    }

    /**
     * Detects location of generic virtual hosts file (if windows/mac) or folder to write individual virtual hosts into (if linux)
     *
     * @return void
     * @throws \Exception
     */
    private function setVirtualHostsFile(): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::WINDOWS) {
            $baseDir = dirname($this->documentRoot);
            $this->virtualHostsFile = $this->locate($baseDir, "conf/extra/httpd-vhosts.conf");
        } else if ($this->operatingSystemFamily == OperatingSystemFamily::MAC) {
            if ($this->name == "httpd") {
                $this->virtualHostsFile = "/opt/homebrew/etc/httpd/extra/httpd-vhosts.conf";
                if (!file_exists($this->virtualHostsFile)) {
                    $this->virtualHostsFile = "/usr/local/etc/httpd/extra/httpd-vhosts.conf";
                }
            } else if ($this->name == "nginx") {
                $this->virtualHostsFile = "/opt/homebrew/etc/nginx/nginx.conf";
                if (!file_exists($this->virtualHostsFile)) {
                    $this->virtualHostsFile = "/usr/local/etc/nginx/nginx.conf";
                }
            }
        } else {
            if ($this->name == "nginx") {
                $this->virtualHostsFile = "/etc/nginx/sites-available/";
                if (!file_exists($this->virtualHostsFile)) {
                    $this->virtualHostsFile = "/etc/nginx/conf.d/";
                }
            } else if ($this->name == "httpd") {
                $this->virtualHostsFile = "/etc/httpd/conf.d/";
            } else {
                $this->virtualHostsFile = "/etc/apache2/sites-available/";
            }
        }
        if (!$this->virtualHostsFile || !file_exists($this->virtualHostsFile)) {
            throw new \Exception("Virtual host file could not be detected!");
        }
    }

    /**
     * Recursively locates file in folder and returns absolute path
     *
     * @param string $folder
     * @param string $search
     * @return string|null
     */
    private function locate(string $folder, string $search): string|null
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }
            if (is_dir($folder . "/" . $file)) {
                if (file_exists($folder . "/" . $search)) {
                    return $folder . "/" . $search;
                }
                $result = $this->locate($folder . "/" . $file, $search);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }

    /**
     * Executes command in operating system
     *
     * @param string $command
     * @return string
     * @throws \Exception
     */
    private function executeCommand(string $command): string
    {
        $results = (string) shell_exec($command . ($this->operatingSystemFamily != OperatingSystemFamily::WINDOWS ? " &2>1" : ""));
        if (stripos(strtolower($results), "error")!==false) {
            throw new \Exception("Operation ended with error: " . $results);
        }
        return $results;
    }
}