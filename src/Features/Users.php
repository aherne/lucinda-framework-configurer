<?php
namespace Lucinda\Configurer\Features;

/**
 * Struct encapsulating options to configure WEB SECURITY API user policies
 */
class Users
{
    /**
     * @var \Lucinda\Configurer\Features\Users\User[]
     */
    public $users = [];
    
    /**
     * @var string
     */
    public $default_roles;
}
