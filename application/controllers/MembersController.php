<?php
class MembersController extends Lucinda\MVC\STDOUT\Controller
{
    public function run()
    {
        $this->response->attributes("uid", $this->request->attributes("user_id"));
    }
}
