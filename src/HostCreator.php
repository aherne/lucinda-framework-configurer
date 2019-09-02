<?php
namespace Lucinda\Configurer;

require_once("Prompter.php");
require_once("vhost/UbuntuApache2VirtualHost.php");
require_once("vhost/UbuntuNginxVirtualHost.php");
require_once("vhost/WindowsApache2VirtualHost.php");
require_once("vhost/MacApache2VirtualHost.php");

/**
 * Creates host on server machine, based on selected features, operating system and server vendor
 */
class HostCreator
{
    const OS_LINUX = 1;
    const OS_WINDOWS = 2;
    const OS_MAC = 3;
    const HOSTNAME_REGEX = "/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/";

    private $prompt;
    private $hostCreated;

    /**
     * Kick starts host creation based on features selected by user.
     *
     * @param string $siteName Location of site on disk relative to webroot
     * @param string $documentRoot Webroot location
     * @throws Exception If host creation fails.
     */
    public function __construct($installationFolder)
    {
        $operatingSystem = $this->getOperatingSystem();
        
        if (!$this->isRoot($operatingSystem)) {
            throw new \Exception("Only root account can create virtual host!");
        }
                
        $this->prompt = new Prompter();
        
        $siteName = $this->getSiteName(str_replace(dirname($installationFolder)."/","",$installationFolder));
        $documentRoot = $this->getDocumentRoot(dirname($installationFolder), $siteName);        
        $hostName = $this->getHostName($siteName);
        
        $this->setHostsFile($hostName, $operatingSystem);
        $this->setVirtualHost($operatingSystem, $documentRoot, $siteName, $hostName);
        $this->hostCreated = $hostName;
    }

    /**
     * Gets caller's operating system. Supported: Windows & Linux
     *
     * @return int
     * @throws Exception If operating system is not supported.
     */
    private function getOperatingSystem()
    {
        $operatingSystemInfo = php_uname();
        if (strpos($operatingSystemInfo, "Windows")!==false) {
            return self::OS_WINDOWS;
        } elseif (strpos($operatingSystemInfo, "Linux")!==false) {
            return self::OS_LINUX;
        } elseif (strpos($operatingSystemInfo, "Mac")!==false) {
            return self::OS_MAC;
        } else {
            throw new \Exception("Operating system not yet supported for automatic host creation: ".$operatingSystemInfo);
        }
    }
    
    /**
     * Checks if caller is root/administrator, able to install domain on client machine
     *
     * @param integer $operatingSystem
     * @return bool
     */
    private function isRoot($operatingSystem)
    {
        if ($operatingSystem == self::OS_WINDOWS) {
            $result = shell_exec("net session");
            if (strpos($result, "Access is denied.")===false) {
                return true;
            }
        } else {
            $processUser = posix_getpwuid(posix_geteuid());
            if ($processUser['name']=="root") {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sets name of site
     *
     * @return null|string
     */
    private function getSiteName($siteName)
    {
        return $this->prompt->text("[1/4] Write your freshly created site name", $siteName, function ($result) {
            preg_match(self::HOSTNAME_REGEX, $result, $matches);
            return !empty($result);
        });
    }
    
    /**
     * Gets server document root where project will be installed into
     *
     * @param string $documentRoot
     * @param string $siteName
     * @return string
     * @throws Exception If document root doesn't contain site.
     */
    private function getDocumentRoot($documentRoot, $siteName)
    {        
        $documentRoot = $this->prompt->text("[2/4] Write folder http://localhost points to (DOCUMENT ROOT)", $documentRoot, function ($result) {
            $result = str_replace("\\", "/", $result);
            if (!file_exists($result) || !is_dir($result) || !is_writable($result)) {
                $this->prompt->error("Folder must exist and be writable!");
                return false;
            } elseif (strrpos($result, "/")+1 == strlen($result)) {
                $this->prompt->error("Folder must not end with slash/backslash!");
            } else {
                return true;
            }
        });
            
        if (!file_exists($documentRoot.DIRECTORY_SEPARATOR.$siteName)) {
            throw new \Exception("No project was found at ".$documentRoot."/".$siteName);
        }
        
        return $documentRoot;
    }
    
    /**
     * Gets host name about to be created based on user-selected site name.
     *
     * @param string $siteName
     * @return string
     */
    private function getHostName($siteName)
    {
        return $this->prompt->text("[3/4] Write your domain name", "www.".$siteName.".local", function ($result) {
            $matches = array();
            preg_match(self::HOSTNAME_REGEX, $result, $matches);
            return !empty($matches);
        });
    }
    
    /**
     * Saves host name in hosts file based on operating system
     *
     * @param string $hostName
     * @param integer $operatingSystem
     * @return string
     */
    private function setHostsFile($hostName, $operatingSystem)
    {
        $hostsFile = "";
        switch ($operatingSystem) {
            case self::OS_LINUX:
            case self::OS_MAC:
                $hostsFile = "/etc/hosts";
                break;
            case self::OS_WINDOWS:
                $hostsFile = "C:/Windows/System32/drivers/etc/hosts";
                break;
        }
        $hostsFile = $this->prompt->text("[4/4] Write your hosts file location", $hostsFile, function ($result) {
            return file_exists($result);
        });
        $contents = file_get_contents($hostsFile);
        if (strpos($contents, $hostName)===false) {
            file_put_contents($hostsFile, $contents."\n127.0.0.1\t".$hostName);
        }
    }

    /**
     * Sets virtual host on local web server that points to your hostname.
     *
     * @param integer $operatingSystem
     * @param string $documentRoot
     * @param string $siteName
     * @param string $hostName
     * @throws Exception
     */
    private function setVirtualHost($operatingSystem, $documentRoot, $siteName, $hostName)
    {
        switch ($operatingSystem) {
            case self::OS_LINUX:
                $folder = "";
                if (file_exists("/etc/apache2/sites-available")) {
                    new UbuntuApache2VirtualHost($siteName, $documentRoot, $hostName);
                } elseif (file_exists("/etc/nginx/sites-available")) {
                    new UbuntuNginxVirtualHost($siteName, $documentRoot, $hostName);
                } else {
                    throw new \Exception("Apache/NGINX could not be detected! You must add virtual host manually....");
                }
                break;
            case self::OS_WINDOWS:
                new WindowsApache2VirtualHost($siteName, $documentRoot, $hostName);
                break;
            case self::OS_MAC:
                new MacApache2VirtualHost($siteName, $documentRoot, $hostName);
                break;
        }
    }

    /**
     * Gets host name created
     *
     * @return string
     */
    public function getHostCreated()
    {
        return $this->hostCreated;
    }
}
