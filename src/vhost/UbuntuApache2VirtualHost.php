<?php
namespace Lucinda\Configurer;

require_once("AbstractVirtualHost.php");

/**
 * Recipe for virtual host creation where:
 * - operating system: ubuntu
 * - web server: apache2
 */
class UbuntuApache2VirtualHost extends AbstractVirtualHost
{
    protected function getVirtualHostFile()
    {
        $configurationFile = "/etc/apache2/sites-available/".$this->siteName.".conf";
        if (file_exists($configurationFile)) {
            throw new Exception("Site already installed");
        }
        return $configurationFile;
    }

    protected function setVirtualHost($virtualHostFile)
    {
        file_put_contents($virtualHostFile, "
<VirtualHost *:80>
    ServerName ".$this->hostName."
    ServerAdmin webmaster@localhost
    DocumentRoot ".$this->documentRoot."/".$this->siteName."
    <Directory ".$this->documentRoot."/".$this->siteName.">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>");
        chdir("/etc/apache2/sites-available");
        exec("a2ensite ".$this->siteName.".conf");
    }

    protected function restartWebServer()
    {
        exec("service apache2 restart");
    }
}
