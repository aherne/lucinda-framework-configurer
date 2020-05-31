<?php
/**
 * Mock controller for a page accessed only by privileged users
 */
class RestrictedController extends Lucinda\STDOUT\Controller
{
    /**
     * @var \Lucinda\Framework\Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["uid"] = $this->attributes->getUserId();
    }
}
