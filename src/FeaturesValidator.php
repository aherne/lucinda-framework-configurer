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
    private Features $features;
    
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
        if ($features->migrations) {
            $this->validateMigrations($features);
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
        switch ($server->driver) {
            case 0: // mysql
                $pdo = new \PDO("mysql:dbname=".$server->schema.";host=".$server->host, $server->user, $server->password);
                $pdo->exec("SHOW TABLES");
                break;
        }
    }
    
    /**
     * Validates nosql server selected by opening a connection
     *
     * @param NoSQLServer $server
     * @throws \Exception
     */
    private function validateNoSQLServer(NoSQLServer $server): void
    {
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
                break;
            case 1:
                if (class_exists("\Memcache")) {
                    throw new \Exception("Extension not installed: memcache");
                }
                $memcache = new \Memcache();
                $result = $memcache->connect($server->host, ($server->port?$server->port:11211));
                if (!$result) {
                    throw new \Exception("Connection to server failed: memcache");
                }
                break;
            case 2:
                if (!class_exists("\Memcached")) {
                    throw new \Exception("Extension not installed: memcached");
                }
                $memcached = new \Memcached();
                $result = $memcached->connect($server->host, ($server->port?$server->port:11211));
                $result = $memcached->set("test", 1);
                if (!$result) {
                    throw new \Exception("Connection to server failed: memcached");
                }
                $memcached->delete("test");
                break;
            case 3:
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
            case 4:
                if (!function_exists("\apc_store")) {
                    throw new \Exception("Extension not installed: apcu");
                }
                break;
            case 5:
                if (function_exists("\apcu_store")) {
                    throw new \Exception("Extension not installed: apcu");
                }
                break;
        }
    }
    
    /**
     * Validates migrations settings to check if associated server was configured
     * 
     * @param Features $features
     * @throws \Exception
     */
    protected function validateMigrations(Features $features): void
    {
        if ($features->migrations->storageMethod == 0) {
            if (!$features->sqlServer) {
                throw new \Exception("A SQL server is required if you need to store migrations progress in a SQL table");
            }
        } else {
            if (!$features->nosqlServer) {
                throw new \Exception("A NoSQL server is required if you need to store migrations progress by a NoSQL key");
            }
        }
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
