<?php
namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authentication\OAuth2\VendorAuthenticationDAO;
use Lucinda\WebSecurity\Authentication\OAuth2\UserInformation;
use Lucinda\Framework\OAuth2\UserDAO;

/**
 * DAO to use if user authentication is performed via OAuth2 providers
 */
class UsersOAuth2Authentication implements VendorAuthenticationDAO, UserDAO
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\OAuth2\VendorAuthenticationDAO::login()
     */
    public function login(UserInformation $userInformation, string $vendorName, string $accessToken)
    {
        // email is mandatory
        if (!$userInformation->getEmail()) {
            return;
        }
        
        // get driver ID
        $driverID = SQL(
            "SELECT id FROM oauth2_providers WHERE name=:driver",
            [":driver"=>$vendorName]
        )->toValue();
        
        // driver must exist
        if (!$driverID) {
            return;
        }
        
        // detects user based on driver and remote id
        $userID = SQL(
            "SELECT user_id FROM users__oauth2 WHERE driver_id=:driver AND remote_user_id=:remote_user",
            [":driver"=>$driverID, ":remote_user"=>$userInformation->getId()]
        )->toValue();
        if ($userID) {
            SQL(
                "UPDATE users__oauth2 SET access_token=:access_token WHERE driver_id=:driver AND remote_user_id=:remote_user",
                [":driver"=>$driverID, ":remote_user"=>$userInformation->getId(), ":access_token"=>$accessToken]
            );
            return $userID;
        }
        
        // detects user based on email
        $userID = SQL("SELECT id FROM users WHERE email=:email", [":email"=>$userInformation->getEmail()])->toValue();
        if ($userID) {
            SQL(
                "INSERT INTO users__oauth2 (user_id, remote_user_id, driver_id, access_token) VALUES (:user_id, :remote_user,  :driver, :access_token)",
                [":user_id"=>$userID, ":remote_user"=>$userInformation->getId(), ":driver"=>$driverID, ":access_token"=>$accessToken]
            );
            return $userID;
        }
        
        // creates user
        $userID = SQL(
            "INSERT INTO users (name, email) VALUES (:name, :email)",
            [":name"=>$userInformation->getName(), ":email"=>$userInformation->getEmail()]
        )->getInsertId();
        SQL(
            "INSERT INTO users__oauth2 (user_id, remote_user_id, driver_id, access_token) VALUES (:user_id, :remote_user,  :driver, :access_token)",
            [":user_id"=>$userID, ":remote_user"=>$userInformation->getId(), ":driver"=>$driverID, ":access_token"=>$accessToken]
        );
        return $userID;
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\OAuth2\VendorAuthenticationDAO::logout()
     */
    public function logout($userID): void
    {
        SQL("UPDATE users__oauth2 SET access_token = '' WHERE user_id = :user_id", [":user_id"=>$userID]);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\OAuth2\UserDAO::getVendor()
     */
    public function getVendor($userID): ?string
    {
        return SQL("
            SELECT t2.name FROM users__oauth2 AS t1
            INNER JOIN oauth2_providers AS t2 ON t1.driver_id = t2.id
            WHERE t1.user_id=:user_id
            ", [":user_id"=>$userID])->toValue();
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\OAuth2\UserDAO::getAccessToken()
     */
    public function getAccessToken($userID): ?string
    {
        return SQL("SELECT access_token FROM users__oauth2 WHERE user_id=:user_id", [":user_id"=>$userID])->toValue();
    }
}
