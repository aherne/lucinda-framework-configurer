<?php
/**
 * STDERR MVC controller running whenever a Lucinda\Framework\SecurityPacket is thrown during STDOUT phase.
 */
class SecurityPacketController extends Lucinda\STDERR\Controller
{
    private $redirect;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\Runnable::run()
     */
    public function run(): void
    {
        $this->redirect = (string) $this->application->getTag("application")["redirect"];
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    /**
     * Sets response HTTP status code according to outcome of security validation
     */
    private function setResponseStatus(): void
    {
        switch ($this->request->getException()->getStatus()) {
            case "unauthorized":
                $this->response->setStatus(401);
                break;
            case "forbidden":
                $this->response->setStatus(403);
                break;
            case "not_found":
                $this->response->setStatus(404);
                break;
            default:
                $this->response->setStatus(200);
                break;
        }
    }
    
    /**
     * Sets response body from view file or stream.
     *
     * @throws Exception If content type of response is other than JSON or HTML.
     */
    private function setResponseBody(): void
    {
        // gets wrapped exception
        $exception = $this->request->getException();
        
        // sets response content
        $view = $this->response->view();
        $view["status"] = $exception->getStatus();
        $view["callback"] = $exception->getCallback();
        $view["token"] = $exception->getAccessToken();
        $view["penalty"] = $exception->getTimePenalty();
    }
}
