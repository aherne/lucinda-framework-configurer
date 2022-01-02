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
    public int $id;
    
    /**
     * @var string
     */
    public string $url;
    
    /**
     * @var string
     */
    public string $controller;
    
    /**
     * @var string
     */
    public string $view;
    
    /**
     * @var string
     */
    public string $roles;
    
    /**
     * @var boolean
     */
    public bool $no_cache;
    
    /**
     * @var integer
     */
    public int $cache_expiration;
    
    /**
     * @var string
     */
    public string $allowed_methods;
}
