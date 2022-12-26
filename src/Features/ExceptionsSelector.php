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
        $exception->controller = $this->getController($className);
        if (!$this->features->isREST) {
            $exception->view = $this->getView($className);
        }
        $exception->http_status = $this->getHttpStatus($className);
        $exception->error_type = $this->getErrorType($className);
        $this->exceptions->exceptions[] = $exception;
    }

    /**
     * Detects controller that matches throwable class
     *
     * @param  string $className
     * @return string|null
     */
    private function getController(string $className): ?string
    {
        return match ($className) {
            'Lucinda\STDOUT\MethodNotAllowedException', 'Lucinda\STDOUT\PathNotFoundException', 'Lucinda\STDOUT\ValidationFailedException', 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException', 'Lucinda\WebSecurity\Token\Exception' => null,
            'Lucinda\WebSecurity\SecurityPacket' => "Lucinda\Project\Controllers\SecurityPacket",
            default => "Lucinda\Project\Controllers\Error",
        };
    }

    /**
     * Detects view that matches throwable class
     *
     * @param  string $className
     * @return string|null
     */
    private function getView(string $className): ?string
    {
        return match ($className) {
            'Lucinda\STDOUT\MethodNotAllowedException' => "405",
            'Lucinda\STDOUT\PathNotFoundException' => "404",
            'Lucinda\STDOUT\ValidationFailedException', 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException', 'Lucinda\WebSecurity\Token\Exception' => "400",
            default => null,
        };
    }

    /**
     * Detects response http status that matches throwable class
     *
     * @param  string $className
     * @return int
     */
    private function getHttpStatus(string $className): int
    {
        return match ($className) {
            'Lucinda\STDOUT\MethodNotAllowedException' => 405,
            'Lucinda\STDOUT\PathNotFoundException' => 404,
            'Lucinda\STDOUT\ValidationFailedException', 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException', 'Lucinda\WebSecurity\Token\Exception' => 400,
            default => 500,
        };
    }

    /**
     * Detects error type that matches throwable class
     *
     * @param  string $className
     * @return string
     */
    private function getErrorType(string $className): string
    {
        return match ($className) {
            'Lucinda\STDERR\ConfigurationException', 'Lucinda\Headers\ConfigurationException','Lucinda\Internationalization\ConfigurationException', 'Lucinda\Internationalization\DomainNotFoundException', 'Lucinda\Logging\ConfigurationException', 'Lucinda\STDOUT\ConfigurationException', 'Lucinda\NoSQL\ConfigurationException', 'Lucinda\NoSQL\KeyNotFoundException', 'Lucinda\OAuth2\Client\Exception', 'Lucinda\WebSecurity\ConfigurationException', 'Lucinda\SQL\ConfigurationException', 'Lucinda\SQL\Exception', 'Lucinda\Templating\ConfigurationException', 'Lucinda\MVC\ConfigurationException' => "LOGICAL",
            'Lucinda\STDERR\PHPException', '\JsonException', 'Lucinda\Headers\UserException', 'Lucinda\Internationalization\TranslationInvalidException', 'Lucinda\Templating\ViewException' => "SYNTAX",
            'Lucinda\NoSQL\ConnectionException', 'Lucinda\NoSQL\OperationFailedException', 'Lucinda\OAuth2\Server\Exception', 'Lucinda\SQL\ConnectionException', 'Lucinda\SQL\StatementException' => "SERVER",
            'Lucinda\STDOUT\MethodNotAllowedException', 'Lucinda\STDOUT\PathNotFoundException', 'Lucinda\STDOUT\Request\UploadedFiles\Exception', 'Lucinda\STDOUT\ValidationFailedException', 'Lucinda\WebSecurity\Authentication\Form\Exception', 'Lucinda\WebSecurity\Authentication\OAuth2\Exception', 'Lucinda\WebSecurity\PersistenceDrivers\Session\HijackException', 'Lucinda\WebSecurity\Token\EncryptionException', 'Lucinda\WebSecurity\Token\Exception', 'Lucinda\WebSecurity\Token\ExpiredException', 'Lucinda\WebSecurity\Token\RegenerationException' => "CLIENT",
            'Lucinda\WebSecurity\SecurityPacket' => "NONE",
            default => "SYNTAX",
        };
        // this shouldn't be triggered
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
