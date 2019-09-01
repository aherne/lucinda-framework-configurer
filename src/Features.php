<?php
namespace Lucinda\Configurer;

require_once("features/InternationalizationFeatures.php");
require_once("features/NoSQLServerFeatures.php");
require_once("features/OAuth2Provider.php");
require_once("features/SecurityFeatures.php");
require_once("features/SQLServerFeatures.php");

/**
 * Struct containing features available for installation
 */
class Features
{
    /**
     * @var string
     */
    public $siteType;
    /**
     * @var boolean|null
     */
    public $logging;
    /**
     * @var boolean|null
     */
    public $validation;
    /**
     * @var boolean|null
     */
    public $templating;
    /**
     * @var InternationalizationFeatures|null
     */
    public $internationalization;
    /**
     * @var boolean|null
     */
    public $caching;
    /**
     * @var SQLServerFeatures|null
     */
    public $sqlServer;
    /**
     * @var NoSQLServerFeatures|null
     */
    public $nosqlServer;
    /**
     * @var SecurityFeatures|null
     */
    public $security;
}
