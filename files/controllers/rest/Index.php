<?php

namespace Lucinda\Project\Controllers;

use Lucinda\Framework\RestController;

/**
 * Mock controller for homepage after successful framework installation
 */
class Index extends RestController
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view()["token"] = $this->attributes->getAccessToken();
        $this->response->view()["features"] = json_decode(file_get_contents("features.json"), true);
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["user_id"] = $this->attributes->getUserId();
    }
}
