<?php
namespace Lucinda\Configurer;

/**
 * Recipe for virtual host creation where:
 * - operating system: mac osx
 * - web server: apache2
 */
class MacApache2VirtualHost extends AbstractVirtualHost
{
    protected function getVirtualHostFile()
    {
        $virtualHostsFile = "/etc/apache2/extra/httpd-vhosts.conf";
        if (!file_exists($virtualHostsFile)) {
            throw new \Exception("Virtual hosts file not found: ".$virtualHostsFile);
        }
        if (strpos(file_get_contents($virtualHostsFile), $this->documentRoot."/".$this->siteName)) {
            throw new \Exception("Site already installed");
        }
        
        $apache2ConfigurationFile = "/etc/apache2/httpd.conf";
        if (!file_exists($apache2ConfigurationFile)) {
            throw new \Exception("Apache2 configuration file not found: ".$apache2ConfigurationFile);
        }
        $contents = file_get_contents($apache2ConfigurationFile);
        if (strpos($contents, "#LoadModule rewrite_module libexec/apache2/mod_rewrite.so")) {
            $contents = str_replace($contents, "#LoadModule rewrite_module libexec/apache2/mod_rewrite.so", "LoadModule rewrite_module libexec/apache2/mod_rewrite.so");
        }
        if (strpos($contents, "#LoadModule rewrite_module libexec/apache2/mod_env.so")) {
            $contents = str_replace($contents, "#LoadModule rewrite_module libexec/apache2/mod_env.so", "LoadModule rewrite_module libexec/apache2/mod_env.so");
        }
        if (!strpos($contents, "Include /etc/apache2/extra/httpd-vhosts.conf")) {
            throw new \Exception("Statement 'Include ".$virtualHostsFile."' not found in '".$apache2ConfigurationFile."'");
        }
        
        return $virtualHostsFile;
    }
    
    protected function setVirtualHost($virtualHostFile)
    {
        file_put_contents($virtualHostFile, file_get_contents($virtualHostFile)."
<VirtualHost *:80>
    ServerName ".$this->hostName."
    ServerAdmin webmaster@localhost
    DocumentRoot ".$this->documentRoot."/".$this->siteName."
    <Directory ".$this->documentRoot."/".$this->siteName.">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>");
    }
    
    protected function restartWebServer()
    {
        exec("apachectl restart");
    }
}
