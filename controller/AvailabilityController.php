<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/availability.php";

class AvailabilityController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $ava= new Availability();

        switch ($this->uri) {
            case "getArchiveAvailability":
                break;
            case "getArchiveAvailabilityHour":
                break;
            case "addAvailability":
                break;
            case "removeAvailability":
                break;
            case "getArchiveTypeAvailability":
                break;
        }
    }
}