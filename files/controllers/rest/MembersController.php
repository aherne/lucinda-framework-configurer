<?php
/**
 * Mock controller for a page accessible to all logged in users
 */
class MembersController extends Lucinda\Framework\RestController
{    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view()["token"] = $this->attributes->getAccessToken();
        $this->response->view()["uid"] = $this->attributes->getUserId();
    }
}
