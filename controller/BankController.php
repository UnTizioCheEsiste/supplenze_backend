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
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Errore nel download dei dati."]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $result]);
                break;
            case "getUserHoursBank":
                $params = $this->getQueryStringParams();
                if(!empty($params["id"]))
                {
                    $result = $bank->getUserHoursBank($params['id']);
    
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $result]);
                    break;
                }else{
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Id non inserito."]);
                    break;
                }
                break;
            case "getUserCountHoursBank":
                $params = $this->getQueryStringParams();
                if(!empty($params["id"]))
                {
                    $result = $bank->getUserCountHoursBank($params['id']);
    
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $result]);
                    break;
                }else{
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Id non inserito."]);
                    break;
                }
            case "addUserHoursBank":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                
                if(empty($data->userId) || empty($data->day) || empty($data->type) | empty($data->count) || empty($data->notes))
                {
                    echo json_encode(["success" => false, "data" => "Non sono stati inseriti tutti i parametri."]);
                    break;
                }

                if($bank->addUserHoursBank($data->userId, $data->day, $data->type, $data->count, $data->notes)!=0)
                {
                    echo json_encode(["success" => true, "data" => "Ore aggiunte correttamente."]);
                }else{
                    echo json_encode(["success" => false, "data" => "Errore nell'inserimento."]);
                }
                break;
        }
    }
}