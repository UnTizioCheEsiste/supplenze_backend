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
        $ava = new Availability();

        switch ($this->uri) {
            case "getArchiveAvailability":
                $params = $this->getQueryStringParams();
                if(empty($params["data"])){
                    http_response_code(200);
                    echo json_encode(["success" => false, "data" => "Non è specificata la data"]);
                    break;
                } else if (strtotime($params["data"])) { // se è una data
                    $archiveAva = $ava->getArchiveAvailability($params["data"], true);
                } else if(is_string($params["data"])){ // se è un giorno della settimana
                    $archiveAva = $ava->getArchiveAvailability($params["data"], false);
                }

                if(empty($archiveAva)){
                    http_response_code(200);
                    echo json_encode(["success" => false, "data" => "Non è presente alcuna disponibilità"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveAva]);
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