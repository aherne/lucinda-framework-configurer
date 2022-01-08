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
    public ?string $controller = null;
    
    /**
     * @var string
     */
    public ?string $view = null;
    
    /**
     * @var string
     */
    public ?string $roles = null;
    
    /**
     * @var boolean
     */
    public bool $no_cache = false;
    
    /**
     * @var integer
     */
    public ?int $cache_expiration = null;
    
    /**
     * @var string
     */
    public ?string $allowed_methods = null;
}
