<?php
namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO;

/**
 * DAO to use if user authentication is performed via forms
 */
class UsersFormAuthentication implements UserAuthenticationDAO
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO::login()
     */
    public function login(string $username, string $password): int|string|null
    {
        $result = SQL("SELECT id AS user_id, password FROM users WHERE username=:user", array(":user"=>$username))->toRow();
        if (empty($result) || !password_verify($password, $result["password"])) {
            return null; // login failed
        }
        return $result["user_id"];
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO::logout()
     */
    public function logout(int|string $userID): void
    {
    }
}
