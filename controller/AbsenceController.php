<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/absence.php";

class AbsenceController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $absence= new Absence();

        switch ($this->uri) {
            case "getArchiveAbsence":
                // (5)
                $absence->getArchiveAbsence();
                break;
            case "getAbsence":
                // (6)
                break;
            case "addCertificate":
                // (7)
                break;
            case "addAbsenceHour":
                // (1)
                break;
            case "addAbsenceSingleDay":
                // (2)
                break;
            case "addAbsenceMultipleDay":
                // (3)
                break;
            case "ungroupAbsence":
                // (4)
                $params = $this->getQueryStringParams();
                if(empty($params["id"]))
                {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Assenza non trovata"]);
                    break;
                }else{
                    $success = $absence->ungroupAbsence($params['id']);

                if (empty($success)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Assenza non trovata"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $success]);
                break;
                }
        }
    }
}