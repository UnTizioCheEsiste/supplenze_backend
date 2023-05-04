<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Privilege extends Database
{
    public function getArchivePrivilege()
    {
        $sql = "SELECT p.id,p.nome
        FROM privilegio p
        WHERE 1=1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}