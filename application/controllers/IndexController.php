<?php
class IndexController extends Lucinda\MVC\STDOUT\Controller
{
    public function run()
    {
        $this->response->attributes("features", json_decode('{FEATURES}', true));
        $this->response->attributes("status", $this->request->parameters("status"));
    }
}
