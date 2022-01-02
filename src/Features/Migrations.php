<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure Migrations API installation
 */
class Migrations
{    
    /**
     * @var integer
     * @message Choose where migrations progress will be stored
     * @option SQL table 'migrations'
     * @option NoSQL key 'migrations'
     * @default 0
     */
    public int $storageMethod;
}
