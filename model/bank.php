<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Bank extends Database
{
    public function getArchiveCountHoursBank()
    {
        $sql = "SELECT u.id, CONCAT(u.nome,' ',u.cognome) as utente, SUM(CASE WHEN bo.tipo_ora = 'da recuperare' THEN bo.numero_ore ELSE 0 END) as ore_da_recuperare, SUM(CASE WHEN bo.tipo_ora = 'straordinario' THEN bo.numero_ore ELSE 0 END) as ore_straordinarie
                FROM banca_ore bo
                INNER JOIN utente u ON u.id = bo.docente
                where 1=1
                GROUP BY bo.docente;";

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