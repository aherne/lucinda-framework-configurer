<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure SQL Data Access API installation
 */
class SQLServer
{
    /**
     * @var string
     * @message Write PDO name of SQL driver (see: https://www.php.net/manual/en/pdo.drivers.php)
     * @default mysql
     * @validator ([a-zA-Z0-9_\-]+)
     */
    public $driver;
    
    /**
     * @var string
     * @message Write server host ip
     * @default 127.0.0.1
     * @validator (([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])
     */
    public $host;
    
    /**
     * @var integer
     * @message Write server port
     * @validator ([0-9]+)
     * @optional
     */
    public $port;
    
    /**
     * @var string
     * @message Write username
     */
    public $user;
    
    /**
     * @var string
     * @message Write password
     */
    public $password;
    
    /**
     * @var string
     * @message Write schema your app will be installed into
     * @validator ([0-9a-zA-Z_]+)
     */
    public $schema;
}
