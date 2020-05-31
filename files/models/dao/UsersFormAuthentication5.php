<?php
/**
 * DAO to use if user authentication is performed via forms
 */
class UsersFormAuthentication implements Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO, Lucinda\WebSecurity\Authorization\UserRoles
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO::login()
     */
    public function login(string $username, string $password)
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
    public function logout($userID): void
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\UserRoles::getRoles()
     */
    public function getRoles($userID): array
    {
        return ($userID?["MEMBERS"]:["GUESTS"]);
    }
}
