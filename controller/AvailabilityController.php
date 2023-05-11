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
                $archiveAva = $ava->getArchiveAvailability();
                if(empty($archiveAva)){
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non è presente alcun dato"]);
                }
                http_response_code(200);
                    echo json_encode(["success" => true, "data" => $archiveAva]);
                break;
            case "getArchiveAvailabilityHour":
                // $params = $this->getQueryStringParams();
                // if (empty($params["data"])) {
                //     http_response_code(500);
                //     echo json_encode(["success" => false, "data" => "Non è specificata la data"]);
                //     break;
                // } else if (strtotime($params["data"])) { // se è una data
                //     $archiveAva = $ava->getArchiveAvailabilityHour($params["data"], true);
                // } else if (is_string($params["data"])) { // se è un giorno della settimana
                //     $archiveAva = $ava->getArchiveAvailabilityHour($params["data"], false);
                // }

                // if (empty($archiveAva)) {
                //     http_response_code(500);
                //     echo json_encode(["success" => false, "data" => "Non è presente alcuna disponibilità"]);
                //     break;
                // }
                // http_response_code(200);
                // echo json_encode(["success" => true, "data" => $archiveAva]);
                // break;
            case "addAvailability":
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                // Controllo variabili
                if (empty($data->docente) || empty($data->disponibilita)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "I dati hanno un formato errato"]);
                    break;
                } else if (!is_int($data->docente) || !is_int($data->disponibilita)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "I dati hanno un formato errato"]);
                    break;
                }
                $teacher = $data->docente;
                $availability = $data->disponibilita;

                // Controllo se type1 è data_inizio o giorno
                if (empty($data->data_inizio) || empty($data->data_fine)) {
                    if (empty($data->giorno) || empty($data->ora)) {
                        http_response_code(500);
                        echo json_encode(["success" => false, "data" => "Attributi mancanti"]);
                        break;
                    }

                    // Allora sono giorno e ora. Controllo i valori
                    if (!is_int($data->giorno) || !is_int($data->ora)) { // perche sono ID
                        http_response_code(500);
                        echo json_encode(["success" => false, "data" => "I dati hanno un formato errato"]);
                        break;
                    }
                    $type1 = $data->giorno;
                    $type2 = $data->ora;
                    $is_date = false;
                } else {
                    if (!strtotime($data->data_inizio) || !strtotime($data->data_fine)) {
                        http_response_code(500);
                        echo json_encode(["success" => false, "data" => "I dati hanno un formato errato"]);
                        break;
                    }
                    $type1 = $data->data_inizio;
                    $type2 = $data->data_fine;
                    $is_date = true;
                }

                $addAva = $ava->addAvailability($teacher, $availability, $type1, $type2, $is_date);
                if (!$addAva) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'esecuzione dell'API"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $addAva]);
                break;
            case "removeAvailability":
                $params = $this->getQueryStringParams();
                if (empty($params["id"])) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non è presente l'ID"]);
                    break;
                }
                $removeAva = $ava->removeAvailability($params["id"]);
                if (!$removeAva) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'esecuzione dell'API"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $removeAva]);
                break;
            case "getArchiveTypeAvailability":
                $typeAva = $ava->getArchiveTypeAvailability();

                if (empty($typeAva)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti valori nella tabella"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $typeAva]);
                break;
        }
    }
}