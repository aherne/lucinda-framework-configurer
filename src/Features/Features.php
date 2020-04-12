<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct containing features available for installation
 */
class Features
{    
    /**
     * @var integer
     * @message Choose content types your site will support
     * @option HTML + JSON (normal site)
     * @option JSON (REST-ful web service site)
     * @default 0
     */
    public $isREST;  
    
    /**
     * @var \Lucinda\Configurer\Features\Headers
     * @message Do you want to be able to read request headers and write response headers for caching purposes (RECOMMENDED)
     * @default 1
     */
    public $headers;
    
    /**
     * @var \Lucinda\Configurer\Features\Internationalization
     * @message Is your site expected to display in multiple languages
     * @default 0
     */
    public $internationalization;
    
    /**
     * @var \Lucinda\Configurer\Features\SQLServer
     * @message Will you use SQL databases (eg: MySQL) in your site
     * @default 1
     */
    public $sqlServer;
    
    /**
     * @var \Lucinda\Configurer\Features\NoSQLServer
     * @message Will you use NoSQL key-value store databases (eg: Redis) in your site
     * @default 0
     */
    public $nosqlServer;
    
    /**
     * @var \Lucinda\Configurer\Features\Security
     * @message Do you want SOME/ALL pages in your site to be protected by login
     * @default 0
     */
    public $security;
    
    /**
     * @var \Lucinda\Configurer\Features\Routes
     * @message -
     */
    public $routes;
    
    /**
     * @var \Lucinda\Configurer\Features\Users
     * @message -
     */
    public $users;
    
    /**
     * @var \Lucinda\Configurer\Features\Exceptions
     * @message -
     */
    public $exceptions;
}
