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
     * @option Memcache
     * @option Memcached
     * @option Couchbase
     * @option APC
     * @option APCu
     * @default 0
     */
    public int $driver;

    /**
     * @var string
     * @message Write server host ip
     * @default 127.0.0.1
     * @validator (([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])
     * @if driver=0,1,2
     */
    public string $host = "127.0.0.1";

    /**
     * @var integer
     * @message Write server port
     * @validator ([0-9]+)
     * @optional
     * @if driver=0,1,2
     */
    public int $port = 0;

    /**
     * @var string
     * @message Write username
     * @if driver=2
     */
    public string $user = "";

    /**
     * @var string
     * @message Write password
     * @if driver=2
     */
    public string $password = "";

    /**
     * @var string
     * @message Write bucket name
     * @if driver=2
     */
    public string $bucket = "";

    /**
     * @var string
     * @message Write bucket password
     * @if driver=2
     * @optional
     */
    public string $bucket_password = "";
}
