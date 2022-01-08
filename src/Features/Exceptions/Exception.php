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
    public ?string $controller = null;
    
    /**
     * @var string
     */
    public ?string $view = null;
    
    /**
     * @var string
     */
    public string $error_type;
    
    /**
     * @var int
     */
    public ?int $http_status = null;
}
