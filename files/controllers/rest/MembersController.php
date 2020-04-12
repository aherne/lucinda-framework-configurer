<?php
/**
 * Mock controller for a page accessible to all logged in users
 */
class MembersController extends Lucinda\Framework\RestController
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
        $this->response->attributes("uid", $this->attributes->getUserId());
    }
}
