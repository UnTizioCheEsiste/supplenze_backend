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
                //prelevo i dati dal model e controllo che non siano vuoti
                $hours = $time->getHour();
                if (empty($hours)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Errore nella restituzione delle ore"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $hours]);
                break;
            case "getDay":
                //prelevo i dati dal model e controllo che non siano vuoti
                $days = $time->getDay();
                if (empty($days)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Errore nella restituzione dei giorni"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $days]);
                break;
            case "getHourById":
                //prelevo l'id dalla richiesta e controllo che sia stato inserito
                $params = $this->getQueryStringParams();
                if(empty($params["id"]))
                {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Parametro non inserito"]);
                }else{
                    //prelevo i dati dal model, se l'array non è vuoto lo restituisco altrimenti ritorno un errore
                    $hourInfo = $time->getHourById($params['id']);

                    if (empty($hourInfo)) {
                        http_response_code(404);
                        echo json_encode(["success" => false, "data" => "Ora non trovata"]);
                        break;
                    }

                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $hourInfo]);
                }
                break;
        }
    }
}