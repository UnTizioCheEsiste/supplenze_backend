<?php
require_once __DIR__ . "/config/bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

switch ($uri[2]) {
    case "utente":
        require PROJECT_ROOT_PATH . "/controller/UserController.php";
        $user = new UserController($uri[3]);
        $user->processRequest();
        break;
    case "assenza":
        break;
    default:
        echo "Silence is golden...";
        break;
}
