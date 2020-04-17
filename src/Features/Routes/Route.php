<?php
namespace Lucinda\Configurer\Features\Routes;

/**
 * Struct encapsulating options to configure STDOUT MVC API / WEB SECURITY API / HTTP HEADERS API route
 */
class Route
{
    /**
     * @var integer
     */
    public $id;
    
    /**
     * @var string
     */
    public $url;
    
    /**
     * @var string
     */
    public $controller;
    
    /**
     * @var string
     */
    public $view;
    
    /**
     * @var string
     */
    public $roles;
    
    /**
     * @var boolean
     */
    public $no_cache;
    
    /**
     * @var integer
     */
    public $cache_expiration;
    
    /**
     * @var string
     */
    public $allowed_methods;
}
