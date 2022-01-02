<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure STDOUT MVC API / WEB SECURITY API / HTTP HEADERS API routes
 */
class Routes
{
    /**
     * @var string
     */
    public string $default_roles;
    
    /**
     * @var \Lucinda\Configurer\Features\Routes[]
     */
    public array $routes = [];
}
