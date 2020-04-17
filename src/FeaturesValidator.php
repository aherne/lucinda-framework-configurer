<?php
namespace Lucinda\Configurer;

use Lucinda\Configurer\Features\Features;
use Lucinda\Configurer\Features\SQLServer;
use Lucinda\Configurer\Features\NoSQLServer;

/**
 * Validates SQL/NoSQL servers selected in features along with connection credentials and settings
 */
class FeaturesValidator
{
    const HOSTNAME_REGEX = "/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/";
    private $features;
    
    /**
     * @param Features $features
     * @throws \Exception
     */
    public function __construct(Features $features)
    {
        $this->features = $features;
        if ($features->sqlServer) {
            $this->validateSQLServer($features->sqlServer);
        }
        if ($features->nosqlServer) {
            $this->validateNoSQLServer($features->nosqlServer);
        }
        if ($features->security) {
            $this->validateSecurity($features);
        }
        if ($features->internationalization) {
            $this->validateInternationalization($features);
        }
    }
    
    /**
     * Validates SQL server selected by opening a connection and running a query on it
     *
     * @param SQLServer $server
     */
    private function validateSQLServer(SQLServer $server): void
    {
        $driver = "";
        switch ($server->driver) {
            case 0: // mysql
                $driver = "mysql";
                $pdo = new \PDO($driver.":dbname=".$server->schema.";host=".$server->host, $server->user, $server->password);
                $pdo->exec("SHOW TABLES");
                break;
        }
        $server->driver = $driver;
    }
    
    /**
     * Validates nosql server selected by opening a connection
     *
     * @param NoSQLServer $server
     * @throws \Exception
     */
    private function validateNoSQLServer(NoSQLServer $server): void
    {
        $driver = "";
        switch ($server->driver) {
            case 0:
                if (!class_exists("\Redis")) {
                    throw new \Exception("Extension not installed: redis");
                }
                $redis = new \Redis();
                $result = $redis->connect($server->host, ($server->port?$server->port:6379));
                if (!$result) {
                    throw new \Exception("Connection to server failed: redis");
                }
                $driver = "redis";
                break;
            case 1:
                if (class_exists("\Memcached")) {
                    $memcache = new \Memcached();
                    $result = $memcache->connect($server->host, ($server->port?$server->port:11211));
                    $result = $memcached->set("test", 1);
                    if (!$result) {
                        throw new \Exception("Connection to server failed: memcached");
                    }
                    $memcached->delete("test");
                    $driver = "memcached";
                } elseif (class_exists("\Memcache")) {
                    $memcache = new \Memcache();
                    $result = $memcache->connect($server->host, ($server->port?$server->port:11211));
                    if (!$result) {
                        throw new \Exception("Connection to server failed: memcache");
                    }
                    $driver = "memcache";
                } else {
                    throw new \Exception("Extension not installed: memcache or memcached");
                }
                break;
            case 2:
                if (!class_exists("\Couchbase\PasswordAuthenticator")) {
                    throw new \Exception("Extension not installed: couchbase");
                }
                try {
                    $authenticator = new \Couchbase\PasswordAuthenticator();
                    $authenticator->username($server->user)->password($server->password);
                    
                    $cluster = new \CouchbaseCluster("couchbase://".$server->host);
                    $cluster->authenticate($authenticator);
                    
                    $cluster->openBucket($server->bucket, (string) $server->password);
                } catch (\CouchbaseException $e) {
                    throw new \Exception("Connection to server failed: couchbase");
                }
                $driver = "couchbase";
                break;
            case 3:
                if (function_exists("\apcu_store")) {
                    $driver = "apcu";
                } elseif (function_exists("apc_store")) {
                    $driver = "apc";
                } else {
                    throw new \Exception("Extension not installed: apcu");
                }
                break;
        }
        $server->driver = $driver;
    }
    
    /**
     * Validates security settings by matching them with sql and nosql servers chosen
     *
     * @param Features $features
     * @throws \Exception
     */
    protected function validateSecurity(Features $features): void
    {
        if (!$features->sqlServer && !($features->security->authenticationMethod==2 && $features->security->authorizationMethod==1)) {
            throw new \Exception("A SQL server is required if you need DB authentication/authorization");
        }
        if (!$features->sqlServer && !$features->nosqlServer) {
            throw new \Exception("Form authentication requires a SQL or NoSQL server for login throttling");
        }
    }
    
    /**
     * Validates internationalization settings
     *
     * @param Features $features
     * @throws \Exception
     */
    protected function validateInternationalization(Features $features): void
    {
        if ($features->isREST && $features->internationalization && $features->internationalization->detectionMethod==2) {
            throw new \Exception("Session detection of locale is not supported for RESTful sites!");
        }
    }
}
