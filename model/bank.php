<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Bank extends Database
{
    /**
    * Restituisce lo storico delle ore da recuperare o straordinarie.
    *
    * @return mixed Array di ore.
    */
    public function getArchiveCountHoursBank()
    {
        $sql = "SELECT u.id, CONCAT(u.nome,' ',u.cognome) as utente, SUM(CASE WHEN bo.tipo_ora = 'da recuperare' THEN bo.numero_ore ELSE 0 END) as ore_da_recuperare, SUM(CASE WHEN bo.tipo_ora = 'straordinario' THEN bo.numero_ore ELSE 0 END) as ore_straordinarie
                FROM banca_ore bo
                INNER JOIN utente u ON u.id = bo.docente
                where u.attivo=1
                GROUP BY bo.docente;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
    * Restituisce tutte le ore da recuperare o straordinarie di un utente.
    *
    * @param int $id ID dell'utente.
    *
    * @return mixed Array di ore.
    */
    public function getUserHoursBank($id)
    {
        $sql = "SELECT bo.docente, bo.numero_ore, bo.nota, bo.tipo_ora 
        FROM banca_ore bo
        WHERE bo.docente = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
    * Restituisce il totale di ore da recuperare o straordinarie.
    *
    * @param int $id ID dell'utente.
    *
    * @return mixed Array di ore.
    */
    public function getUserCountHoursBank($id)
    {
        $sql = "SELECT u.id, CONCAT(u.nome,' ',u.cognome) as utente, SUM(CASE WHEN bo.tipo_ora = 'da recuperare' THEN bo.numero_ore ELSE 0 END) as ore_da_recuperare, SUM(CASE WHEN bo.tipo_ora = 'straordinario' THEN bo.numero_ore ELSE 0 END) as ore_straordinarie
        FROM banca_ore bo
        INNER JOIN utente u ON u.id = bo.docente
        where u.id = :id and u.attivo=1
        GROUP BY bo.docente;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
    * Aggiunge un'ora alla banca d'ore di un utente.
    *
    * @param int $userId ID dell'utente.
    * @param string $day Giorno.
    * @param string $type Da recuperare o straordinario.
    * @param int $count Il totale di ore da aggiungere.
    * @param string $notes Note.
    *
    * @return boolean True se l'aggiunta è andata a buon fine.
    */
    public function addUserHoursBank($userId, $type, $count, $notes)
    {
        $sql = "INSERT into banca_ore  (docente, tipo_ora, numero_ore, nota)
                values (:userId, :type, :count, :notes);";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindValue(":type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":count", $count, PDO::PARAM_INT);
        $stmt->bindValue(":notes", $notes, PDO::PARAM_STR);

        return $stmt->execute();
    }
}
