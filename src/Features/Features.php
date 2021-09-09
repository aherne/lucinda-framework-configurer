<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct containing features available for installation
 */
class Features
{    
    /**
     * @var boolean
     * @message Will your project be a REST-ful web service
     * @default 0
     */
    public $isREST;
    
    /**
     * @var boolean
     * @message Will your project be load balanced on multiple web servers
     * @default 0
     */
    public $isLoadBalanced;
    
    /**
     * @var boolean
     * @message Do you want to enable logging abilities
     * @default 1
     */
    public $logging;
    
    /**
     * @var \Lucinda\Configurer\Features\Headers
     * @message Do you want to be able to use HTTP caching or CORS validation (RECOMMENDED)
     * @default 1
     */
    public $headers;
    
    /**
     * @var \Lucinda\Configurer\Features\Internationalization
     * @message Is your project expected to display in multiple languages
     * @default 0
     */
    public $internationalization;
    
    /**
     * @var \Lucinda\Configurer\Features\SQLServer
     * @message Will you use SQL databases (eg: MySQL) in your project
     * @default 1
     */
    public $sqlServer;
    
    /**
     * @var \Lucinda\Configurer\Features\NoSQLServer
     * @message Will you use NoSQL key-value store databases (eg: Redis) in your project
     * @default 0
     */
    public $nosqlServer;
    
    /**
     * @var \Lucinda\Configurer\Features\Migrations
     * @message Will you require DB migrations in your project
     * @default 0
     */
    public $migrations;
    
    /**
     * @var \Lucinda\Configurer\Features\Security
     * @message Do you want SOME/ALL pages in your site to be protected by login
     * @default 0
     */
    public $security;
    
    /**
     * @var \Lucinda\Configurer\Features\Routes
     * @message -
     * @handler \Lucinda\Configurer\Features\RoutesSelector
     */
    public $routes;
    
    /**
     * @var \Lucinda\Configurer\Features\Users
     * @message -
     * @handler \Lucinda\Configurer\Features\UsersSelector
     */
    public $users;
    
    /**
     * @var \Lucinda\Configurer\Features\Exceptions
     * @message -
     * @handler \Lucinda\Configurer\Features\ExceptionsSelector
     */
    public $exceptions;
}
