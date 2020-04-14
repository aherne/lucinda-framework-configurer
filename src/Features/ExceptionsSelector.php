<?php
namespace Lucinda\Configurer\Features;

use Lucinda\Configurer\Features\Exceptions\Exception;

/**
 * Sets up routes field of Features based on data already prompted by user
 */
class ExceptionsSelector
{
    const CLASSES = [
        'Lucinda\STDERR\ConfigurationException',
        'Lucinda\STDERR\PHPException',
        'Lucinda\Framework\Json\Exception',
        'Lucinda\Headers\ConfigurationException',
        'Lucinda\Headers\UserException',
        'Lucinda\Internationalization\ConfigurationException',
        'Lucinda\Internationalization\DomainNotFoundException',
        'Lucinda\Internationalization\TranslationInvalidException',
        'Lucinda\Logging\ConfigurationException',
        'Lucinda\STDOUT\ConfigurationException',
        'Lucinda\STDOUT\MethodNotAllowedException',
        'Lucinda\STDOUT\PathNotFoundException',
        'Lucinda\STDOUT\Request\UploadedFiles\Exception',
        'Lucinda\STDOUT\ValidationFailedException',
        'Lucinda\NoSQL\ConfigurationException',
        'Lucinda\NoSQL\ConnectionException',
        'Lucinda\NoSQL\KeyNotFoundException',
        'Lucinda\NoSQL\OperationFailedException',
        'Lucinda\OAuth2\Client\Exception',
        'Lucinda\OAuth2\Server\Exception',
        'Lucinda\WebSecurity\Authentication\Form\Exception',
        'Lucinda\WebSecurity\Authentication\OAuth2\Exception',
        'Lucinda\WebSecurity\ConfigurationException',
        'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException',
        'Lucinda\WebSecurity\SecurityPacket',
        'Lucinda\WebSecurity\Token\EncryptionException',
        'Lucinda\WebSecurity\Token\Exception',
        'Lucinda\WebSecurity\Token\ExpiredException',
        'Lucinda\WebSecurity\Token\RegenerationException',
        'Lucinda\SQL\ConfigurationException',
        'Lucinda\SQL\ConnectionException',
        'Lucinda\SQL\Exception',
        'Lucinda\SQL\StatementException',
        'Lucinda\Templating\ConfigurationException',
        'Lucinda\Templating\ViewException'
    ];
    
    private $exceptions;
    private $features;
    
    /**
     * @param Features $features
     */
    public function __construct(Features $features)
    {
        $this->features = $features;
        
        $this->exceptions = new Exceptions();
        foreach (self::CLASSES as $name) {
            $this->addException($name);
        }
    }
    
    /**
     * Adds exception route based on class name
     *
     * @param string $className
     */
    public function addException(string $className): void
    {
        $exception = new Exception();
        $exception->class = $className;
        switch($className) {
            case 'Lucinda\STDOUT\MethodNotAllowedException':
            case 'Lucinda\STDOUT\PathNotFoundException':
            case 'Lucinda\STDOUT\ValidationFailedException':
            case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':
            case 'Lucinda\WebSecurity\Token\Exception':
                break;
            case 'Lucinda\WebSecurity\SecurityPacket':
                $exception->controller = "SecurityPacketController";
                break;            
            default:
                $exception->controller = "ErrorsController";
                break;
        }
        if (!$this->features->isREST) {
            switch($className) {
                case 'Lucinda\STDOUT\MethodNotAllowedException':
                    $exception->view = 405;
                    break;
                case 'Lucinda\STDOUT\PathNotFoundException':
                    $exception->view = 404;
                    break;
                case 'Lucinda\STDOUT\ValidationFailedException':
                case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':
                case 'Lucinda\WebSecurity\Token\Exception':
                    $exception->view = 400;
                    break;
                default:
                    break;
            }
        }
        switch($className) {
            case 'Lucinda\STDOUT\MethodNotAllowedException':
                $exception->http_status = 405;
                break;
            case 'Lucinda\STDOUT\PathNotFoundException':
                $exception->http_status = 404;
                break;
            case 'Lucinda\STDOUT\ValidationFailedException':
            case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':
            case 'Lucinda\WebSecurity\Token\Exception':
                $exception->http_status = 400;
                break;
            default:
                $exception->http_status = 500;
                break;
        }
        switch($className) {
            case 'Lucinda\STDERR\ConfigurationException':   // logical
            case 'Lucinda\Headers\ConfigurationException':   // logical
            case 'Lucinda\Internationalization\ConfigurationException':   // logical
            case 'Lucinda\Internationalization\DomainNotFoundException':   // logical
            case 'Lucinda\Logging\ConfigurationException':   // logical
            case 'Lucinda\STDOUT\ConfigurationException':   // logical
            case 'Lucinda\NoSQL\ConfigurationException':   // logical
            case 'Lucinda\NoSQL\KeyNotFoundException':  // logical
            case 'Lucinda\OAuth2\Client\Exception':  // logical
            case 'Lucinda\WebSecurity\ConfigurationException':  // logical
            case 'Lucinda\SQL\ConfigurationException':   // logical
            case 'Lucinda\SQL\Exception': //logical
            case 'Lucinda\Templating\ConfigurationException':   // logical
                $exception->error_type = "LOGICAL";
                break;
            case 'Lucinda\STDERR\PHPException': // syntax
            case 'Lucinda\Framework\Json\Exception': // syntax
            case 'Lucinda\Headers\UserException': // syntax
            case 'Lucinda\Internationalization\TranslationInvalidException': // syntax
            case 'Lucinda\Templating\ViewException':   // syntax
                $exception->error_type = "SYNTAX";
                break;
            case 'Lucinda\NoSQL\ConnectionException':   // server
            case 'Lucinda\NoSQL\OperationFailedException':  // server
            case 'Lucinda\OAuth2\Server\Exception':  // server
            case 'Lucinda\SQL\ConnectionException': // server
            case 'Lucinda\SQL\StatementException':  // server
                $exception->error_type = "SERVER";
                break;
            case 'Lucinda\STDOUT\MethodNotAllowedException':    // client
            case 'Lucinda\STDOUT\PathNotFoundException':    // client
            case 'Lucinda\STDOUT\Request\UploadedFiles\Exception':    // client
            case 'Lucinda\STDOUT\ValidationFailedException':    // client
            case 'Lucinda\WebSecurity\Authentication\Form\Exception':   // client
            case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':    // client
            case 'Lucinda\WebSecurity\Token\EncryptionException':    // client
            case 'Lucinda\WebSecurity\Token\Exception':    // client
            case 'Lucinda\WebSecurity\Token\ExpiredException':    // client
            case 'Lucinda\WebSecurity\Token\RegenerationException':    // client
                $exception->error_type = "CLIENT";
                break;
            case 'Lucinda\WebSecurity\SecurityPacket':    // none
                $exception->error_type = "NONE";
                break;
        }
        $this->exceptions->exceptions[] = $exception;
    }
    
    /**
     * Gets all routes added
     *
     * @return Exceptions
     */
    public function getExceptions(): Exceptions
    {
        return $this->exceptions;
    }
}

