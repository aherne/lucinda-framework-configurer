<?php
namespace Lucinda\Configurer\Features\Exceptions;

/**
 * Struct encapsulating options to configure STDERR MVC API route
 */
class Exception
{
    /**
     * @var string
     */
    public string $class;
    
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
    public string $error_type;
    
    /**
     * @var boolean
     */
    public bool $http_status;
}
