<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure NoSQL Data Access API installation
 */
class NoSQLServer
{
    /**
     * @var integer
     * @message Choose NoSQL driver
     * @option Redis
     * @option Memcache / Memcached
     * @option Couchbase
     * @option APC / APCu
     * @default 0
     */
    public $driver;
    
    /**
     * @var string
     * @message Write server host ip
     * @default 127.0.0.1
     * @validator (([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])
     * @if driver=0,1,2
     */
    public $host;
    
    /**
     * @var integer
     * @message Write server port
     * @validator ([0-9]+)
     * @optional
     * @if driver=0,1,2
     */
    public $port;
    
    /**
     * @var string
     * @message Write username
     * @if driver=2
     */
    public $user;
    
    /**
     * @var string
     * @message Write password
     * @if driver=2
     */
    public $password;
    
    /**
     * @var string
     * @message Write bucket name
     * @if driver=2
     */
    public $bucket;
    
    /**
     * @var string
     * @message Write bucket password
     * @if driver=2
     * @optional
     */
    public $bucket_password;
}
