<?php
namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO;

/**
 * DAO to use when user rights are to be checked in database
 */
class UsersAuthorization extends UserAuthorizationDAO
{
    public const DRIVER_NAME = "";

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO::isAllowed()
     */
    public function isAllowed(\Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO $page, string $httpRequestMethod): bool
    {
        return \SQL("
            SELECT id FROM users_resources WHERE resource_id=:resource AND user_id=:user
        ", [
            ":user"=>$this->userID,
            ":resource"=>$page->getID()
        ], self::DRIVER_NAME)->toValue();
    }
}
