<?php
class UsersAuthorization extends Lucinda\WebSecurity\UserAuthorizationDAO
{
    public function isAllowed(Lucinda\WebSecurity\PageAuthorizationDAO $page, $httpRequestMethod)
    {
        return SQL("{QUERY}", array(":user"=>$this->userID, ":resource"=>$page->getID()))->toValue();
    }
}
