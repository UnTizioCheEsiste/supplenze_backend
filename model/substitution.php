<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Substitution extends Database
{
    public function addSubstitute($id_absence, $id_user, $not_necessary, $to_pay){ // da retribuire
        //L’api aggiunge il sostituto alla lezione. “non necessaria” serve per 
        //indicare se la lezione ha necessità di avere il supplente o meno

        $sql = "INSERT INTO supplenza (assenza, supplente, non_necessaria, da_retribuire)
                VALUES (:id_absence, :id_user, :not_necessary, :to_pay)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id_absence", $id_absence, PDO::PARAM_INT);
        $stmt->bindValue(":id_user", $id_user, PDO::PARAM_INT);
        $stmt->bindValue(":not_necessary", $not_necessary, PDO::PARAM_INT);
        $stmt->bindValue(":to_pay", $to_pay, PDO::PARAM_INT);
        if ($stmt->execute()) //se esegue allora si restituisce true per poi controllare la corretta esecuzione
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // public function addSubstituteTeaching(){ // Aggiunge una disponibilità per fare supplenza
    //     ID User, giorno (della settimana), ora (della lezione), tipo ora (ora buca, compresenza…)

    // }

    public function getArchiveSubstitution() { 
        // Restituisce la lista delle supplenze. Assente e supplente sono delle concatenazioni di nome e cognome
        $sql = "SELECT s.id, CONCAT(u.nome, ' ', u.cognome) as assente, CONCAT(u2.nome, ' ', u2.cognome) as supplente, CONCAT(o.data_inizio, ' - ', o.data_fine) as ora, s.da_retribuire
                FROM supplenza s
                INNER JOIN assenza a
                ON a.id = s.assenza
                INNER JOIN utente u
                ON u.id = a.docente
                INNER JOIN utente u2
                ON u2.id = s.supplente
                INNER JOIN ora o
                ON o.id = s.ora
                WHERE 1=1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $archivesub = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $archivesub;
    }

    public function getArchiveUserSubstitution($id_user) {
        // Restituisce tutte le supplenze fatte da un docente
        $sql = "SELECT s.id, CONCAT(o.data_inizio, ' - ', o.data_fine) as ora, s.nota, s.da_retribuire
        FROM supplenza s
        INNER JOIN ora o
        ON o.id = s.ora
        WHERE s.supplente = :id_user";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id_user", $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $archiveUserSub = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $archiveUserSub;
    }

}