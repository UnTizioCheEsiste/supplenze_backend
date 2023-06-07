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
                $result = $bank->getArchiveCountHoursBank();

                if (empty($result)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Errore nel download dei dati"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "getUserHoursBank":
                $params = $this->getQueryStringParams();
                if (!empty($params["id"])) {
                    $result = $bank->getUserHoursBank($params['id']);

                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $result]);
                    break;
                } else {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Id non inserito."]);
                    break;
                }
            case "getUserCountHoursBank":
                $params = $this->getQueryStringParams();
                if (!empty($params["id"])) {
                    $result = $bank->getUserCountHoursBank($params['id']);

                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $result]);
                    break;
                } else {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Id non inserito."]);
                    break;
                }
            case "addUserHoursBank":
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                if (empty($data->utente) || empty($data->tipo) | empty($data->numero_ore)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Non sono stati inseriti tutti i parametri"]);
                    break;
                }

                if (empty($data->nota)) {
                    $notes = " ";
                } else {
                    $notes = $data->nota;
                }

                if ($bank->addUserHoursBank($data->utente, $data->tipo, $data->numero_ore, $notes) != 0) {
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => "Ore aggiunte correttamente"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'inserimento"]);
                }
                break;
            case "removeHours":
                $params = $this->getQueryStringParams();

                if (!$params["id"]) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Non sono stati inseriti tutti i parametri"]);
                    break;
                }

                $result = $bank->removeHours($params["id"]) > 0 ? true : false;
                $result ? http_response_code(200) : http_response_code(500);
                echo json_encode(["success" => $result, "data" => $result]);
                break;
            default:
                http_response_code(400);
                echo json_encode("Route not found");
                break;
        }
    }
}
