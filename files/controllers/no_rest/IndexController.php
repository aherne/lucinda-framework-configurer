<?php
use Lucinda\STDOUT\Controller;

/**
 * Mock controller for homepage after successful framework installation
 */
class IndexController extends Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        $this->response->view()["features"] = json_decode('{FEATURES}', true);
        $this->response->view()["status"] = $this->request->parameters("status");
    }
}
