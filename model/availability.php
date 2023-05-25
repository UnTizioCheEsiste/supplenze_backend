<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";
require_once PROJECT_ROOT_PATH . "/model/time.php";

class Availability extends Database
{
    /**
     *  Restituisce tutte le disponibilità temporanee e non,
     *  nel caso sia permanente data inizio e data fine sono null, nel caso sia temporanea niente è null 
     *  @return bool true se va a buon fine
     * @return int 0 se non va a buon fine
     * */
    public function getArchiveAvailability()
    {
        //left join con giorno e ora per prelevare anche quelle temporanee
        $sql = "SELECT u.id as id_docente, concat(u.nome, ' ', u.cognome) as docente, d.id as id_disponibilita, 
        d.tipo_disponibilita as id_tipo_disponibilita, td.nome as tipo_disponibilita, d.giorno, d.ora, d.data_inizio, d.data_fine 
                FROM disponibilita d 
                inner join tipo_disponibilita td 
                on td.id = d.tipo_disponibilita 
                left join giorno g 
                on g.id = d.giorno 
                left join ora o 
                on o.id = d.ora
                left join utente u 
                on u.id = d.docente 
                WHERE u.attivo=1";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            return 0;
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    //Mostra la lista dei supplenti disponibili per quel determinato giorno e quella determinata ora
    public function getArchiveAvailabilityHour($date, $hours)
    {
        $time = new Time();
        // Ora inizio disponibilità
        $hour = $time->getHourById($hours[0]);

        // Concatenzione giorno e ora
        $start_date = $date . " " . $hour["data_inizio"];
        $finish_date = $date . " " . $hour["data_fine"];

        /* Trovo i docenti con disponibilita TEMPORANEA che sono liberi quel determinato giorno
         * a quella determinata ora*/
        $sql1 = "SELECT d.id as id_docente, concat(u.nome, ' ', u.cognome) as docente, td.nome as tipo_disponibilita 
        from disponibilita d 
        inner join utente u 
        on u.id = d.docente
        inner join tipo_disponibilita td 
        on td.id = d.tipo_disponibilita 
        where :startdate between d.data_inizio and d.data_fine
        and :finishdate between d.data_inizio and d.data_fine and u.attivo=1";

        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->bindValue(":startdate", $start_date, PDO::PARAM_STR);
        $stmt1->bindValue(":finishdate", $finish_date, PDO::PARAM_STR);
        try{
            $stmt1->execute();
        } catch(Exception $e){
            return 0;
        }
        
        $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        /* Trovo i docenti con disponibilita PERMANENTE che sono liberi quel giorno della settimana
         * a quell'ora di lezione */
        $dayofweek = date('w', strtotime($date));
        $sql2 = "SELECT d.id as id_docente, concat(u.nome, ' ', u.cognome) as docente, td.nome as tipo_disponibilita
        from disponibilita d 
        inner join utente u 
        on u.id = d.docente
        inner join tipo_disponibilita td 
        on td.id = d.tipo_disponibilita 
        inner join giorno g 
        on g.id = d.giorno
        where d.ora = :hourr
        and g.id = :dayofweek";

        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bindValue(":hourr", $hours, PDO::PARAM_INT);
        $stmt2->bindValue(":dayofweek", $dayofweek, PDO::PARAM_INT);
        try{
            $stmt2->execute();
        } catch(Exception $e){
            return 0;
        }
        $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    // Aggiunge una nuova disponibilita nella tabella disponibilita
    public function addAvailability($teacher, $availability_type, $type1, $type2, $is_date)
    {
        //type1 può essere data_inizio o giorno
        //type2 può essere data_fine e ora
        $sql1 = "SELECT INTO disponibilita (docente, tipo_disponibilita, data_inizio, data_fine)
                VALUES (:teacher, :availability_type, :type1, :type2)";
        $sql2 = "INSERT INTO disponibilita (docente, tipo_disponibilita, giorno, ora)
                VALUES (:teacher, :availability_type, :type1, :type2)";

        if ($is_date) { //se è una data
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

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    // Rimuove la disponibilita dato il suo ID
    public function removeAvailability($availability_id)
    {
        $sql = "DELETE FROM disponibilita
                WHERE id = :availability_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":availability_id", $availability_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Mostra la lista dei tipi di disponibilita dalla tabella tipo_disponibilita
    public function getArchiveTypeAvailability()
    {

        $sql = "SELECT id, nome, descrizione
                FROM tipo_disponibilita
                WHERE 1=1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}