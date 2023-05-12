<?php
//importo il base controller e altri eventuali model necessari
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
                //richiamo il metodo nel model
                $absences=$absence->getArchiveAbsence();

                //controllo sul valore restituito
                if (empty($absences)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Assenze non presenti"]);
                    break;
                }

                //restituisco i dati 
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $absences]);
                break;
            case "getAbsence":
                $params = $this->getQueryStringParams();

                if(empty($params["id"]))
                {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Prametro non inserito"]);
                    break;
                }
                else
                {
                    $absenceInfo = $absence->getAbsence($params['id']);

                    if (empty($absenceInfo)) {
                        http_response_code(404);
                        echo json_encode(["success" => false, "data" => "Assenza non trovata"]);
                        break;
                    }

                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $absenceInfo]);
                    break;
                }
            case "addCertificate":
                //ottengo gli input e assegno a delle variabili i vari parametri del body
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                //se i parametri obbligatori non sono forniti
                if (empty($data->absenceId) || empty($data->certificate_code)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                //assegno i paremtri obbligatori
                $absenceId = $data->absenceId;
                $certificate = $data->certificate_code;

                if(empty($absence->getAbsence($absenceId)))
                {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Assenza inesistente"]);
                    break;
                }
                //richaimo la query nel model che restituisce true o false
                $result = $absence->addCertificate($absenceId, $certificate);

                //se non va a buon fine segnalo l'errore
                if (!$result) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Certificato non aggiunto"]);
                    break;
                }
                
                //altrimenti seganlo il successo dell'operazione
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "addAbsenceHour":
                //prelevo i dati dal body e assegno le variabili dei dati obbligatori
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                //se i parametri obbligatori non sono forniti
                if (empty($data->userId) || empty($data->date) || empty($data->hours) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                $userId = $data->userId;
                $date = $data->date;
                $hours = $data->hours;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }else{
                    $certificate = $data->certificate_code;
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }else{
                    $notes = $data->notes;
                    
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

                 // Controllo sulla presenza dei campi obbligatori
                 if (empty($data->userId) || empty($data->date) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                $userId = $data->userId;
                $date = $data->date;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }else{
                    $certificate = $data->certificate_code;
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }else{
                    $notes = $data->notes;
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

                // Controllo sulla presenza dei campi obbligatori
                if (empty($data->userId) || empty($data->dates) || empty($data->reason)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                if($data->dates[0]>($data->dates[count($data->dates)-1]))
                {
                    http_response_code();
                    echo json_encode(["success" => false, "data" => "Date errate"]);
                    break;
                }

                $userId = $data->userId;
                $dates = $data->dates;
                $reason = $data->reason;
                
                // Controllo sulla presenza del campo opzionale certificato medico
                if (empty($data->certificate_code)) 
                {
                    $certificate = "";
                }else{
                    $certificate = $data->certificate_code;
                }
                
                // Controllo sulla presenza del campo opzionale note
                if (empty($data->notes)) {
                    $notes = "";
                }else{
                    $notes = $data->notes;
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
                    echo json_encode(["success" => false, "data" => "Parametro non inserito"]);
                    break;
                }
                else
                {
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