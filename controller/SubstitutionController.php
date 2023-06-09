<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/substitution.php";
require_once PROJECT_ROOT_PATH . "/model/user.php";
//INVIO MAIL CON LIBRERIA PHPMAILER, SE NON C'E' DA INSTALLARE CON COMPOSER E VENDORS
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// importa la libreria PHPMailer
require 'vendor/autoload.php';
require_once PROJECT_ROOT_PATH . "/controller/UserController.php";

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
                if (empty($data->assenza) || empty($data->supplente) || empty($data->ora) || !is_int($data->da_retribuire) || !is_int($data->non_necessaria) || empty($data->data_supplenza)) {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti tutti gli attributi"]);
                    break;
                }

                $id_absence = $data->assenza;
                $id_user = $data->supplente;
                $not_necessary = $data->non_necessaria;
                $to_pay = $data->da_retribuire;
                $hour = $data->ora;
                $substitution_date = $data->data_supplenza;

                // Nel caso i parametri opzionali non fossero presenti viene assegnato un valore stringa vuota
                if (empty($data->nota)) {
                    $note = " ";
                } else {
                    $note = $data->nota;
                }

                // Aggiunta delle supplenze
                $newSubstitute = $sub->addSubstitute($id_absence, $id_user, $not_necessary, $to_pay, $hour, $substitution_date, $note);
                $userController = new UserController(1);
                $result = $userController->sendMail($newSubstitute["email"], "Aggiunta supplenza del " . $newSubstitute["data_supplenza"], "Le comunichiamo che le è stata assegnata una supplenza il giorno " . $newSubstitute["data_supplenza"] . " dalle " . $newSubstitute["data_inizio"] . " alle " . $newSubstitute["data_fine"] . ".");
                // Se tutto è andato a buon fine
                if (!$result['status']) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'invio della email"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => "Supplenza assegnata correttamente"]);
                break;
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
            case "removeSubstitution":
                $params = $this->getQueryStringParams();

                if (empty($params["id"])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Parametro non inserito"]);
                    break;
                }
                $email = $sub->removeSubstitution($params["id"]);

                if (empty($email["email"])) {
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => "Supplenza correttamente rimossa"]);
                    break;
                }

                // Invio della mail al docente per comunicare la deselezione della supplenza
                $userController = new UserController(1);
                $result = $userController->sendMail($email["email"], "Rimozione supplenza del " . $email["data_supplenza"], "Le comunichiamo che la supplenza del " . $email["data_supplenza"] . " dalle " . $email["data_inizio"] . " alle " . $email["data_fine"] . " è stata rimossa.");

                if (!$result['status']) {
                    http_response_code(500);
                    echo json_encode(["success" => false, "data" => "Errore nell'invio della email"]);
                    break;
                }
                http_response_code(200);
                echo json_encode(["success" => true, "data" => "Email inviata"]);
                break;
            default:
                http_response_code(400);
                echo json_encode("Route not found");
                break;
        }
    }
}
