<?php
namespace Lucinda\Configurer;

require_once("Prompter.php");
require_once("Features.php");

/**
 * Console based step by step selector of framework features available for installation.
 */
class FeaturesSelection
{
    const IP_REGEX = "/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/";
    const HOSTNAME_REGEX = "/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/";
    
    private $choices = [];
    private $prompt;
    
    public function __construct()
    {
        $this->prompt = new Prompter();
        if (empty($this->choices)) {
            $this->choices = new Features();
        }
        
        $steps = array_keys(get_object_vars(new Features()));
        foreach ($steps as $step) {
            if (isset($this->choices->$step) && $this->choices->$step !== null) {
                continue;
            }
            $methodName = "get".ucwords($step);
            $this->choices->$step = $this->$methodName();
        }
    }
    
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Sets site type
     *
     * @return null|string
     */
    private function getSiteType()
    {
        return $this->prompt->singleSelect("[3/9] Choose your site type", array("normal", "CMS", "RESTful web services"), 0);
    }

    /**
     * Sets whether or not to use logging
     *
     * @return bool
     */
    private function getLogging()
    {
        return $this->prompt->singleSelect("[4/9] Do you want to use loggers", array("yes","no"), 0)=="yes";
    }
    
    /**
     * Sets whether or not to use parameters validation
     *
     * @return bool
     */
    private function getValidation()
    {
        return $this->prompt->singleSelect("[5/9] Do you want to use request/path parameters validation", array("yes","no"), 0)=="yes";
    }

