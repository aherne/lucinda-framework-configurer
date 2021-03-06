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
     * @see \Lucinda\WebSecurity\Authorization\UserRoles::getRoles()
     */
    public function getRoles($userID): array
    {
        if ($userID) {
            return SQL("SELECT t2.name FROM user_roles AS t1
            INNER JOIN roles AS t2 ON t1.role_id = t2.id
            WHERE t1.user_id=:user", array(":user"=>$userID))->toColumn();
        } else {
            return ["GUESTS"];
        }
    }
}
