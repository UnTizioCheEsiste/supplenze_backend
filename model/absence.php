<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";
require_once PROJECT_ROOT_PATH . "/model/time.php";

class Absence extends Database
{
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

        // Get delle supplenze
        $sql = "SELECT id,assenza
                FROM supplenza
                WHERE 1 = 1";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $substitutions = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // Array associativo con tutte le informazioni necessarie
        $users = array();
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
        if ($data_inizio[0] === $data_fine[0]) 
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
            $stmt->execute();
        }
        else 
        {/* Assenza giorni multipli */}

    }

    /**
     * Aggiunge assenze alla tabella "assenza"
     * 
     * @param int $userId ID dell'utente.
     * @param string[] $hours Ore/a di assenza.
     * @param string $certificate_code Codice del certificato medico.
     * @param string $notes Note inerenti all'assenza.
     * @param int $reason Motivo dell'assenza.
     * 
     * @return boolean
     */
    public function addAbsenceHour($userId, $hours, $certificate_code, $notes, $reason) 
    {
        if (count($hours) === 1) 
        {
            // Divisione data da ora
            $absence_date = explode(" ", $hours[0]);

            // Ora inizio assenza
            $ora_inizio = $absence_date[1];

            // <!> Come faccio a capire se l'ora è formata da 30min o da 1h? <!>

            // Insert dell'assenza
            $sql = "INSERT INTO assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
            VALUES (:id, :motivo, :certificato, :data_inizio, :data_fine, :nota)";
    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $reason, PDO::PARAM_INT);
            $stmt->bindValue(':certificato', $certificate_code, PDO::PARAM_STR);
            $stmt->bindValue(':data_inizio', $ora_inizio, PDO::PARAM_STR);
            $stmt->bindValue(':data_fine', /*<!> $ora_fine <!>*/, PDO::PARAM_STR);
            $stmt->bindValue(':nota', $notes, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }
}