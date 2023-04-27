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
                $hours = $time->getHour();
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $hours]);
                break;
            case "getDay":
                $days = $time->getDay();
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $days]);
                break;
        }
    }
}