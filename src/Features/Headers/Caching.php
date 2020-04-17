<?php
namespace Lucinda\Configurer\Features\Headers;

/**
 * Struct encapsulating options to configure HTTP caching
 */
class Caching
{
    /**
     * @var boolean
     * @message Do you want HTTP caching to be disabled by default and allowed on a route basis only
     * @default 0
     */
    public $no_cache = 0;
    
    /**
     * @var integer
     * @message Write how many seconds you want response to be served from browser cache without any server round-trip
     * @default 0
     * @validator ([0-9]+)
     */
    public $expiration = 0;
}
