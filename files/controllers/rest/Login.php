<?php

namespace Lucinda\Project\Controllers;

use Lucinda\Framework\RestController;

/**
 * Mock controller for login page
 */
class Login extends RestController
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view()["csrf"] = $this->attributes->getCsrfToken();
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["wait"] = (string) $this->request->parameters("wait");
    }
}
