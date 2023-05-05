<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Bank extends Database
{
    public function getArchiveCountHoursBank()
    {
        //query unica per ore da recuperare e ore straordinari
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
        $sql = "select concat(u.nome, ' ', u.cognome) as docente, bo.giorno, bo.tipo_ora, bo.numero_ore, bo.nota
                from banca_ore bo
                inner join utente u on bo.docente = u.id
                where u.id = :id;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCountHoursBank($id)
    {
        $sql = "SELECT u.id, CONCAT(u.nome,' ',u.cognome) as utente, SUM(CASE WHEN bo.tipo_ora = 'da recuperare' THEN bo.numero_ore ELSE 0 END) as ore_da_recuperare, SUM(CASE WHEN bo.tipo_ora = 'straordinario' THEN bo.numero_ore ELSE 0 END) as ore_straordinarie
        FROM banca_ore bo
        INNER JOIN utente u ON u.id = bo.docente
        where u.id = :id
        GROUP BY bo.docente;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addUserHoursBank($userId, $day, $type, $count, $notes)
    {
        $sql = "insert into banca_ore  (docente, giorno, tipo_ora, numero_ore, nota)
                values (:userId, :day, :type, :count, :notes);";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindValue(":day", $day, PDO::PARAM_STR);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":count", $count, PDO::PARAM_INT);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);

        return $stmt->execute();
    }
}