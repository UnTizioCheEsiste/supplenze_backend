<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Time extends Database
{
    /**
     * Ritorna la tabella ore del database con id, inizio e fine.
     * 
     * @return mixed l'elenco delle ore.
     */
    public function getHour()
    {
        $sql = "SELECT id, data_inizio, data_fine
                FROM ora
                WHERE 1=1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Ritorna la tabelle day del database.
     * 
     * @return mixed id e nome di ogni giorno presente.
     */
    public function getDay()
    {
        $sql = "SELECT id, nome
                FROM giorno
                WHERE 1=1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Ritorna un'ora passando un id.
     * 
     * @param int $id l'id dell'ora.
     * 
     * @return mixed l'ora con inizio e fine.
     */
    public function getHourById($id)
    {
        $sql = "SELECT data_inizio, data_fine
                FROM ora
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
