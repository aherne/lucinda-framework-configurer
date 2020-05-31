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
    public $class;
    
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
    public $error_type;
    
    /**
     * @var boolean
     */
    public $http_status;
}
