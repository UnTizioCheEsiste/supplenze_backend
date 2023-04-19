<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class User extends Database
{
    /**
     * Ottieni gli elementi del URI.
     * 
     * @return User
     */
    public function getUser($id)
    {
        $sql = "SELECT nome, cognome, email, privilegio, telefono
                FROM utente
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
