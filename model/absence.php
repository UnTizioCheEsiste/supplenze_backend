<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";
require_once PROJECT_ROOT_PATH . "/model/time.php";

class Absence extends Database
{
    /**
     * Ottiene la lista delle assenze (non ottiene se sono state coperte oppure no perchè questo sarà nello storico supplenze)
     */
    public function getArchiveAbsence()
    {
        // Get delle assenze dei docenti
        $sql = "SELECT a.id,a.data_inizio, a.data_fine, concat(u.nome,' ',u.cognome) as docente, a.certificato_medico, a.motivazione, a.nota
                FROM assenza a
                INNER JOIN utente u ON u.id = a.docente
                WHERE 1=1";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();


        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $absences;

        /* Get delle supplenze
        $sql = "SELECT id,assenza
                FROM supplenza
                WHERE 1 = 1";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $substitutions = $stmt->fetchAll(PDO::FETCH_ASSOC);*/
    }

    /**
     * Ottiene la singola assenza dall'id
     * 
     * @param int $id ID dell'assenza.
     */

    public function getAbsence($id)
    {
        // Get delle assenze dei docenti
        $sql = "SELECT a.id,a.data_inizio, a.data_fine, concat(u.nome,' ',u.cognome) as docente, a.certificato_medico, a.motivazione, a.nota
                FROM assenza a
                INNER JOIN utente u ON u.id = a.docente
                WHERE u.id=:id";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();


        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    /**
     * Divide l'assenza in supplenze da coprire.
     * 
     * @param int $id ID dell'assenza.
     * @return mixed supplenze singole e giorno.
     */
    public function ungroupAbsence($id)
    {
        // Get dell'assenza
        $sql = "SELECT *
                FROM assenza
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        // Data inizio
        $di = new DateTime($absence["data_inizio"]);

        // Data fine
        $df = new DateTime($absence["data_fine"]);

        $dateDifference = date_diff($df, $di);

        // Divisione data da ora
        $data_inizio = explode(" ", $absence["data_inizio"]);
        $data_fine = explode(" ", $absence["data_fine"]);

        $time = new Time();
        $hours = $time->getHour();

        // Se l'assenza rientra in un giorno
        if ($data_inizio[0] === $data_fine[0] && $dateDifference->h === "5" && $dateDifference->i === "30")
        {
            $hourId = 0;
            // Get dell'id dell'ora
            foreach ($hours as $hour)
            {
                // Se l'ora di inizio e di fine combaciano con un'ora della tabella "ora"
                if ($hour["data_inizio"] === $data_inizio[1] && $hour["data_fine"] === $data_fine[1])
                {
                    // hourId prende l'id di quell'ora
                    $hourId = $hour["id"];
                    break;
                }
            }

            // Insert della supplenza singola
            $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:assenza, :ora, :data_supplenza)";
                
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':assenza', $id, PDO::PARAM_INT);
            $stmt->bindValue(':ora', $hourId, PDO::PARAM_STR);
            $stmt->bindValue(':data_supplenza', $data_inizio[0], PDO::PARAM_STR);
            $exc=$stmt->execute();
            
            if(!$exc) return false;
        }
        else 
        {
            //fcreare un array con i giorni dalla data di inizio alla data di fine
            //ciclo dove cicli i giorni e poi cicli le ore (nested) in cui fai insert into supplenze 
            $current_date=strtotime($data_inizio[0]);
            $last_date=strtotime($data_fine[0]);

            $date_array=array();
            while($current_date<=$last_date){
                $date_array[]=date('Y-m-d',$current_date);
                $current_date=strtotime("+1 day",$current_date);
            }

            $insertCounter=0;
            foreach($date_array as $day)
            {
                foreach($hours as $hour)
                {
                    $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:assenza, :ora, :data_supplenza)";
                
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':assenza', $id, PDO::PARAM_INT);
                    $stmt->bindValue(':ora', $hour["id"], PDO::PARAM_STR);
                    $stmt->bindValue(':data_supplenza', $day, PDO::PARAM_STR);

                    $exc = $stmt->execute();
                    $exc ? $insertCounter++ : null;

                    if (!$exc) return false;
                }
                $insertCounter++;
            }
            return count($hours) + count($date_array) === $insertCounter;
        }

    }

    /**
     * Aggiunge assenze alla tabella "assenza".
     * 
     * @param int $userId ID dell'utente.
     * @param string $date Data dell'assenza.
     * @param int[] $hours ID delle/a Ore/a di assenza.
     * @param string $certificate_code Codice del certificato medico.
     * @param string $notes Note inerenti all'assenza.
     * @param int $reason Motivo dell'assenza.
     * 
     * @return boolean
     */
    public function addAbsenceHour($userId, $date, $hours, $certificate_code, $notes, $reason)
    {
        $time = new Time();

        if (count($hours) === 1) 
        {
            // Ora inizio assenza
            $hour = $time->getHourById($hours[0]);

            // Concatenzione giorno e ora
            $data_inizio = $date . " " . $hour["data_inizio"];
            $data_fine = $date . " " . $hour["data_fine"];

            // Insert dell'assenza
            $sql = "INSERT INTO assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
            VALUES (:id, :motivo, :certificato, :data_inizio, :data_fine, :nota)";
    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $reason, PDO::PARAM_INT);
            $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
            $stmt->bindValue(':data_inizio', $data_inizio, PDO::PARAM_STR);
            $stmt->bindValue(':data_fine', $data_fine, PDO::PARAM_STR);
            $stmt->bindValue(':nota', $notes, PDO::PARAM_STR);

            $exc = $stmt->execute();

            if (!$exc) return false;

            // Insert in supplenza
            $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:id, :ora, :data_supplenza)";

            $st = $this->conn->prepare($sql);
            $st->bindValue(':id', $this->conn->lastInsertId(), PDO::PARAM_INT);
            $st->bindValue(':ora', $hour, PDO::PARAM_STR);
            $st->bindValue('data_supplenza', $date, PDO::PARAM_STR);

            return $exc && $st->execute();


        } 
        else 
        {
            // Variabile per il controllo del corretto inserimento
            $insertCounter = 0;

            // Ciclo per le ore di assenza
            foreach ($hours as $h) 
            {
                // Ora inizio assenza
                $hour = $time->getHourById($h);

                // Concatenzione giorno e ora
                $data_inizio = $date . " " . $hour["data_inizio"];
                $data_fine = $date . " " . $hour["data_fine"];

                // Insert dell'assenza
                $sql = "INSERT INTO assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
                VALUES (:id, :motivo, :certificato, :data_inizio, :data_fine, :nota)";
    
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':motivo', $reason, PDO::PARAM_INT);
                $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
                $stmt->bindValue(':data_inizio', $data_inizio, PDO::PARAM_STR);
                $stmt->bindValue(':data_fine', $data_fine, PDO::PARAM_STR);
                $stmt->bindValue(':nota', $notes, PDO::PARAM_STR);

                $exc = $stmt->execute();
                $exc ? $insertCounter++ : null;

                if (!$exc) return false;

                // Insert in supplenza
                $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                        VALUES (:id, :ora, :data_supplenza)";

                $st = $this->conn->prepare($sql);
                $st->bindValue(':id', $this->conn->lastInsertId(), PDO::PARAM_INT);
                $st->bindValue(':ora', $hour, PDO::PARAM_STR);
                $st->bindValue('data_supplenza', $date, PDO::PARAM_STR);

                $st->execute();
            }

            // Se tutte le ore sono state aggiunte come assenze
            return count($hours) === $insertCounter;
        }
    }

    /**
     * Aggiunge una assenza giornaliera alla tabella "assenza".
     * 
     * @param int $userId ID dell'utente.
     * @param string $date Data dell'assenza.
     * @param string $certificate_code Codice del certificato medico.
     * @param string $notes Note inerenti all'assenza.
     * @param int $reason Motivo dell'assenza.
     * 
     * @return boolean
     */
    public function addAbsenceSingleDay($userId, $date, $certificate_code, $notes, $reason) 
    {
        $data_inizio = $date . " 08:00:00";
        $data_fine = $date . " 13:30:00";

        // Insert dell'assenza
        $sql = "INSERT INTO assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
        VALUES (:id, :motivo, :certificato, :data_inizio, :data_fine, :nota)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $reason, PDO::PARAM_INT);
        $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
        $stmt->bindValue(':data_inizio', $data_inizio, PDO::PARAM_STR);
        $stmt->bindValue(':data_fine', $data_fine, PDO::PARAM_STR);
        $stmt->bindValue(':nota', $notes, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Aggiunge un insieme di giornidi assenza alla tabella "assenza".
     * 
     * @param int $userId ID dell'utente.
     * @param string[] $dates Giorni di assenza.
     * @param string $certificate_code Codice del certificato medico.
     * @param string $notes Note inerenti all'assenza.
     * @param int $reason Motivo dell'assenza.
     * 
     * @return boolean
     */
    public function addAbsenceMultipleDay($userId, $dates, $certificate_code, $notes, $reason) 
    {
        $data_inizio = $dates[0] . " 08:00:00";
        $data_fine = $dates[count($dates) - 1] . " 13:30:00";

        // Insert dell'assenza
        $sql = "INSERT INTO assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
        VALUES (:id, :motivo, :certificato, :data_inizio, :data_fine, :nota)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $reason, PDO::PARAM_INT);
        $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
        $stmt->bindValue(':data_inizio', $data_inizio, PDO::PARAM_STR);
        $stmt->bindValue(':data_fine', $data_fine, PDO::PARAM_STR);
        $stmt->bindValue(':nota', $notes, PDO::PARAM_STR);

        return $stmt->execute();    
    }

    /**
     * Aggiunge un certificato medico ad una assenza già presente.
     * 
     * @param int $id ID assenza.
     * @param string $certificate_code Codice del certificato medico.
     * 
     * @return boolean
     */
    public function addCertificate($id, $certificate_code)
    {
        $sql = "UPDATE assenza
                SET certificato_medico = :certificato
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}