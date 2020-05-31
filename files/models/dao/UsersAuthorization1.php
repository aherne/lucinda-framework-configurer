<?php
/**
 * DAO to use when user rights are to be checked in database
 */
class UsersAuthorization extends Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO::isAllowed()
     */
    public function isAllowed(\Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO $page, string $httpRequestMethod): bool
    {
        return SQL("
            SELECT t1.id FROM roles_resources AS t1
            INNER JOIN users_roles AS t2 USING(role_id)
            WHERE t1.resource_id = :resource AND t2.user_id=:user", 
            array(":user"=>$this->userID, ":resource"=>$page->getID())
            )->toValue();
    }
}
