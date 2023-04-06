<?php
require __DIR__ . "/inc/bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Switch che controlla l'uri
/* ES 
    switch ($uri) {
        case "user":
            require PROJECT_ROOT_PATH . "/controller/api/UserController.php";
            $user = nwe UserController();
            ...
    }

*/