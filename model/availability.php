<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Availability extends Database
{
    //getArchiveAvailability, getArchiveAvailabilityHour, addAvailability, 
    //removeAvailability, getArchiveTypeAvailability 
    public function getArchiveAvailability($date, $is_date)
    //IS_DATE = true se è una data, false se è un giorno della settimana
    { //Mostra la lista dei supplenti disponibili per quel determinato giorno
        // Da controllare la presenza del possibile supplente
        //data inizio e data fine possono essere sia date normali (data+ora) oppure giorni (lunedi, martedi) 
        // e si devono contraddistinguere con due diverse query

        $sql1 = "SELECT d.id, u.id as id_utente, CONCAT(u.nome, ' ', u.cognome) as docente, td.nome as tipo_disponibilita, d.data_inizio, d.data_fine 
                from utente u
                inner join disponibilita d
                on u.id = d.docente
                inner join tipo_disponibilita td 
                on td.id = d.tipo_disponibilita
                where date(d.data_inizio) = :date_hour";
        $sql2 = "SELECT d.id, u.id as id_utente, CONCAT(u.nome, ' ', u.cognome) as docente, td.nome as tipo_disponibilita, d.giorno, d.ora
                from utente u
                inner join disponibilita d
                on u.id = d.docente
                inner join tipo_disponibilita td 
                on td.id = d.tipo_disponibilita
                inner join giorno g 
                on g.id = d.giorno 
                inner join ora o 
                on o.id = d.ora 
                where g.nome = :week_day";

        if ($is_date) { //se è una data del formato data-ora
            $stmt = $this->conn->prepare($sql1);
            $stmt->bindValue(":date_hour", $date, PDO::PARAM_STR);
        } else { //se è un giorno della settimana
            $stmt = $this->conn->prepare($sql2);
            $stmt->bindValue(":week_day", $date, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getArchiveAvailabilityHour(){ 

    }

    public function addAvailability($teacher, $availability_type, $type1, $type2, $is_date){

        $sql1 = "SELECT INTO disponibilita (docente, tipo_disponibilita, data_inizio, data_fine)
                VALUES (:teacher, :availability_type, :type1, :type2)";
        $sql2 = "INSERT INTO disponibilita (docente, tipo_disponibilita, giorno, ora)
                VALUES (:teacher, :availability_type, :type1, :type2)";
        
        if($is_date){ //se è una data
            $stmt = $this->conn->prepare($sql1);
            $stmt->bindValue(":type1", $type1, PDO::PARAM_STR);
            $stmt->bindValue(":type2", $type2, PDO::PARAM_STR);
        } else { // se è un giorno e un'ora
            $stmt = $this->conn->prepare($sql2);
            $stmt->bindValue(":type1", $type1, PDO::PARAM_INT);
            $stmt->bindValue(":type2", $type2, PDO::PARAM_INT);
        }
        $stmt->bindValue(":teacher", $teacher, PDO::PARAM_INT);
        $stmt->bindValue(":availability_type", $availability_type, PDO::PARAM_INT);

        if($stmt->execute()){
            return true;
        }
        return false;
    }



}