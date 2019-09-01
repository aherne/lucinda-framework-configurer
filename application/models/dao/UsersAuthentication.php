<?php
class UsersAuthentication implements Lucinda\WebSecurity\UserAuthenticationDAO
{
    public function login($username, $password)
    {
        $result = SQL("{QUERY}", array(":user"=>$username))->toRow();
        if (empty($result) || !password_verify($password, $result["password"])) {
            return null; // login failed
        }
        return $result["user_id"];
    }

    public function logout($userID)
    {
    }
}
