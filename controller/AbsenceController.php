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
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                $absenceId = $data->absenceId;
                $certificate = $data->certificate_code;

                if (empty($data->absenceId) || empty($data->certificate_code)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                $result = $absence->addCertificate($absenceId, $certificate);

                if (!$result) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Certificato non aggiunto"]);
                    break;
                }
                
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "addAbsenceHour":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                $userId = $data->userId;
                $date = $data->date;
                $hours = $data->hours;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }

                // Controllo sulla presenza dei campi obbligatori
                if (empty($data->userId) || empty($data->date) || empty($data->hours) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                // Nel caso esistessero i due campi opzionali
                if (!empty($data->certificate_code) && !empty($data->notes)) {
                    $notes = $data->notes;
                    $certificate = $data->certificate_code;
                }

                // Aggiunta della/e assenza/e
                $result = $absence->addAbsenceHour($userId, $date, $hours, $certificate, $notes, $reason);

                if (!$result) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Assenza non aggiunta"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "addAbsenceSingleDay":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                $userId = $data->userId;
                $date = $data->date;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }

                // Controllo sulla presenza dei campi obbligatori
                if (empty($data->userId) || empty($data->date) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                // Nel caso esistessero i due campi opzionali
                if (!empty($data->certificate_code) && !empty($data->notes)) {
                    $notes = $data->notes;
                    $certificate = $data->certificate_code;
                }

                // Aggiunta della/e assenza/e
                $result = $absence->addAbsenceSingleDay($userId, $date, $certificate, $notes, $reason);

                if (!$result) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Assenza non aggiunta"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "addAbsenceMultipleDay":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                $userId = $data->userId;
                $dates = $data->dates;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }

                // Controllo sulla presenza dei campi obbligatori
                if (empty($data->userId) || empty($data->dates) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                // Nel caso esistessero i due campi opzionali
                if (!empty($data->certificate_code) && !empty($data->notes)) {
                    $notes = $data->notes;
                    $certificate = $data->certificate_code;
                }

                // Aggiunta della/e assenza/e
                $result = $absence->addAbsenceMultipleDay($userId, $dates, $certificate, $notes, $reason);

                if (!$result) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Assenza non aggiunta"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
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