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
            if ($features->sqlServer->driver!="mysql") {
                throw new \Exception("Currently, SQL installer only works with mysql vendor");
            }
            $this->validateSQLServer($features->sqlServer);
        } else if($features->security && !($features->security->authenticationMethod==1 && $features->security->authorizationMethod==1)) {
            throw new \Exception("A SQL server is required if you need DB authentication/authorization");
        }
        if ($features->nosqlServer) {
            $this->validateNoSQLServer($features->nosqlServer);
        }
    }
    
    /**
     * Validates SQL server selected by opening a connection and running a query on it
     * 
     * @param SQLServer $server
     */
    private function validateSQLServer(SQLServer $server)
    {
        $pdo = new \PDO($server->driver.":dbname=".$server->schema.";host=".$server->host, $server->user, $server->password);
        $pdo->exec("SHOW TABLES");
    }
    
    /**
     * Validates nosql server selected by opening a connection
     * 
     * @param NoSQLServer $server
     * @throws \Exception
     */
    private function validateNoSQLServer(NoSQLServer $server)
    {
        switch ($server->driver) {
            case "apc":
                if (!function_exists("\apc_store")) {
                    throw new \Exception("Extension not installed: apc");
                }
                break;
            case "apcu":
                if (!function_exists("\apcu_store")) {
                    throw new \Exception("Extension not installed: apcu");
                }
                break;
            case "redis":
                if (!class_exists("\Redis")) {
                    throw new \Exception("Extension not installed: redis");
                }
                $redis = new \Redis();
                $result = $redis->connect($server->host, ($server->port?$server->port:6379));
                if (!$result) {
                    throw new \Exception("Connection to server failed: redis");
                }
                break;
            case "memcache":
                if (!class_exists("\Memcache")) {
                    throw new \Exception("Extension not installed: memcache");
                }
                $memcache = new \Memcache();
                $result = $memcache->connect($server->host, ($server->port?$server->port:11211));
                if (!$result) {
                    throw new \Exception("Connection to server failed: memcache");
                }
                break;
            case "memcached":
                if (!class_exists("\Memcached")) {
                    throw new \Exception("Extension not installed: memcached");
                }
                $memcached = new \Memcached();
                $memcached->addServer($server->host, ($server->port?$server->port:11211));
                $result = $memcached->set("test", 1);
                if (!$result) {
                    throw new \Exception("Connection to server failed: memcached");
                }
                $memcached->delete("test");
                break;
            case "couchbase":
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
                break;
        }
    }
}

