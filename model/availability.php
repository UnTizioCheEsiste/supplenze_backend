<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Availability extends Database
{
    //getArchiveAvailability, getArchiveAvailabilityHour, addAvailability, 
    //removeAvailability, getArchiveTypeAvailability 
    public function getArchiveAvailability($date)
    { //Mostra la lista dei supplenti disponibili per quel determinato giorno
        // Da controllare la presenza del possibile supplente
        //data inizio e data fine possono essere sia date normali (data+ora) oppure giorni (lunedi, martedi) 
        // e si devono contraddistinguere con due diverse query

        $sql1 = "SELECT d.id, u.id as id_utente, CONCAT(u.nome, ' ', u.cognome) as supplente, td.nome as tipo_disponibilita, d.data_inizio, d.data_fine 
                from utente u
                inner join disponibilita d
                on u.id = d.docente
                inner join tipo_disponibilita td 
                on td.id = d.tipo_disponibilita
                where date(d.data_inizio) = :date_hour";
        $sql2 = "SELECT d.id, u.id as id_utente, CONCAT(u.nome, ' ', u.cognome) as supplente, td.nome as tipo_disponibilita, d.giorno, d.ora
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

        if (DateTime::createFromFormat('Y-m-d H:i:s', $date) !== false) {
            echo $date;
            $stmt = $this->conn->prepare($sql1);
            $stmt->bindValue(":date_hour", $date, PDO::PARAM_STR);
        } else {
            $stmt = $this->conn->prepare($sql2);
            $stmt->bindValue(":week_day", $date, PDO::PARAM_STR);

        }
    }



}