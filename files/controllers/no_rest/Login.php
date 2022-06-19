<?php

namespace Lucinda\Project\Controllers;

use Lucinda\STDOUT\Controller;

/**
 * Mock controller for login page
 */
class Login extends Controller
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["csrf"] = $this->attributes->getCsrfToken();
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["wait"] = (string) $this->request->parameters("wait");
        $this->response->view()["version"] = $this->application->getVersion();
    }
}
