<?php
namespace Lucinda\Configurer\Features\Headers;

/**
 * Struct encapsulating options to configure CORS requests validation
 */
class CORS
{
    /**
     * @var boolean
     * @message Choose whether or not credentials are allowed in CORS requests
     * @default 1
     */
    public $allow_credentials;
    
    /**
     * @var integer
     * @message Choose duration in seconds CORS responses will be cached
     * @default 0
     * @validator ([0-9]+)
     */
    public $max_age;
    
    /**
     * @var string
     * @message Request headers that are by default allowed in your site, separated by comma
     * @optional
     */
    public $allowed_request_headers;
    
    /**
     * @var string
     * @message Response headers that are by default exposed by your site, separated by comma
     * @optional
     */
    public $allowed_response_headers;
}