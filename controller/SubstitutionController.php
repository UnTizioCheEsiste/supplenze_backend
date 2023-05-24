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

                // Controllo presenza parametri necessari
                if (empty($data->assenza) || empty($data->supplente) || empty($data->ora) || !is_int($data->da_retribuire) || !is_int($data->non_necessaria)) {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                $id_absence = $data->assenza;
                $id_user = $data->supplente;
                $not_necessary = $data->non_necessaria;
                $to_pay = $data->da_retribuire;
                $hour = $data->ora;

                // Nel caso i parametri opzionali non fossero presenti viene assegnato un valore stringa vuota
                if (empty($data->data_supplenza)) {
                    $substitution_date = "";
                } else {
                    $substitution_date = $data->data_supplenza;
                }
                if (empty($data->nota)) {
                    $note = "";
                } else {
                    $note = $data->nota;
                }


                // Aggiunta delle supplenze
                $newSubstitute = $sub->addSubstitute($id_absence, $id_user, $not_necessary, $to_pay, $hour, $substitution_date, $note);

                // Se la supplenza non viene assegnata
                if (!$newSubstitute) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Operazione non completata"]);
                    break;
                }

                // Se tutto è andato a buon fine
                http_response_code(200);
                echo json_encode(["success" => true, "data" => "Riga aggiunta con successo"]);
                break;
            // case "addSubtituteTeaching":
            //     break;
            case "getArchiveSubstitution":
                // Ottenimento delle supplenze
                $archiveSub = $sub->getArchiveSubstitution();

                // Nel caso non ci fossero supplenze
                if (empty($archiveSub)) {
                    http_response_code(204);
                    echo json_encode(["success" => true, "data" => $archiveSub]);
                    break;
                }

                // Se tutto è andato a buon fine
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveSub]);
                break;
            case "getArchiveUserSubstitution":
                $params = $this->getQueryStringParams();

                // Se l'ID dell'utente non è presente
                if (empty($params["id"])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Non è presente l'id"]);
                    break;
                }
                $archiveUserSub = $sub->getArchiveUserSubstitution($params['id']);
                http_response_code(200);
                echo json_encode(["success" => true, "data" => $archiveUserSub]);
                break;
            case 'removeSubstitution':
                break;

        }
    }
}