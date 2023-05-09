<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Time extends Database
{
    public function getHour() 
    {
        $sql = "SELECT id, data_inizio, data_fine
                FROM ora
                WHERE 1=1";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDay() 
    {
        $sql = "SELECT id, nome
                FROM giorno
                WHERE 1=1";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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