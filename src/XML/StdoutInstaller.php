<?php
namespace Lucinda\Configurer\XML;

/**
 * Populates stdout.xml based on features selected by user
 */
class StdoutInstaller extends Installer
{
    const SALT_LENGTH=128;
    
    /**
     * {@inheritDoc}
     * @see Installer::generateXML()
     */
    protected function generateXML(): void
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE xml><xml></xml>');
        $this->setApplicationTag();
        $this->setFormatsTag();
        $this->setTemplatingTag();
        $this->setLoggersTag();
        $this->setInternationalizationTag();
        $this->setHeadersTag();
        $this->setSqlTag();
        $this->setNoSqlTag();
        $this->setSecurityTag();
        $this->setRoutesTag();
        $this->setUsersTag();
        $this->setSessionTag();
    }
    
    /**
     * Populates <application> tag @ stdout.xml
     */
    private function setApplicationTag(): void
    {
        $application = $this->xml->addChild("application");
        $application->addAttribute("version", "0.0.1");
        $application->addAttribute("default_format", (!$this->features->isREST?"html":"json"));
        $application->addAttribute("default_page", "index");
        $paths = $application->addChild("paths");
        $paths->addAttribute("controllers", "application/controllers");
        $paths->addAttribute("resolvers", "application/resolvers");
        if (!$this->features->isREST) {
            $paths->addAttribute("views", "application/views");
        }
    }
    
    /**
     * Populates <formats> tag @ stdout.xml
     */
    private function setFormatsTag(): void
    {
        $application = $this->xml->addChild("formats");
        
        if (!$this->features->isREST) {
            $html = $application->addChild("format");
            $html->addAttribute("name", "html");
            $html->addAttribute("content_type", "text/html");
            $html->addAttribute("class", "HtmlResolver");
            $html->addAttribute("charset", "UTF-8");
        }
        
        $json = $application->addChild("format");
        $json->addAttribute("name", "json");
        $json->addAttribute("content_type", "application/json");
        $json->addAttribute("class", "JsonResolver");
        $json->addAttribute("charset", "UTF-8");
    }
    
    
    /**
     * Populates <templating> tag @ stdout.xml
     */
    private function setTemplatingTag(): void
    {
        if ($this->features->isREST) {
            return;
        }
        
        $templating = $this->xml->addChild("templating");
        $templating->addAttribute("compilations_path", "compilations");
        $templating->addAttribute("tags_path", "application/tags");
        $templating->addAttribute("templates_path", "application/views");
        $templating->addAttribute("templates_extension", "html");
    }
    
    /**
     * Populates <loggers> tag @ stdout.xml
     */
    private function setLoggersTag(): void
    {
        if (!$this->features->logging) {
            return;
        }
        
        $loggers = $this->xml->addChild("loggers");
        $loggers->addAttribute("path", "application/loggers");
        
        $logger = $loggers->addChild(self::DEFAULT_ENVIRONMENT)->addChild("logger");
        $logger->addAttribute("class", "FileLogger");
        $logger->addAttribute("path", "messages");
        $logger->addAttribute("format", "%d %f %l %m");
    }
    
    /**
     * Populates <internationalization> tag @ stdout.xml
     */
    private function setInternationalizationTag(): void
    {
        if (!$this->features->internationalization) {
            return;
        }
        
        $detectionMethod = "";
        switch ($this->features->internationalization->detectionMethod) {
            case "0":
                $detectionMethod = "header";
                break;
            case "1":
                $detectionMethod = "request";
                break;
            case "2":
                $detectionMethod = "session";
                break;
        }
        
        $internationalization = $this->xml->addChild("internationalization");
        $internationalization->addAttribute("locale", $this->features->internationalization->defaultLocale);
        $internationalization->addAttribute("method", $detectionMethod);
        $internationalization->addAttribute("folder", "locale");
        $internationalization->addAttribute("domain", "messages");
    }
    
    /**
     * Populates <http_caching> tag @ stdout.xml
     */
    private function setHeadersTag(): void
    {
        if (!$this->features->headers) {
            return;
        }
        
        $headers = $this->xml->addChild("headers");
        if ($this->features->headers->caching) {
            $headers->addAttribute("no_cache", $this->features->headers->caching->no_cache);
            $headers->addAttribute("expiration", $this->features->headers->caching->expiration);
            $headers->addAttribute("cacheable", "application/cacheables/EtagCacheable");
        }
        if ($this->features->headers->cors) {
            $headers->addAttribute("allow_credentials", $this->features->headers->cors->allow_credentials);
            $headers->addAttribute("cors_max_age", $this->features->headers->cors->max_age);
            $headers->addAttribute("allowed_request_headers", $this->features->headers->cors->allowed_request_headers);
            $headers->addAttribute("allowed_response_headers", $this->features->headers->cors->allowed_response_headers);
        }
    }
    
    /**
     * Populates <sql> tag @ stdout.xml
     */
    private function setSqlTag(): void
    {
        if (!$this->features->sqlServer) {
            return;
        }
        
        $server = $this->xml->addChild("sql")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("server");
        $server->addAttribute("driver", $this->features->sqlServer->driver);
        $server->addAttribute("host", $this->features->sqlServer->host);
        if ($this->features->sqlServer->port) {
            $server->addAttribute("port", $this->features->sqlServer->port);
        }
        $server->addAttribute("username", $this->features->sqlServer->user);
        $server->addAttribute("password", $this->features->sqlServer->password);
        $server->addAttribute("schema", $this->features->sqlServer->schema);
        $server->addAttribute("charset", "UTF8");
    }
    
    /**
     * Populates <nosql> tag @ stdout.xml
     */
    private function setNoSqlTag(): void
    {
        if (!$this->features->nosqlServer) {
            return;
        }
        
        $server = $this->xml->addChild("nosql")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("server");
        $server->addAttribute("driver", $this->features->nosqlServer->driver);
        if (!in_array($this->features->nosqlServer->driver, ["apc","apcu"])) {
            $server->addAttribute("host", $this->features->nosqlServer->host);
            if ($this->features->nosqlServer->port) {
                $server->addAttribute("port", $this->features->nosqlServer->port);
            }
            if ($this->features->nosqlServer->driver == "couchbase") {
                $server->addAttribute("username", $this->features->nosqlServer->user);
                $server->addAttribute("password", $this->features->nosqlServer->password);
                $server->addAttribute("bucket_name", $this->features->nosqlServer->bucket);
                if ($this->features->nosqlServer->bucket_password) {
                    $server->addAttribute("bucket_password", $this->features->nosqlServer->bucket_password);
                }
            }
        }
    }
    
    /**
     * Populates <security> tag @ stdout.xml
     */
    private function setSecurityTag(): void
    {
        if (!$this->features->security) {
            return;
        }
        
        $security = $this->xml->addChild("security");
        $security->addAttribute("dao_path", "application/models/dao");
        
        $csrf = $security->addChild("csrf");
        $csrf->addAttribute("secret", $this->generateSecret());
        
        $persistenceDrivers = $security->addChild("persistence");
        switch ($this->features->security->persistenceDrivers) {
            case 0:
                $persistenceDrivers->addChild("session");
                $rememberMe = $persistenceDrivers->addChild("remember_me");
                $rememberMe->addAttribute("secret", $this->generateSecret());
                break;
            case 1:
                $synchronizerToken =$persistenceDrivers->addChild("synchronizer_token");
                $synchronizerToken->addAttribute("secret", $this->generateSecret());
                break;
        }
        
        $authentication = $security->addChild("authentication");
        $form = $authentication->addChild("form");
        if ($this->features->nosqlServer) {
            $form->addAttribute("throttler", "NoSqlLoginThrottler");
        } elseif ($this->features->sqlServer) {
            $form->addAttribute("throttler", "SqlLoginThrottler");
        }
        switch ($this->features->security->authenticationMethod) {
            case 0:
                $form->addAttribute("dao", "UsersFormAuthentication");
                break;
            case 1:
                $form->addAttribute("dao", "UsersFormAuthentication");
                $oauth2 = $authentication->addChild("oauth2");
                $oauth2->addAttribute("dao", "UsersOAuth2Authentication");
                break;
        }
        
        $authorization = $security->addChild("authorization");
        switch ($this->features->security->authorizationMethod) {
            case 0:
                $dao = $authorization->addChild("by_dao");
                $dao->addAttribute("page_dao", "PagesAuthorization");
                $dao->addAttribute("user_dao", "UsersAuthorization");
                break;
            case 1:
                $authorization->addChild("by_route");
                break;
        }
    }
    
    /**
     * Populates <routes> tag @ stdout.xml
     */
    private function setRoutesTag(): void
    {
        $routes = $this->xml->addChild("routes");
        if ($this->features->routes->default_roles) {
            $routes->addAttribute("roles", $this->features->routes->default_roles);
        }
        foreach ($this->features->routes->routes as $info) {
            $route = $routes->addChild("route");
            $route->addAttribute("url", $info->url);
            if ($info->controller!==null) {
                $route->addAttribute("controller", $info->controller);
            }
            if ($info->view!==null) {
                $route->addAttribute("view", $info->view);
            }
            if ($info->roles!==null) {
                $route->addAttribute("roles", $info->roles);
            }
            if ($info->no_cache!==null) {
                $route->addAttribute("no_cache", $info->no_cache);
            }
            if ($info->cache_expiration!==null) {
                $route->addAttribute("cache_expiration", $info->cache_expiration);
            }
            if ($info->allowed_methods!==null) {
                $route->addAttribute("allowed_methods", $info->allowed_methods);
            }
        }
    }
    
    
    /**
     * Populates <users> tag @ stdout.xml
     */
    private function setUsersTag(): void
    {
        if (!$this->features->users || !$this->features->security || $this->features->security->authenticationMethod!=2) {
            return;
        }
        
        $users = $this->xml->addChild("users");
        $users->addAttribute("roles", $this->features->users->default_roles);
        foreach ($this->features->users->users as $info) {
            $user = $users->addChild("user");
            $user->addAttribute("id", $info->id);
            $user->addAttribute("name", $info->name);
            $user->addAttribute("email", $info->email);
            $user->addAttribute("username", $info->username);
            $user->addAttribute("password", $info->password);
            $user->addAttribute("roles", $info->roles);
        }
    }
    
    /**
     * Populates <session> tag @ stdout.xml
     */
    private function setSessionTag(): void
    {
        if (!$this->features->isREST && (($this->features->internationalization && $this->features->internationalization->detectionMethod==2) || ($this->features->security && $this->features->security->persistenceDrivers==0))) {
            $session = $this->xml->addChild("session");
            $session->addAttribute("auto_start", 1);
        }
    }
    
    /**
     * Generates cryptographically secure key
     *
     * @return string
     */
    private function generateSecret(): string
    {
        $saltGenerator = new \Lucinda\WebSecurity\Token\SaltGenerator(self::SALT_LENGTH);
        return $saltGenerator->getSalt();
    }
}
