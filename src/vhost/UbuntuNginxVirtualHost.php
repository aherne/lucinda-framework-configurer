<?php
namespace Lucinda\Configurer;

require_once("AbstractVirtualHost.php");

/**
 * Recipe for virtual host creation where:
 * - operating system: ubuntu
 * - web server: nginx
 */
class UbuntuNginxVirtualHost extends AbstractVirtualHost
{
    protected function getVirtualHostFile()
    {
        $configurationFile = "/etc/nginx/sites-available/".$this->siteName;
        if (file_exists($configurationFile)) {
            throw new Exception("Site already installed");
        }
        return $configurationFile;
    }

    protected function setVirtualHost($virtualHostFile)
    {
        if (!file_exists("/var/run/php")) {
            throw new Exception("PHP FPM is not installed!");
        }
        $files = scandir("/var/run/php");
        $socketFile = "";
        foreach ($files as $file) {
            if (strpos($file, ".sock")) {
                $socketFile = "/var/run/php/".$file;
            }
        }
        if (!$socketFile) {
            throw new Exception("PHP FPM is not running (no active socket found)!");
        }

        file_put_contents($virtualHostFile, '
server {
    listen 80;
    listen [::]:80 ipv6only=on;

    # Log files location based on site name
    access_log /var/log/nginx/'.$this->siteName.'-access.log;
    error_log /var/log/nginx/'.$this->siteName.'-error.log;

    # Webroot Directory of site
    root '.$this->documentRoot."/".$this->siteName.';
    index index.php;

    # Your Domain Name
    server_name '.$this->hostName.';

    # Redirects all requests to bootstrap
    location / {
        rewrite ^/(.*)$ /index.php;
    }
	
    # makes requests to public/ folder to be fully managed by webserver
    location /public/ {
    }
    
    # skips routing of favicon
    location /favicon.ico {
    }    

    # PHP-FPM Configuration Nginx
    location ~ \.php$ {
         try_files $uri =404;
         fastcgi_split_path_info ^(.+\.php)(/.+)$;
         fastcgi_pass unix:'.$socketFile.';
         fastcgi_index index.php;
         fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
         fastcgi_param SERVER_ADMIN NA;
         fastcgi_param ENVIRONMENT local;         
         fastcgi_param SERVER_SIGNATURE nginx/$nginx_version;
         include fastcgi_params;
    }
 }
                    ');
        copy($virtualHostFile, str_replace("sites-available", "sites-enabled", $virtualHostFile));
    }

    protected function restartWebServer()
    {
        exec("service nginx restart");
    }
}
