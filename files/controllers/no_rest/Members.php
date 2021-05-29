<?php
namespace Lucinda\Project\Controllers;

use Lucinda\STDOUT\Controller;

/**
 * Mock controller for a page accessible to all logged in users
 */
class Members extends Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["uid"] = $this->attributes->getUserId();
    }
}
