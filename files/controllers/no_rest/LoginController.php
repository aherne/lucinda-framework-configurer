<?php
/**
 * Mock controller for login page
 */
class LoginController extends Lucinda\STDOUT\Controller
{
    /**
     * @var \Lucinda\Framework\Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run()
    {
        $this->response->view()["csrf"] = $this->attributes->getCsrfToken();
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["wait"] = (string) $this->request->parameters("wait");
    }
}
