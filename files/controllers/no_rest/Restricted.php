<?php

namespace Lucinda\Project\Controllers;

use Lucinda\STDOUT\Controller;

/**
 * Mock controller for a page accessed only by privileged users
 */
class Restricted extends Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["uid"] = $this->attributes->getUserId();
        $this->response->view()["version"] = $this->application->getVersion();
    }
}
