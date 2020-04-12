<?php
namespace Lucinda\Configurer\Features\Users;

/**
 * Struct encapsulating options to configure site user
 */
class User {
    /**
     * @var integer
     */
    public $id;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $email;
    
    /**
     * @var string
     */
    public $username;
    
    /**
     * @var string
     */
    public $password;
    
    /**
     * @var string
     */
    public $roles;
}