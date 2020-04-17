<?php
namespace Lucinda\Configurer\Features;

use Lucinda\Configurer\Features\Users\User;

/**
 * Sets up users field of Features based on data already prompted by user
 */
class UsersSelector
{
    private $users;
    private $features;
    
    /**
     * @param Features $features
     */
    public function __construct(Features $features)
    {
        if (!$features->security) {
            return;
        }
        $this->features = $features;
        $this->users = new Users();
        $this->addUser("John Doe");
        $this->addUser("Jane Doe");
    }
    
    /**
     * Adds user based on name
     *
     * @param string $name
     */
    private function addUser(string $name): void
    {
        $user = new User();
        $user->name = $name;
        switch ($name) {
            case "John Doe":
                $user->id = 1;
                $user->username = "john";
                $user->email = "john@doe.com";
                $user->password = password_hash("doe", PASSWORD_BCRYPT);
                $user->roles = $this->features->security->isCMS?"MEMBERS,ADMINISTRATORS":"MEMBERS";
                break;
            case "Jane Doe":
                $user->id = 2;
                $user->username = "jane";
                $user->email = "jane@doe.com";
                $user->password = password_hash("doe", PASSWORD_BCRYPT);
                $user->roles = "MEMBERS";
                break;
        }
        $this->users->users[] = $user;
    }
    
    /**
     * Gets all users added
     *
     * @return Users|NULL
     */
    public function getResults(): ?Users
    {
        return $this->users;
    }
}
