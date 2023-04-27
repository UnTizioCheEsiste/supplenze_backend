<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Time extends Database
{
    public function getHour() 
    {
        $sql = "SELECT id, start_time, finish_time
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
}