<?php
namespace Lucinda\Configurer;

require_once("XMLInstallation.php");

/**
 * Populates stdout.xml based on features selected by user
 */
class StdoutXMLInstallation extends XMLInstallation
{    
    protected function generateXML()
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE xml><xml></xml>');
        $this->setApplicationTag();
        $this->setFormatsTag();
        $this->setListeners();
        $this->setLoggersTag();
        $this->setInternationalizationTag();
        $this->setCachingTag();
        $this->setServersTag();
        $this->setSecurityTag();
        $this->setRoutes();
        $this->setUsers();
    }
    
    
    /**
     * Populates <application> tag @ stdout.xml
     */
    private function setApplicationTag()
    {
        $application = $this->xml->addChild("application");
        $application->addAttribute("version", "0.0.1");
        $application->addAttribute("auto_routing", "0");
        $application->addAttribute("default_format", ($this->features->siteType!="RESTful web services"?"html":"json"));
        $application->addAttribute("default_page", "index");
        if ($this->features->templating) {
            $application->addAttribute("templates_extension", "html");
        }
        
        $paths = $application->addChild("paths");
        $paths->addChild("controllers", "application/controllers");
        $paths->addChild("resolvers", "application/resolvers");
        $paths->addChild("listeners", "application/listeners");
        if ($this->features->siteType!="RESTful web services") {
            $paths->addChild("views", "application/views");
        }
        if ($this->features->templating) {
            $paths->addChild("compilations", "compilations");
            $paths->addChild("tags", "application/tags");
        }
    }
    
    /**
     * Populates <formats> tag @ stdout.xml
     */
    private function setFormatsTag()
    {
        $application = $this->xml->addChild("formats");
        
        if ($this->features->siteType!="RESTful web services") {
            $html = $application->addChild("format");
            $html->addAttribute("name", "html");
            $html->addAttribute("content_type", "text/html");
            if ($this->features->templating) {
                $html->addAttribute("class", "ViewLanguageResolver");
            } else {
                $html->addAttribute("class", "HtmlResolver");
            }
            $html->addAttribute("charset", "UTF-8");
        }
        
        $json = $application->addChild("format");
        $json->addAttribute("name", "json");
        $json->addAttribute("content_type", "application/json");
        $json->addAttribute("class", "JsonResolver");
        $json->addAttribute("charset", "UTF-8");
    }
    
    /**
     * Populates <listeners> tag @ stdout.xml
     */
    private function setListeners()
    {
        $listeners = $this->xml->addChild("listeners");
        $eventListeners = array();
        if ($this->features->logging) {
            $eventListeners[] = "LoggingListener";
        }
        if ($this->features->sqlServer) {
            $eventListeners[] = "SQLDataSourceInjector";
        }
        if ($this->features->nosqlServer) {
            $eventListeners[] = "NoSQLDataSourceInjector";
        }
        $eventListeners[] = "ErrorListener";
        if ($this->features->validation) {
            $eventListeners[] = "ValidationListener";
        }
        if ($this->features->security) {
            $eventListeners[] = "SecurityListener";
        }
        if ($this->features->internationalization) {
            $eventListeners[] = "LocalizationListener";
        }
        if ($this->features->caching) {
            $eventListeners[] = "HttpCachingListener";
        }
        
        foreach ($eventListeners as $className) {
            $listener = $listeners->addChild("listener");
            $listener->addAttribute("class", $className);
        }
    }
    
    /**
     * Populates <loggers> tag @ stdout.xml
     */
    private function setLoggersTag()
    {
        if (!$this->features->logging) {
            return;
        }
        
        $loggers = $this->xml->addChild("loggers");
        $loggers->addAttribute("path", "application/models/loggers");
        
        $logger = $loggers->addChild(self::DEFAULT_ENVIRONMENT)->addChild("logger");
        $logger->addAttribute("class", "FileLoggerWrapper");
        $logger->addAttribute("path", "messages");
        $logger->addAttribute("format", "%d %f %l %m");
    }
    
    /**
     * Populates <internationalization> tag @ stdout.xml
     */
    private function setInternationalizationTag()
    {
        if (!$this->features->internationalization) {
            return;
        }
        
        $internationalization = $this->xml->addChild("internationalization");
        $internationalization->addAttribute("locale", $this->features->internationalization->defaultLocale);
        $internationalization->addAttribute("method", $this->features->internationalization->detectionMethod);
        $internationalization->addAttribute("folder", "locale");
        $internationalization->addAttribute("domain", "messages");
    }
    
    /**
     * Populates <http_caching> tag @ stdout.xml
     */
    private function setCachingTag()
    {
        if (!$this->features->caching) {
            return;
        }
        
        $caching = $this->xml->addChild("http_caching");
        $caching->addAttribute("class", "EtagCacheableDriver");
        $caching->addAttribute("secret", $this->generateSecret());
        $caching->addAttribute("drivers_path", "application/models/cacheables");
    }
    
    /**
     * Populates <servers> tag @ stdout.xml
     */
    private function setServersTag()
    {
        if (!$this->features->sqlServer && !$this->features->nosqlServer) {
            return;
        }
        
        $servers = $this->xml->addChild("servers");
        
        if ($this->features->sqlServer) {
            $server = $servers->addChild("sql")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("server");
            $server->addAttribute("driver", $this->features->sqlServer->driver);
            $server->addAttribute("host", $this->features->sqlServer->host);
            $server->addAttribute("username", $this->features->sqlServer->user);
            $server->addAttribute("password", $this->features->sqlServer->password);
            $server->addAttribute("schema", $this->features->sqlServer->schema);
            $server->addAttribute("charset", "UTF8");
        }
        if ($this->features->nosqlServer) {
            $server = $servers->addChild("nosql")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("server");
            $server->addAttribute("driver", $this->features->nosqlServer->driver);
            switch ($this->features->nosqlServer->driver) {
                case "redis":
                case "memcache":
                case "memcached":
                    $server->addAttribute("host", $this->features->nosqlServer->host);
                    break;
                case "couchbase":
                    $server->addAttribute("host", $this->features->nosqlServer->host);
                    $server->addAttribute("username", $this->features->nosqlServer->user);
                    $server->addAttribute("password", $this->features->nosqlServer->password);
                    $server->addAttribute("bucket_name", $this->features->nosqlServer->bucket);
                    break;
            }
        }
    }
    
    /**
     * Populates <security> tag @ stdout.xml
     */
    private function setSecurityTag()
    {
        if (!$this->features->security) {
            return;
        }
        
        $security = $this->xml->addChild("security");
        $security->addAttribute("dao_path", "application/models/dao");
        
        $csrf = $security->addChild("csrf");
        $csrf->addAttribute("secret", $this->generateSecret());
        
        $persistenceDrivers = $security->addChild("persistence");
        foreach ($this->features->security->persistenceDrivers as $driverName) {
            switch ($driverName) {
                case "session":
                    $persistenceDrivers->addChild("session");
                    break;
                case "remember me":
                    $rememberMe =$persistenceDrivers->addChild("remember_me");
                    $rememberMe->addAttribute("secret", $this->generateSecret());
                    break;
                case "synchronizer token":
                    $synchronizerToken =$persistenceDrivers->addChild("synchronizer_token");
                    $synchronizerToken->addAttribute("secret", $this->generateSecret());
                    break;
                case "json web token":
                    $jsonWebToken =$persistenceDrivers->addChild("json_web_token");
                    $jsonWebToken->addAttribute("secret", $this->generateSecret());
                    break;
            }
        }
        
        $authentication = $security->addChild("authentication");
        if (in_array("database", $this->features->security->authenticationMethods) || in_array("access control list", $this->features->security->authenticationMethods)) {
            $form = $authentication->addChild("form");
            if (in_array("database", $this->features->security->authenticationMethods)) {
                $form->addAttribute("dao", "UsersAuthentication");
            }
            $form->addChild("login");
            $form->addChild("logout");
        }
        if (in_array("oauth2 providers", $this->features->security->authenticationMethods)) {
            $oauth2 = $authentication->addChild("oauth2");
            $oauth2->addAttribute("dao", "UsersOAuth2Authentication");
            $oauth2->addAttribute("auto_create", "1");
            $environment = $oauth2->addChild("local");
            foreach ($this->features->security->oauth2Providers as $oauth2DriverInfo) {
                $driverTag = $environment->addChild("driver");
                $driverTag->addAttribute("name", $oauth2DriverInfo->driver);
                $driverTag->addAttribute("callback", "/login/".strtolower($oauth2DriverInfo->driver));
                $driverTag->addAttribute("client_id", $oauth2DriverInfo->clientID);
                $driverTag->addAttribute("client_secret", $oauth2DriverInfo->clientSecret);
                if ($oauth2DriverInfo->driver=="GitHub") {
                    $driverTag->addAttribute("application_name", $oauth2DriverInfo->applicationName);
                }
            }
        }
        
        $authorization = $security->addChild("authorization");
        switch ($this->features->security->authorizationMethod) {
            case "database":
                $dao = $authorization->addChild("by_dao");
                $dao->addAttribute("page_dao", "PagesAuthorization");
                $dao->addAttribute("user_dao", "UsersAuthorization");
                break;
            case "access control list":
                $authorization->addChild("by_route");
                break;
        }
    }
    
    /**
     * Populates <routes> tag @ stdout.xml
     */
    private function setRoutes()
    {
        $routes = $this->xml->addChild("routes");
        
        if ($this->features->validation) {
            $routes->addAttribute("validators_path", "application/models/validators");
        }
        
        $routesPossible = array();
        if ($this->features->security) {
            $routesPossible["index"] = array("url"=>"index", "controller"=>"IndexController");
            $routesPossible["login"] = array("url"=>"login", "controller"=>"LoginController");
            $routesPossible["logout"] = array("url"=>"logout");
            if ($this->features->siteType=="CMS") {
                $routesPossible["restricted"] = array("url"=>"restricted", "controller"=>"RestrictedController");
            } else {
                $routesPossible["members"] = array("url"=>"members", "controller"=>"MembersController");
            }
            
            if ($this->features->security->authorizationMethod == "access control list") {
                if ($this->features->siteType=="CMS") {
                    $routesPossible["index"]["roles"]="MEMBER,ADMINISTRATOR";
                    $routesPossible["login"]["roles"]="GUEST";
                    $routesPossible["logout"]["roles"]="MEMBER,ADMINISTRATOR";
                    $routesPossible["restricted"]["roles"] = "ADMINISTRATOR";
                } else {
                    $routesPossible["index"]["roles"]= "GUEST,MEMBER";
                    $routesPossible["login"]["roles"]="GUEST";
                    $routesPossible["logout"]["roles"]="MEMBER";
                    $routesPossible["members"]["roles"] = "MEMBER";
                }
            }
            
            if ($this->features->siteType!="RESTful web services") {
                $routesPossible["index"]["view"]="index";
                $routesPossible["login"]["view"]="login";
                if ($this->features->siteType=="CMS") {
                    $routesPossible["restricted"]["view"] = "restricted";
                } else {
                    $routesPossible["members"]["view"] = "members";
                }
            }
            
            if (!empty($this->features->security->oauth2Providers)) {
                foreach ($this->features->security->oauth2Providers as $oauth2DriverInfo) {
                    $driverName = "login/".strtolower($oauth2DriverInfo->driver);
                    $routesPossible[$driverName] = array("url"=>"login/".$driverName);
                }
            }
        } else {
            $routesPossible[] = array("url"=>"index", "view"=>"index", "controller"=>"IndexController");
        }
        
        foreach ($routesPossible as $routeInfo) {
            $route = $routes->addChild("route");
            foreach ($routeInfo as $key=>$value) {
                $route->addAttribute($key, $value);
            }
        }
    }
    
    /**
     * Populates <users> tag @ stdout.xml
     */
    private function setUsers()
    {
        if (!$this->features->security || !in_array("access control list", $this->features->security->authenticationMethods)) {
            return;
        }
        
        $routes = $this->xml->addChild("users");
        
        $usersPossible = array();
        if ($this->features->siteType=="CMS") {
            $usersPossible[] = array("id"=>1, "username"=>"john", "password"=>password_hash("doe", PASSWORD_BCRYPT), "roles"=>"MEMBER,ADMINISTRATOR");
            $usersPossible[] = array("id"=>2, "username"=>"jane", "password"=>password_hash("doe", PASSWORD_BCRYPT), "roles"=>"MEMBER");
        } else {
            $usersPossible[] = array("id"=>1, "username"=>"john", "password"=>password_hash("doe", PASSWORD_BCRYPT), "roles"=>"MEMBER");
        }
        foreach ($usersPossible as $userInfo) {
            $route = $routes->addChild("user");
            foreach ($userInfo as $key=>$value) {
                $route->addAttribute($key, $value);
            }
        }
    }
    
    /**
     * Generates cryptographically secure secret key of fixed length
     *
     * @param int $secretLength
     * @return string
     */
    private function generateSecret($secretLength = 32)
    {
        return substr(password_hash(uniqid(), PASSWORD_BCRYPT), rand(7, 60-$secretLength), $secretLength);
    }
}