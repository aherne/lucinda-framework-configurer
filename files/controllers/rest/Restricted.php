<?php

namespace Lucinda\Project\Controllers;

use Lucinda\Framework\RestController;

/**
 * Mock controller for a page accessed only by privileged users
 */
class Restricted extends RestController
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
