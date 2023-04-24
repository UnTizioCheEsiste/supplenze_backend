<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/time.php";

class TimeController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $time = new Time();

        switch ($this->uri) {
            case "getHour":
                break;
            case "getDay":
                break;
        }
    }
}