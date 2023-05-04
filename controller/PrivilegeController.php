<?php
require_once PROJECT_ROOT_PATH . "/controller/BaseController.php";
require_once PROJECT_ROOT_PATH . "/model/privilege.php";

class PrivilegeController extends BaseController
{
    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function processRequest()
    {
        $privilege = new Privilege();

        if ($this->uri == "getArchivePrivilege")
        {
            $privileges = $privilege->getArchivePrivilege();
                
            if (empty($privileges)) {
                http_response_code(404);
                echo json_encode(["success" => false, "data" => "Errore nella restituzione privilegi"]);
            }

            http_response_code(200);
            echo json_encode(["success" => true, "data" => $privileges]);
        }
    }
}