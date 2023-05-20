<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/user.php";
//INVIO MAIL DA GESTIRE
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// importa la libreria PHPMailer
require 'vendor/autoload.php';


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

                //segnalo con errore se i dati obbligatori non sono inseriti
                if(empty($data->email) || empty($data->password)){
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti gli attributi richiesti"]);
                    break;
                }

                //assegno le variabili e eseguo la query nel model
                $email = $data->email;
                $password = $data->password;

                $userId = $user->login($email, $password);

                //se non ricevo risultati seganlo l'errore
                if ($userId < 0) {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Credenziali errate"]);
                    break;
                }

                //se non ci sono errori prelevo i dati dell'utente e controllo che anche questi non siano vuoti
                $userInfo = $user->getUser($userId["id"]);

                if (empty($userInfo)) {
                    http_response_code(404);
                    echo json_encode(["success" => false, "data" => "Utente non trovato"]);
                    break;
                }

                //array associativo con i dati dell'utente che ha fatto il login
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

                //controllo sugli input
                if(empty($data->nome) || empty($data->cognome)  || empty($data->email)  || empty($data->telefono)  || empty($data->privilegio)){
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti gli attributi richiesti"]);
                    break;
                }
                
                //controllo sulla registrazione
                if($user->register($data->nome, $data->cognome, $data->email, $data->telefono, $data->privilegio)!=0)
                {
                    $result=$this->sendMail($data->email,"Registrazione alla piattaforma supplenze","
                    Carissimo utente le confermiamo che la registrazione alla piattaforma per gestire assenze e supplenze è avvenuta correttamente");
                    //controllo sull'invio della mail
                    if($result['status'])
                    {
                        http_response_code(200);
                        echo json_encode(["success" => true, "data" => "Email inviata correttamente."]);
                    }else{
                        http_response_code(400);
                        echo json_encode(["success" => false, "data" => "Errore nell'invio della mail"]);
                    }
                }else{
                    //messaggio errore
                    http_response_code(400);
                    echo json_encode(["success" => false, "data" => "Errore nell'esecuzione della registrazione"]);
                }
                break;

            case "changePassword":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                //controllo i parametri di input
                if(empty($data->idUtente) || empty($data->passwordVecchia) || empty($data->passwordNuova))
                {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti gli attributi richiesti"]);
                    break;
                }
                //controllo se la query è riuscita
                if($user->changePassword($data->idUtente, $data->passwordVecchia, $data->passwordNuova) == 1)
                {
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => "Password cambiata con successo."]);
                }else{
                    //messaggio errore
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Errore nella modifica della password."]);
                }
                break;
                
            case "resetPassword":
                $json = file_get_contents('php://input');
                $data = json_decode($json);
                
                //controllo gli input del frontend
                if(empty($data->idUtente) || empty($data->email))
                {
                    http_response_code(401);
                    echo json_encode(["success" => false, "data" => "Non sono presenti gli attributi richiesti"]);
                    break;
                }

                //controllo la query se avviene correttamente
                if($user->resetPassword($data->idUtente, $data->email) == 1)
                {
                    //setto i parametri per la mail
                    echo json_encode(["success" => true, "data" => "Password temporanea creata con successo."]);
                    $subject="Password resettata con successo";
                    $body="Gentile utente la informiamo che la sua password è stata resettata con successo";
                    $result=$this->sendMail($data->email, $subject,$body);
                    
                    //controllo sull'invio della mail
                    if($result['status'])
                    {
                        http_response_code(200);
                        echo json_encode(["success" => true, "data" => "Email inviata correttamente."]);
                    }else{
                        http_response_code(400);
                        echo json_encode(["success" => false, "data" => "Errore nell'invio della mail"]);
                    }
                }else{
                    //messaggio errore
                    http_response_code(400);
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
            case 'provaEmail':
                $result=$this->sendMail("pirra.francesco@iisviolamarchesini.edu.it","Prova","Prova email");
                if($result['status'])
                    echo 'Email inviata con successo!';
                else
                    echo 'Errore durante l\'invio della email: ' . $result["error"];
                break;
        }
    }

    public function sendMail($to,$subject,$body)
    {
        $from = 'frapirra123@gmail.com';
        /*
        $to = 'medea.luca@iisviolamarchesini.edu.it';
        $subject = 'email inviata';
        $body = 'Guarda che bravo che sono';*/

        // configura le impostazioni del server SMTP di Google
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = 'frapirra123@gmail.com';
        $mail->Password = 'zikiridqjnvxodhj';

        // imposta le informazioni della email
        $mail->setFrom($from);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $response=[];
        // invia la email
        if(!$mail->send()) {
            $response['status'] = false;
            $response['error'] = $mail->ErrorInfo;
        } else {
            $response['status'] = true;
        }
        return $response;
    }
}

