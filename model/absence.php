<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";
require_once PROJECT_ROOT_PATH . "/model/time.php";

class Absence extends Database
{
    /**
     * Ottiene la lista delle assenze (non ottiene se sono state coperte oppure no perchè questo sarà nello storico supplenze)
     * 
     * @return mixed lista di assenze
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
                WHERE a.id=:id";

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
    public function divideAbsence($id)
    {
        // Get dell'assenza
        $sql = "SELECT *
                FROM assenza
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $absence = $stmt->fetch(PDO::FETCH_ASSOC);

        // Data inizio in formato datetime per il date difference
        $di = new DateTime($absence["data_inizio"]);

        // Data fine in formato datetime per il date difference
        $df = new DateTime($absence["data_fine"]);

        //date difference un oggetto json con la differenza divisa per giorni ore minuti secondi
        $dateDifference = date_diff($df, $di);

        // Divisione data da ora, data inizio e data fine sono due vettori con alla posizione 0 la data e alla posizione 1 l'ora
        $data_inizio = explode(" ", $absence["data_inizio"]);
        $data_fine = explode(" ", $absence["data_fine"]);

        $time = new Time();
        $hours = $time->getHour(); //oggetto che contiene tutte le ore del database con i rwlativi id, inizio e fine

        // Se l'assenza rientra in un giorno, ovvero data inizio e fine sono uguali e la differenza di ore è minore di 5 ore e mezza
        if ($data_inizio[0] === $data_fine[0] && $dateDifference->h === "5" && $dateDifference->i === "30") {
            //variabile che conterrà l'id dell'ora dato che siamo nel caso di assenze singola ora o ore multiple(comunque spezzate in singole)
            $hourId = 0;
            foreach ($hours as $hour) {
                // Se l'ora di inizio e di fine combaciano con un'ora della tabella "ora"
                if ($hour["data_inizio"] === $data_inizio[1] && $hour["data_fine"] === $data_fine[1]) {
                    // hourId prende l'id di quell'ora
                    $hourId = $hour["id"];
                    break;
                }
            }

            // Insert dell'assenza singola ora nella tabella supplenze, dato che dovrà essere coperta
            $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:assenza, :ora, :data_supplenza)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':assenza', $id, PDO::PARAM_INT);
            $stmt->bindValue(':ora', $hourId, PDO::PARAM_STR);
            $stmt->bindValue(':data_supplenza', $data_inizio[0], PDO::PARAM_STR);
            $exc = $stmt->execute();

            if (!$exc) return false; //se non va a buon fine la query
        } else //nel caso in cui sia una assenza di un giorno intero o di più giorni
        {
            //creare un array con i giorni dalla data di inizio alla data di fine
            //ciclo dove cicli i giorni e poi cicli le ore (nested) in cui fai insert into supplenze 
            $current_date = strtotime($data_inizio[0]); //data del primo giorno (alla posizaione 0 di data_inizio c'è la data percè precedentemente ho fatto lo split)
            $last_date = strtotime($data_fine[0]); //data dell'ultimo giorno

            //vado a creare un array con le date dalla prima all'ultima
            $date_array = array();
            while ($current_date <= $last_date) {
                $date_array[] = date('Y-m-d', $current_date);
                $current_date = strtotime("+1 day", $current_date);
            }

            $insertCounter = 0; //controllo se le ore inserite sono quelle effettivamente da coprire
            foreach ($date_array as $day) {
                foreach ($hours as $hour) {
                    $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:assenza, :ora, :data_supplenza)";

                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':assenza', $id, PDO::PARAM_INT);
                    $stmt->bindValue(':ora', $hour["id"], PDO::PARAM_STR);
                    $stmt->bindValue(':data_supplenza', $day, PDO::PARAM_STR);

                    $exc = $stmt->execute();
                    $exc ? $insertCounter++ : null; //se ho eseguito incremento il counter, se c'è un errore non lo incremento

                    if (!$exc) return false; //se non ho eseguito ritorno false
                }
                $insertCounter++;
            }
            return count($hours) + count($date_array) === $insertCounter; //se il counter non è uguale alla somma delle ore e dei giorni ritorno false
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

        if (count($hours) === 1) {
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

            // Insert in supplenza, dato che è un'ora singola la aggiungo anche in questa tabella
            $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                    VALUES (:id, :ora, :data_supplenza)";

            $st = $this->conn->prepare($sql);
            $st->bindValue(':id', $this->conn->lastInsertId(), PDO::PARAM_INT);
            $st->bindValue(':ora', $hours[0], PDO::PARAM_INT);
            $st->bindValue('data_supplenza', $date, PDO::PARAM_STR);

            return $exc && $st->execute(); //true se entrambe le query sono andate a buon fine
        } else //se ho più ore passo un vettore di ore dal frontend ma nelle tabelle devono essere divise e inserite come righe singole
        {
            // Variabile per il controllo del corretto inserimento
            $insertCounter = 0;

            // Ciclo per le ore di assenza (è un vettore)
            foreach ($hours as $h) {
                // Ora inizio assenza, la ottengo tramite il vettore di id ($hours) passato al metodo
                $hour = $time->getHourById($h);

                // Concatenzione giorno e ora (date è fissa perchè siamo in assenza di più ore nello stesso giorno)
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
                $exc ? $insertCounter++ : null; //incremento un counter per vedere se ho inserito tutte le ore

                if (!$exc) return false;

                // Insert in supplenza, ache qui inserisco l'ora di assenza anche nella tabella supplenze
                $sql = "INSERT INTO supplenza (assenza, ora, data_supplenza)
                        VALUES (:id, :ora, :data_supplenza)";

                $st = $this->conn->prepare($sql);
                $st->bindValue(':id', $this->conn->lastInsertId(), PDO::PARAM_INT);
                $st->bindValue(':ora', $hours[0], PDO::PARAM_INT);
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

        $result = $stmt->execute();
        $this->divideAbsence($this->conn->lastInsertId());
        return $result;
    }

    /**
     * Aggiunge un insieme di giornidi assenza alla tabella "assenza". Queste sono accorpate e saranno disaccorpate dalla api ungroup
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

        $result = $stmt->execute();
        $this->divideAbsence($this->conn->lastInsertId());
        return $result;
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

    /**
     * Visualizza le supplenze relative ad una assenza.
     * 
     * @param int $id l'id dell'assenza.
     * 
     * @return mixed lista delle assenze con relativo supplente che le ha coperte.
     */
    public function ungroupAbsence($id)
    {
        $sql = "SELECT s.id, o.data_inizio as ora_inizio, o.data_fine as ora_fine, s.data_supplenza, s.supplente, s.da_retribuire, s.non_necessaria, s.nota
        FROM supplenza s
        LEFT JOIN utente u on u.id=s.supplente
        INNER JOIN ora o on o.id=s.ora
        INNER JOIN assenza a on a.id=s.assenza
        WHERE a.id=:id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rimuove una assenza e le relative supplenze da coprire.
     * 
     * @param int $id ID dell'assenza.
     * 
     * @return bool True se l'eminazione è andata a buon fine.
     */
    public function removeAbsence($id)
    {
        // Per vincoli referenziali prima vengono eliminate le supplenze, successivamente l'assenza
        $substitutions = $this->ungroupAbsence($id);

        // Se una delle eliminazioni non va a buon fine la variabile assume valore false
        $deletedSubs = true;
        foreach ($substitutions as $sub) {
            $sql = "DELETE
                    FROM supplenza
                    WHERE supplenza.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":id", $sub["id"], PDO::PARAM_INT);
            $stmt->execute() ? null : $deletedSubs = false;
        }

        $sql = "DELETE
        FROM assenza
        WHERE assenza.id=:id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $deletedAbs = $stmt->execute();

        return $deletedAbs && $deletedSubs;
    }
}
