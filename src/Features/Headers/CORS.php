<?php

namespace Lucinda\Configurer\Features\Headers;

/**
 * Struct encapsulating options to configure CORS requests validation
 */
class CORS
{
    /**
     * @var     boolean
     * @message Choose whether or not credentials are allowed in CORS requests
     * @default 1
     */
    public bool $allow_credentials = true;

    /**
     * @var       integer
     * @message   Choose duration in seconds CORS responses will be cached
     * @default   0
     * @validator ([0-9]+)
     */
    public int $max_age = 0;

    /**
     * @var      string
     * @message  Request headers that are by default allowed in your site, separated by comma
     * @optional
     */
    public ?string $allowed_request_headers = null;

    /**
     * @var      string
     * @message  Response headers that are by default exposed by your site, separated by comma
     * @optional
     */
    public ?string $allowed_response_headers = null;
}
