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
                $userInfo = $user->getUser($params['id']);

                if (empty($userInfo)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }

                http_response_code(200);
                echo json_encode(["success" => true, "data" => $userInfo]);
                break;

            case "login":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                $email = $data->email;
                $password = $data->password;
                $userId = $user->login($email, $password);

                if ($userId < 0) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Credenziali errate"]);
                    break;
                }

                $userInfo = $user->getUser($userId);

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
        }
    }
}
