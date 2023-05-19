<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/user.php";

class UserController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $user = new User();

        switch ($this->uri) {
            case "getUser":
                $params = $this->getQueryStringParams();
                if(empty($params["id"]))
                {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }else{
                    $userInfo = $user->getUser($params['id']);

                if (empty($userInfo)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $userInfo]);
                break;
                }
            case "login":
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                if(empty($data->email) || empty($data->password)){
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti gli attributi richiesti"]);
                    break;
                }

                $email = $data->email;
                $password = $data->password;

                $userId = $user->login($email, $password);

                if ($userId < 0) {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Credenziali errate"]);
                    break;
                }

                $userInfo = $user->getUser($userId["id"]);

                if (empty($userInfo)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }

                $userData =
                    [
                        "id" => $userId["id"],
                        "nome" => $userInfo["nome"],
                        "cognome" => $userInfo["cognome"],
                        "email" => $userInfo["email"],
                        "privilegio" => $userInfo["privilegio"],
                        "telefono" => $userInfo["telefono"]
                    ];

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $userData]);
                break;
                
            case "register":
                // BISOGNA GESTIRE L'INVIO DELLA EMAIL
                $json = file_get_contents('php://input');
                $data = json_decode($json);

                if($user->register($data->nome, $data->cognome, $data->email, $data->telefono, $data->privilegio)!=0)
                {
                    //INVIO MAIL DA GESTIRE
                    echo json_encode(["success" => true, "data" => "Email inviata correttamente."]);
                }else{
                    //messaggio errore
                    echo json_encode(["success" => false, "data" => "Errore nell'esecuzione della registrazione"]);
                }
                break;

            case "changePassword":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                echo json_encode($data);
                if($user->changePassword($data->idUtente, $data->passwordVecchia, $data->passwordNuova) == 1)
                {
                    echo json_encode(["success" => true, "data" => "Password cambiata con successo."]);
                }else{
                    //messaggio errore
                    echo json_encode(["success" => false, "data" => "Errore nella modifica della password."]);
                }
                break;
                
            case "resetPassword":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                if($user->resetPassword($data->idUtente, $data->email) == 1)
                {
                    echo json_encode(["success" => true, "data" => "Password temporanea creata con successo."]);
                    //INVIO MAIL DA GESTIRE
                }else{
                //messaggio errore
                    echo json_encode(["success" => false, "data" => "Errore nella modifica della password."]);
                }
                break;
            case "getArchiveUser":
                $userInfo = $user->getArchiveUser();
                
                if (empty($userInfo)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $userInfo]);
                break;
            
            case "GetArchiveUserAbsence":
                $params = $this->getQueryStringParams();
                if(!empty($params["id"]))
                {
                    $userInfo = $user->GetArchiveUserAbsence($params['id']);

                    if (empty($userInfo)) {
                        http_response_code(401);
                        echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                        break;
                    }
    
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $userInfo]);
                    break;
                }else{
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Id non inserito"]);
                    break;
                }
                break;
        }
    }
}
