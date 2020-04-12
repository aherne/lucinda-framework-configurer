<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure NoSQL Data Access API installation
 */
class NoSQLServer
{
    /**
     * @var integer
     * @message Write name of NoSQL driver
     * @option Redis
     * @option Memcache
     * @option Memcached
     * @option Couchbase
     * @option APC
     * @option APCu
     */
    public $driver;
    
    /**
     * @var string
     * @message Write server host ip (if APC/APCu, just hit enter)
     * @default 127.0.0.1
     * @validator (([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])
     */
    public $host;
    
    /**
     * @var integer
     * @message Write server port (if APC/APCu, just hit enter)
     * @validator ([0-9]+)
     * @optional
     */
    public $port;
    
    /**
     * @var string
     * @message Write username (if not Couchbase, just hit enter)
     */
    public $user;
    
    /**
     * @var string
     * @message Write password (if not Couchbase, just hit enter)
     */
    public $password;
    
    /**
     * @var string
     * @message Write bucket name (if not Couchbase, just hit enter)
     */
    public $bucket;
    
    /**
     * @var string
     * @message Write bucket password (if not Couchbase, just hit enter)
     * @optional
     */
    public $bucket_password;
}
