<?php
/**
 * STDERR MVC controller that gets activated whenever an non-routed error occurs during application lifecycle.
 */
class ErrorsController extends Lucinda\STDERR\Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\Runnable::run()
     */
    public function run(): void
    {
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    /**
     * Sets response status to HTTP status code 500
     */
    private function setResponseStatus(): void
    {
        $this->response->setStatus(500);
    }
    
    /**
     * Sets response body from view file or stream.
     *
     * @throws Exception If content type of response is other than JSON or HTML.
     */
    private function setResponseBody(): void
    {
        $exception = $this->request->getException();
        if ($this->application->getDisplayErrors()) {
            $view = $this->response->view();
            $view["class"] = get_class($exception);
            $view["message"] = $exception->getMessage();
            $view["file"] = $exception->getFile();
            $view["line"] = $exception->getLine();
            $view["trace"] = $exception->getTraceAsString();
        }
    }
}
