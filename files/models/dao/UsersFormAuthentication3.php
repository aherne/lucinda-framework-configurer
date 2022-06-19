<?php

namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO;
use Lucinda\WebSecurity\Authorization\UserRoles;

/**
 * DAO to use if user authentication is performed via forms
 */
class UsersFormAuthentication implements UserAuthenticationDAO, UserRoles
{
    public const DRIVER_NAME = "";

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO::login()
     */
    public function login(string $username, string $password): int|string|null
    {
        $result = \SQL(
            "
            SELECT id AS user_id, password FROM users WHERE username=:user
        ",
            [
            ":user"=>$username
            ],
            self::DRIVER_NAME
        )->toRow();
        if (empty($result) || !password_verify($password, $result["password"])) {
            return null; // login failed
        }
        return $result["user_id"];
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO::logout()
     */
    public function logout(int|string $userID): void
    {
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authorization\UserRoles::getRoles()
     */
    public function getRoles(int|string|null $userID): array
    {
        if ($userID) {
            return \SQL(
                "
                SELECT t2.name FROM user_roles AS t1
                INNER JOIN roles AS t2 ON t1.role_id = t2.id
                WHERE t1.user_id=:user
            ",
                [
                ":user"=>$userID
                ],
                self::DRIVER_NAME
            )->toColumn();
        } else {
            return ["GUESTS"];
        }
    }
}
