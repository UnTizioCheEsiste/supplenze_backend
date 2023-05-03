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
                //$id_absence, $id_user, $not_necessary, $to_pay
                $id_absence = $data->assenza;
                $id_user = $data->utente;
                $not_necessary = $data->non_necessaria;
                $to_pay = $data->da_retribuire;
                $newSubstitute = $sub->addSubstitute($id_absence, $id_user, $not_necessary, $to_pay);

                if(!$newSubstitute){ //se ritorna TRUE
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Operazione non completata"]);
                    break; 
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $newSubstitute]); 
                break;
            // case "addSubtituteTeaching":
            //     break;
            case "getArchiveSubstitution":
                $ArchiveSub = $sub->getArchiveSubstitution();

                if(empty($archiveSub)){
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'operazione"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveSub]); 
                break;
            case "getArchiveUserSubstitution":
                $params = $this->getQueryStringParams();
                $archiveUserSub = $sub->getArchiveUserSubstitution($params['id']);

                if(empty($archiveUserSub)){
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'operazione"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveUserSub]); 
                break;
        }
    }
}