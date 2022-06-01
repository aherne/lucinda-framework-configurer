<?php

namespace Lucinda\Project\Controllers;

use Lucinda\STDOUT\Controller;

/**
 * Mock controller for homepage after successful framework installation
 */
class Index extends Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["features"] = json_decode(file_get_contents("features.json"), true);
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["user_id"] = $this->attributes->getUserId();
        $this->response->view()["version"] = $this->application->getVersion();
    }
}