    /**
     * Sets whether or not to use html templating
     *
     * @return bool
     */
    private function getTemplating()
    {
        if ($this->choices->siteType=="RESTful web services") {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Sets whether or not to use output internationalization and how to configure that feature, if user opts to use it
     *
     * @return InternationalizationFeatures
     */
    private function getInternationalization()
    {
        if ($this->choices->siteType=="RESTful web services") {
            return false;
        }
        $choice = $this->prompt->singleSelect("[6/9] Do you want to customize HTML output by user language", array("yes","no"), 1)=="yes";
        if (!$choice) {
            return false;
        }
        
        $defaultLocale = $this->prompt->text("[6/9 1/2] Write default locale (lowercase ISO language followed by uppercase ISO country codes separated by underscore) your site will be using", "en_US", function ($result) {
            preg_match("/^([a-z]{2}_[A-Z]{2})$/", $result, $matches);
            return !empty($matches);
        });
        
        $detectionMethod = $this->prompt->singleSelect("[6/9 2/2] Choose how user locale will be detected", ["request (via 'locale' query string param)", "session (via 'locale' query string param remembered in session)", "header (via 'Accept-Language' request header)"], 1);
        
        $features = new InternationalizationFeatures();
        $features->defaultLocale = $defaultLocale;
        $features->detectionMethod = $detectionMethod;
        return $features;
    }

    /**
     * Sets whether or not to use HTTP caching
     *
     * @return bool
     */
    private function getCaching()
    {
        return true;
    }

    /**
     * Sets whether or not to use a SQL server and how to configure that feature, if user opts to use it
     *
     * @return SQLServerFeatures
     */
    private function getSqlServer()
    {
        $choice = $this->prompt->singleSelect("[7/9] Will your site use an SQL-type database (example: MySQL)", array("yes","no"), 0)=="yes";
        if (!$choice) {
            return false;
        }
        
        $driverName = $this->prompt->text("[7/9 1/5] Write SQL vendor that will be used in connections", "mysql", function ($result) {
            preg_match("/^([a-zA-Z0-9_\-]+)$/", $result, $matches1);
            return !empty($matches1);
        });
        $hostName = $this->prompt->text("[7/9 2/5] Write SQL server hostname", "127.0.0.1", function ($result) {
            preg_match(self::HOSTNAME_REGEX, $result, $matches1);
            preg_match(self::IP_REGEX, $result, $matches2);
            return !empty($matches1) || !empty($matches2);
        });
        $schema = $this->prompt->text("[7/9 3/5] Write schema (database) your application will be installed to (NOTE: must exist already!)", null, function ($result) {
            preg_match("/^([0-9a-zA-Z_]+)$/", $result, $matches);
            return !empty($matches);
        });
        $userName = $this->prompt->text("[7/9 4/5] Write database user name allowed to access that schema (NOTE: using root account is not recommended!)", "root", function ($result) {
            preg_match("/^([0-9a-zA-Z_]+)$/", $result, $matches);
            return !empty($matches);
        });
        $userPassword = $this->prompt->text("[7/9 5/5] Write database user password allowed to access that schema (NOTE: using empty password is not recommended!)", "", function ($result) {
            return true; // any password is ok, including none
        });
        try {
            $dbh = new PDO($driverName.":dbname=".$schema.";host=".$hostName, $userName, $userPassword);
            
            $features = new SQLServerFeatures();
            $features->driver = $driverName;
            $features->host = $hostName;
            $features->user = $userName;
            $features->password = $userPassword;
            $features->schema = $schema;
            return $features;
        } catch (PDOException $e) {
            $this->prompt->error("Connection to ".$driverName." failed with message '". $e->getMessage()."'");
            return $this->getSqlServer();
        }
    }


    /**
     * Sets whether or not to use a NoSQL server and how to configure that feature, if user opts to use it
     *
     * @return NoSQLServerFeatures
     */
    private function getNosqlServer()
    {
        $choice = $this->prompt->singleSelect("[8/9] Will your site use a NoSQL-type database (example: Redis)", array("yes","no"), 0)=="yes";
        if (!$choice) {
            return false;
        }
        
        
        $driverName = $this->prompt->singleSelect("[8/9 1/5] Choose NoSQL vendor that will be used in connections", array("apc","apcu","memcache","memcached","couchbase","redis"), null);
        if (!extension_loaded($driverName)) {
            $this->prompt->error("PHP extension ".$driverName." is not installed!");
            return $this->getNosqlServer();
        }
        
        $features = new NoSQLServerFeatures();
        $features->driver = $driverName;
        if (!in_array($driverName, array("apc","apcu"))) {
            $features->host = $this->prompt->text("[8/9 2/5] Write NoSQL server hostname", "127.0.0.1", function ($result) {
                preg_match(self::HOSTNAME_REGEX, $result, $matches1);
                preg_match(self::IP_REGEX, $result, $matches2);
                return !empty($matches1) || !empty($matches2);
            });
        }
        if ($driverName == "couchbase") {
            $features->user = $this->prompt->text("[8/9 3/5] Write user name to be used in couchbase connection", null, function ($result) {
                preg_match("/^([0-9a-zA-Z_]+)$/", $result, $matches);
                return !empty($matches);
            });
            $features->password = $this->prompt->text("[8/9 4/5] Write user password to be used in couchbase connection", null, function ($result) {
                return true; // any password is ok, excluding none
            });
            $features->bucket = $this->prompt->text("[8/9 5/5] Write name of couchbase bucket that stores your data", "default", function ($result) {
                preg_match("/^([0-9a-zA-Z_]+)$/", $result, $matches);
                return !empty($matches);
            });
        }
        
        // test connection
        switch ($driverName) {
            case "redis":
                $redis = new Redis();
                $result = $redis->connect($features->host);
                if (!$result) {
                    $this->prompt->error("Connection to redis server failed!");
                    return $this->getNosqlServer();
                }
                break;
            case "memcache":
                $memcache = new Memcache();
                $result = $memcache->connect($features->host);
                if (!$result) {
                    $this->prompt->error("Connection to memcache server failed!");
                    return $this->getNosqlServer();
                }
                break;
            case "memcached":
                $memcached = new Memcached();
                $memcached->addServer($features->host, 11211);
                $result = $memcached->set("test", 1);
                if (!$result) {
                    $this->prompt->error("Connection to memcached server failed!");
                    return $this->getNosqlServer();
                }
                $memcached->delete("test");
                break;
            case "couchbase":
                try {
                    $authenticator = new \Couchbase\PasswordAuthenticator();
                    $authenticator->username($features->user)->password($features->password);
                    
                    $cluster = new \CouchbaseCluster("couchbase://".$features->host);
                    $cluster->authenticate($authenticator);
                    
                    $bucket = $cluster->openBucket($features->bucket);
                } catch (\CouchbaseException $e) {
                    $this->prompt->error("Connection to couchbase server failed with error '".$e->getMessage()."'");
                    return $this->getNosqlServer();
                }
                break;
        }
        
        return $features;
    }

    /**
     * Sets whether or not to use authentication & authorization and how to configure that feature, if user opts to use it
     *
     * @return SecurityFeatures
     */
    private function getSecurity()
    {
        if ($this->choices->siteType!="CMS") {
            $choice = $this->prompt->singleSelect("[9/9] Will your site have login", array("yes","no"), 1)=="yes";
            if (!$choice) {
                return false;
            }
        }
        
        $features = new SecurityFeatures();
        
        $features->persistenceDrivers = $this->prompt->multipleSelect(
            "[9/9 1/4] Choose where logged in state is remembered across requests",
            ($this->choices->siteType != "RESTful web services"?array("session","remember me"):array("synchronizer token","json web token")),
            0
        );
        
        if ($this->choices->siteType=="CMS") {
            $features->authenticationMethods = array("database");
            $features->authorizationMethod = "database";
            return $features;
        }
        
        $features->authenticationMethods = $this->prompt->multipleSelect(
            "[9/9 2/4] Choose where authentication will be performed",
            array("database","oauth2 providers","access control list"),
            0
        );
        if (in_array("access control list", $features->authenticationMethods) && sizeof($features->authenticationMethods)!=1) {
            $this->prompt->error("Access control list is incompatible with other authentication methods");
            return $this->getSecurity();
        }
        
        if (in_array("oauth2 providers", $features->authenticationMethods)) {
            $oauth2Providers = $this->prompt->multipleSelect(
                "[9/9 3/4] Choose oauth2 providers you want to support",
                array("Facebook","Google","Instagram","GitHub","LinkedIn","VK","Yandex"),
                null
            );
            foreach ($oauth2Providers as $provider) {
                $info = new OAuth2Provider();
                $info->driver = $provider;
                $info->clientID = $this->prompt->text("[9/9 3/4 1/3] Please write your client id setup in ".$provider." site", null, function ($result) {
                    return !empty($result);
                });
                $info->clientSecret = $this->prompt->text("[9/9 3/4 2/3] Please write your client secret setup in ".$provider." site", null, function ($result) {
                    return !empty($result);
                });
                if ($provider=="GitHub") {
                    $info->applicationName = $this->prompt->text("[9/9 3/4 3/3] Please write your application name setup in ".$provider." site", null, function ($result) {
                        return !empty($result);
                    });
                }
                $features->oauth2Providers[] = $info;
            }
        }
        
        $features->authorizationMethod = $this->prompt->singleSelect(
            "[9/9 4/4] Choose where authorization will be performed",
            array("database","access control list"),
            1
        );
        
        return $features;
    }

    /**
     * Tests web server to see if document root was ok
     *
     * @param string $url
     * @return bool
     */
    private function pageExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }
}
