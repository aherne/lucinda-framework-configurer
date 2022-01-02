<?php
namespace Lucinda\Configurer\Features\Users;

/**
 * Struct encapsulating options to configure site user
 */
class User
{
    /**
     * @var integer
     */
    public int $id;
    
    /**
     * @var string
     */
    public string $name;
    
    /**
     * @var string
     */
    public string $email;
    
    /**
     * @var string
     */
    public string $username;
    
    /**
     * @var string
     */
    public string $password;
    
    /**
     * @var string
     */
    public string $roles;
}
