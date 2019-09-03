<?php
class UsersOAuth2Authentication implements Lucinda\WebSecurity\OAuth2AuthenticationDAO
{
    public function login(Lucinda\WebSecurity\OAuth2UserInformation $userInformation, $accessToken)
    {
        // get driver ID
        $driver = str_replace(array("Lucinda\\Framework\\", "UserInformation"), "", get_class($userInformation));
        $driverID = SQL(
            "SELECT id FROM oauth2_providers WHERE name=:driver",
            array(":driver"=>$driver)
            )->toValue();
        
        // detects user based on driver and remote id
        $userID = SQL(
            "SELECT user_id FROM users__oauth2 WHERE driver_id=:driver AND remote_user_id=:remote_user",
            array(":driver"=>$driverID, ":remote_user"=>$userInformation->getId())
            )->toValue();
        if ($userID) {
            SQL(
                "UPDATE users__oauth2 SET access_token=:access_token WHERE driver_id=:driver AND remote_user_id=:remote_user",
                array(":driver"=>$driverID, ":remote_user"=>$userInformation->getId(), ":access_token"=>$accessToken)
            );
            return $userID;
        }
        
        // detects user based on email
        $userID = SQL("SELECT id FROM users WHERE email=:email", array(":email"=>$userInformation->getEmail()))->toValue();
        if ($userID) {
            SQL(
                "INSERT INTO users__oauth2 (user_id, remote_user_id, driver_id, access_token) VALUES (:user_id, :remote_user,  :driver, :access_token)",
                array(":user_id"=>$userID, ":remote_user"=>$userInformation->getId(), ":driver"=>$driverID, ":access_token"=>$accessToken)
            );
            return $userID;
        }
        
        // creates user
        $userID = SQL(
            "INSERT INTO users (name, email) VALUES (:name, :email)",
            array(":name"=>$userInformation->getName(), ":email"=>$userInformation->getEmail())
            )->getInsertId();
        SQL(
            "INSERT INTO users__oauth2 (user_id, remote_user_id, driver_id, access_token) VALUES (:user_id, :remote_user,  :driver, :access_token)",
            array(":user_id"=>$userID, ":remote_user"=>$userInformation->getId(), ":driver"=>$driverID, ":access_token"=>$accessToken)
        );
        return $userID;
    }
    
    public function logout($userID)
    {
        SQL("UPDATE users__oauth2 SET access_token = '' WHERE user_id = :user_id", array(":user_id"=>$userID));
    }
}
