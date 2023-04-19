<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/user.php";

class UserController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        switch ($this->uri) {
            case "getUser":
                $params = $this->getQueryStringParams();
                $user = new User();
                $user->getUser($params['id']);
                break;
        }
    }
}
