<?php
/**
 * DAO to use if user authentication is performed via forms
 */
class UsersFormAuthentication implements Lucinda\WebSecurity\Authorization\UserRoles
{    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\UserRoles::getRoles()
     */
    public function getRoles($userID): array
    {
        return ($userID?["MEMBERS"]:["GUESTS"]);
    }
}
