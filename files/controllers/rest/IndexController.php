<?php
/**
 * Mock controller for homepage after successful framework installation
 */
class IndexController extends Lucinda\Framework\RestController
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    protected function GET()
    {
        $this->response->view["features"] = json_decode('{FEATURES}', true);
        $this->response->view["status"] = $this->request->parameters("status");
    }
}
