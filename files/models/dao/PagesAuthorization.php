<?php
/**
 * DAO to use when route rights are to be checked in database
 */
class PagesAuthorization extends Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO::isPublic()
     */
    public function isPublic(): bool
    {
        return SQL("SELECT is_public FROM resources WHERE id=:id", array(":id"=>$this->pageID))->toValue();
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO::detectID()
     */
    public function detectID(string $pageURL): ?int
    {
        return SQL("SELECT id FROM resources WHERE url=:url", array(":url"=>$pageURL))->toValue();
    }
}
