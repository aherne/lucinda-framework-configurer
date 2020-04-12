<?php
/**
 * Mock controller for a page accessed only by privileged users
 */
class RestrictedController extends Lucinda\Framework\RestController
{
    /**
     * @var \Lucinda\Framework\Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view()["csrf"] = $this->attributes->getCsrfToken();
        $this->response->view()["status"] = $this->request->parameters("status");
        $this->response->view()["wait"] = (string) $this->request->parameters("wait");
    }
}
