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
            
        }
    }
}