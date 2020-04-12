<?php
/**
 * Mock controller for a page accessible to all logged in users
 */
class MembersController extends Lucinda\STDOUT\Controller
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
        $this->response->attributes("uid", $this->attributes->getUserId());
    }
}
