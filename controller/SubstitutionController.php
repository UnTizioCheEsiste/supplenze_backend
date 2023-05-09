<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/substitution.php";

class SubstitutionController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $sub = new Substitution();

        switch ($this->uri) {
            case "addSubstitute":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                //VARIABILI NECESSARIE: $id_absence, $id_user, $not_necessary, $to_pay
                if (empty($data->assenza) || empty($data->supplente) || empty($data->ora) || !is_int($data->da_retribuire) || !is_int($data->non_necessaria)) {
                    //errore perche sono variabili necessarie
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }
                $id_absence = $data->assenza;
                $id_user = $data->supplente;
                $not_necessary = $data->non_necessaria;
                $to_pay = $data->da_retribuire;
                $hour = $data->ora;

                if (empty($data->data_supplenza)) {
                    $substitution_date = "";
                }

                if (empty($data->nota)) {
                    $note = "";
                }
                
                if (!empty($data->data_supplenza) && !empty($data->nota)) {
                    $substitution_date = $data->data_supplenza;
                    $note = $data->nota;
                }
                
                $newSubstitute = $sub->addSubstitute($id_absence, $id_user, $not_necessary, $to_pay, $hour, $substitution_date, $note);

                if (!$newSubstitute) { //se ritorna FALSE
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Operazione non completata"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => "Riga aggiunta con successo"]);
                break;
            // case "addSubtituteTeaching":
            //     break;
            case "getArchiveSubstitution":
                $archiveSub = $sub->getArchiveSubstitution();
                if (empty($archiveSub)) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'operazione"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveSub]);
                break;
            case "getArchiveUserSubstitution":
                $params = $this->getQueryStringParams();
                if (empty($params["id"])) { //se non è stato indicato l'ID
                    http_response_code(200);
                    echo json_encode(["success" => false, "data" => "Non è presente l'id"]);
                    break;
                } else if(is_int($params["id"])){
                    $archiveUserSub = $sub->getArchiveUserSubstitution($params['id']);
                    if (empty($archiveUserSub)) {
                        http_response_code(200);
                        echo json_encode(["success" => false, "data" => "L'utente non ha supplenze"]);
                        break;
                    }
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $archiveUserSub]);
                    break;
                } else {
                    http_response_code(200);
                        echo json_encode(["success" => false, "data" => "L'id inserito non è un intero"]);
                        break;
                }

        }
    }
}