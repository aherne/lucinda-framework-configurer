<?php
class LoginController extends Lucinda\MVC\STDOUT\Controller
{
    public function run()
    {
        $this->response->attributes("csrf", $this->request->attributes("csrf")->generate(0));
        $this->response->attributes("status", $this->request->parameters("status"));
        $this->response->attributes("wait", (string) $this->request->parameters("wait"));
    }
}
