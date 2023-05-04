<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Bank extends Database
{
    public function getArchiveCountHoursBank()
    {
        //User ID, nome+cognome utente, ore straordinari, ore recuperare
        //devo fare due query? perchè una restituisce le ore da recuperare e un'altra le ore straordinario
        $sql = "SELECT u.id, concat (u.nome,' ',u.cognome) as utente,bo.numero_ore,bo.nota
        FROM banca_ore bo
        inner join utente u on u.id=bo.docente
        WHERE 1=1 and bo.tipo_ora='da recuperare'";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserHoursBank($id)
    {

    }

    public function getUserCountHoursBank()
    {

    }

    public function addUserHoursBank()
    {

    }
}