<?php
require_once __DIR__ . "/config/bootstrap.php";
require_once __DIR__ . "/config/errorHandler.php";

// Header
header("Content-type: application/json; charset=UTF-8");

// Error handler
set_exception_handler("errorHandler::handleException");
set_error_handler("errorHandler::handleError");

// Exploding url
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

/**
 *                        0          1         2         3
 *  esempio uri http://localhost/supplenze/utente/getUser.php
 * 
 * 
 */

//switch sull'url per smistare a uno dei vari controller
switch ($uri[2]) {
    case "utente":
        require PROJECT_ROOT_PATH . "/controller/UserController.php";
        $user = new UserController($uri[3]);
        $user->processRequest();//questo metodo processa la richiesta guardando alla parte dell'url dopo UserController
        break;
    case "assenza":
        require PROJECT_ROOT_PATH . "/controller/AbsenceController.php";
        $absence = new AbsenceController($uri[3]);
        $absence->processRequest();
        break;
    case "disponibilita":
        require PROJECT_ROOT_PATH . "/controller/AvailabilityController.php";
        $availability = new AvailabilityController($uri[3]);
        $availability->processRequest();
        break;
    case "privilegi":
        require PROJECT_ROOT_PATH . "/controller/PrivilegeController.php";
        $privilege = new PrivilegeController($uri[3]);
        $privilege->processRequest();
        break;
    case "bancaore":
        require PROJECT_ROOT_PATH . "/controller/BankController.php";
        $bank = new BankController($uri[3]);
        $bank->processRequest();
        break;
    case "supplenza":
        require PROJECT_ROOT_PATH . "/controller/SubstitutionController.php";
        $substitution = new SubstitutionController($uri[3]);
        $substitution->processRequest();
        break;
    case "tempo":
        require PROJECT_ROOT_PATH . "/controller/TimeController.php";
        $time = new TimeController($uri[3]);
        $time->processRequest();
        break;
    default:
        http_response_code(400);
        echo json_encode("Route not found");
        break;
}
