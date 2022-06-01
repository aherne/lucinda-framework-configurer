<?php

namespace Lucinda\Configurer\Features;

use Lucinda\Configurer\Features\Exceptions\Exception;

/**
 * Sets up routes field of Features based on data already prompted by user
 */
class ExceptionsSelector
{
    public const CLASSES = [
        'Lucinda\MVC\ConfigurationException',
        'Lucinda\STDERR\PHPException',
        '\JsonException',
        'Lucinda\Headers\ConfigurationException',
        'Lucinda\Headers\UserException',
        'Lucinda\Internationalization\ConfigurationException',
        'Lucinda\Internationalization\DomainNotFoundException',
        'Lucinda\Internationalization\TranslationInvalidException',
        'Lucinda\Logging\ConfigurationException',
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

    private Exceptions $exceptions;
    private Features $features;

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
        switch ($className) {
            case 'Lucinda\STDOUT\MethodNotAllowedException':
            case 'Lucinda\STDOUT\PathNotFoundException':
            case 'Lucinda\STDOUT\ValidationFailedException':
            case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':
            case 'Lucinda\WebSecurity\Token\Exception':
                break;
            case 'Lucinda\WebSecurity\SecurityPacket':
                $exception->controller = "Lucinda\Project\Controllers\SecurityPacket";
                break;
            default:
                $exception->controller = "Lucinda\Project\Controllers\Error";
                break;
        }
        if (!$this->features->isREST) {
            switch ($className) {
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
        switch ($className) {
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
        switch ($className) {
            case 'Lucinda\STDERR\ConfigurationException':
            case 'Lucinda\Headers\ConfigurationException':
            case 'Lucinda\Internationalization\ConfigurationException':
            case 'Lucinda\Internationalization\DomainNotFoundException':
            case 'Lucinda\Logging\ConfigurationException':
            case 'Lucinda\STDOUT\ConfigurationException':
            case 'Lucinda\NoSQL\ConfigurationException':
            case 'Lucinda\NoSQL\KeyNotFoundException':
            case 'Lucinda\OAuth2\Client\Exception':
            case 'Lucinda\WebSecurity\ConfigurationException':
            case 'Lucinda\SQL\ConfigurationException':
            case 'Lucinda\SQL\Exception':
            case 'Lucinda\Templating\ConfigurationException':
            case 'Lucinda\MVC\ConfigurationException':
                $exception->error_type = "LOGICAL";
                break;
            case 'Lucinda\STDERR\PHPException':
            case '\JsonException':
            case 'Lucinda\Headers\UserException':
            case 'Lucinda\Internationalization\TranslationInvalidException':
            case 'Lucinda\Templating\ViewException':
                $exception->error_type = "SYNTAX";
                break;
            case 'Lucinda\NoSQL\ConnectionException':
            case 'Lucinda\NoSQL\OperationFailedException':
            case 'Lucinda\OAuth2\Server\Exception':
            case 'Lucinda\SQL\ConnectionException':
            case 'Lucinda\SQL\StatementException':
                $exception->error_type = "SERVER";
                break;
            case 'Lucinda\STDOUT\MethodNotAllowedException':
            case 'Lucinda\STDOUT\PathNotFoundException':
            case 'Lucinda\STDOUT\Request\UploadedFiles\Exception':
            case 'Lucinda\STDOUT\ValidationFailedException':
            case 'Lucinda\WebSecurity\Authentication\Form\Exception':
            case 'Lucinda\WebSecurity\Authentication\OAuth2\Exception':
            case 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException':
            case 'Lucinda\WebSecurity\Token\EncryptionException':
            case 'Lucinda\WebSecurity\Token\Exception':
            case 'Lucinda\WebSecurity\Token\ExpiredException':
            case 'Lucinda\WebSecurity\Token\RegenerationException':
                $exception->error_type = "CLIENT";
                break;
            case 'Lucinda\WebSecurity\SecurityPacket':
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
    public function getResults(): Exceptions
    {
        return $this->exceptions;
    }
}
