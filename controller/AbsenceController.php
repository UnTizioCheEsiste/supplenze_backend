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
                $absence->getArchiveAbsence();
                break;
            case "getAbsence":
                break;
            case "addCertificate":
                break;
            case "addAbsenceHour":
                break;
            case "addAbsenceSingleDay":
                break;
            case "addAbsenceMultipleDay":
                break;
            case "ungroupAbsence":
                break;
        }
    }
}