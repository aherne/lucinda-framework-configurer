<?php
namespace Lucinda\Project\Controllers;

use Lucinda\Framework\RestController;

/**
 * Mock controller for a page accessible to all logged in users
 */
class Members extends RestController
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view()["token"] = $this->attributes->getAccessToken();
        $this->response->view()["uid"] = $this->attributes->getUserId();
    }
}
