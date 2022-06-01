<?php

namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO;

/**
 * DAO to use when route rights are to be checked in database
 */
class PagesAuthorization extends PageAuthorizationDAO
{
    public const DRIVER_NAME = "";

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO::isPublic()
     */
    public function isPublic(): bool
    {
        return \SQL("
            SELECT is_public FROM resources WHERE id=:id
        ", [
            ":id"=>$this->pageID
        ], self::DRIVER_NAME)->toValue();
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO::detectID()
     */
    public function detectID(string $pageURL): ?int
    {
        return \SQL("
            SELECT id FROM resources WHERE url=:url
        ", [
            ":url"=>$pageURL
        ], self::DRIVER_NAME)->toValue();
    }
}
