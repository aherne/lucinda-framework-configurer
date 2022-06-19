<?php

namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure Web Security API installation
 */
class Security
{
    /**
     * @var     boolean
     * @message Will your site be a CMS where all pages except login require authentication
     * @default 0
     */
    public bool $isCMS = false;

    /**
     * @var     integer
     * @message Choose mediums login state will be persisted into
     * @option  SESSION + REMEMBER ME COOKIE
     * @option  SYNCHRONIZER TOKEN
     */
    public int $persistenceDrivers;

    /**
     * @var     integer
     * @message Choose authentication method
     * @option  Using LOGIN FORM, checked in DATABASE
     * @option  Using LOGIN FORM, checked in DATABASE + OAUTH2 PROVIDERS
     * @option  Using LOGIN FORM, checked in XML (use only if users will always be predefined)
     * @default 0
     */
    public int $authenticationMethod;

    /**
     * @var     integer
     * @message Choose authorization method
     * @option  Using DATABASE
     * @option  Using XML
     */
    public int $authorizationMethod;
}
