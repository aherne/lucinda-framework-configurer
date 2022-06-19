<?php

namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authorization\UserRoles;

/**
 * DAO to use if user authentication is performed via forms
 */
class UsersFormAuthentication implements UserRoles
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authorization\UserRoles::getRoles()
     */
    public function getRoles(int|string|null $userID): array
    {
        return ($userID ? ["MEMBERS"] : ["GUESTS"]);
    }
}
