<?php

namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure Headers API installation
 */
class Headers
{
    /**
     * @var     \Lucinda\Configurer\Features\Headers\Caching
     * @message Do you want to cache response from your site in clients' browser for higher performance (STRONGLY RECOMMENDED)
     * @default 1
     */
    public ?\Lucinda\Configurer\Features\Headers\Caching $caching = null;

    /**
     * @var     \Lucinda\Configurer\Features\Headers\CORS
     * @message Do you want to answer OPTIONS requests that point to pages in your site for CORS validation
     * @default 0
     */
    public ?\Lucinda\Configurer\Features\Headers\CORS $cors = null;
}
