<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure Internationalization API installation
 */
class Internationalization
{
    /**
     * @var string
     * @message Write the default locale your site will be using
     * @default en_US
     * @validator ([a-z]{2}_[A-Z]{2})
     */
    public $defaultLocale;
    
    /**
     * @var integer
     * @message Choose method user locale (language) will be detected with
     * @option Based on request header 'Accept-Language'
     * @option By value of request parameter 'locale'
     * @option By value of session parameter 'locale'
     * @default 0
     */
    public $detectionMethod;
}
