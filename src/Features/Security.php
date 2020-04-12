<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure Web Security API installation
 */
class Security
{
    /**
     * @var boolean
     * @message Will your site be a CMS where all pages except login require authentication
     * @default 0
     */
    public $isCMS;
    
    /**
     * @var integer
     * @message Choose mediums login state will be persisted into
     * @option SESSION + REMEMBER ME COOKIE (available if site IS NOT a REST API) 
     * @option SESSION (available if site IS NOT a REST API)
     * @option SYNCHRONIZER TOKEN  (available if site IS a REST API)
     * @option JSON WEB TOKEN  (available if site IS a REST API)
     */
    public $persistenceDrivers;
    
    /**
     * @var integer
     * @message Choose authentication method
     * @option Using LOGIN FORM, checked in DATABASE
     * @option Using LOGIN FORM, checked in DATABASE + OAUTH2 PROVIDERS
     * @option Using LOGIN FORM, checked in XML (use this if your site will always have same users)
     * @default 0
     */
    public $authenticationMethod;
    
    /**
     * @var integer
     * @message Choose authorization method
     * @option Using DATABASE (recommended if site IS a CMS)
     * @option Using XML (recommended if site IS NOT a CMS)
     */
    public $authorizationMethod;
}
