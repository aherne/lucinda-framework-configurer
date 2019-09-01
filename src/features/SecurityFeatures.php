<?php
namespace Lucinda\Configurer;

class SecurityFeatures
{
    public $persistenceDrivers;
    public $authenticationMethods;
    public $authorizationMethod;
    /**
     * @var OAuth2Provider[]
     */
    public $oauth2Providers=array();
}
