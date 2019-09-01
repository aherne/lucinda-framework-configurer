<?php
class PagesAuthorization extends Lucinda\WebSecurity\PageAuthorizationDAO
{
    public function isPublic()
    {
        return SQL("SELECT is_public FROM resources WHERE id=:id", array(":id"=>$this->pageID))->toValue();
    }
    
    public function detectID($path)
    {
        return SQL("SELECT id FROM resources WHERE url=:url", array(":url"=>$path))->toValue();
    }
}
