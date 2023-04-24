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
                break;
            case "addSbtituteTeaching":
                break;
            case "getArchiveSubstitution":
                break;
            case "getArchiveUserSubstitution":
                break;
        }
    }
}