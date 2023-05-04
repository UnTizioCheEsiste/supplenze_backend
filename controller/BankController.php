<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/bank.php";

class BankController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $bank = new Bank();

        switch ($this->uri) {
            case "getArchiveCountHoursBank":

                break;
            case "getUserHoursBank":

                break;
            case "getUserCountHoursBank":

                break;
            case "addUserHoursBank":
                
                break;
        }
    }
}