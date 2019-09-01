<?php
namespace Lucinda\Configurer;

require_once("AbstractVirtualHost.php");

/**
 * Recipe for virtual host creation where:
 * - operating system: windows
 * - web server: apache2
 * - software: wamp, xampp
 */
class WindowsApache2VirtualHost extends AbstractVirtualHost
{
    protected function getVirtualHostFile()
    {
        $baseDir = substr($this->documentRoot, 0, strrpos($this->documentRoot, "/"));
        $virtualHostFile = $this->locate($baseDir, "conf/extra/httpd-vhosts.conf");
        if (!$virtualHostFile) {
            throw new Exception("Virtual host file could not be detected: httpd-vhosts.conf. Search for it manually and add following lines:\r\n".$this->getLines());
        }
        return $virtualHostFile;
    }
    
    private function locate($folder, $search)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }
            if (is_dir($folder."/".$file)) {
                if (file_exists($folder."/".$search)) {
                    return $folder."/".$search;
                }
                $result = $this->locate($folder."/".$file, $search);
                if ($result) {
                    return $result;
                }
            }
        }
    }

    protected function setVirtualHost($virtualHostFile)
    {
        $contents = file_get_contents($virtualHostFile);
        if (strpos($contents, $this->hostName)!==false || strpos($contents, $this->documentRoot."/".$this->siteName)!==false) {
            throw new Exception("Host is already configured");
        }
        file_put_contents($virtualHostFile, $contents."\r\n".$this->getLines());
    }
    
    private function getLines()
    {
        return '
<VirtualHost *:80>
    ServerName '.$this->hostName.'
    DocumentRoot "'.$this->documentRoot."/".$this->siteName.'"
    <Directory "'.$this->documentRoot."/".$this->siteName.'/">
		AllowOverride All
		Require local
    </Directory>
</VirtualHost>';
    }

    protected function restartWebServer()
    {
        $baseDir = substr($this->documentRoot, 0, strrpos($this->documentRoot, "/"));
        $apacheLocation = $this->locate($baseDir, "httpd.exe");
        if (!$apacheLocation) {
            throw new Exception("Web server could not be detected: httpd.exe. Please restart it manually!");
        }
        exec($apacheLocation." -k restart", $results);
        if (empty($results) || strpos($results[0], "error")!==false) {
            echo "Automated web server restart failed. PLEASE RESTART WEB SERVER MANUALLY!\n";
        }
    }
}
