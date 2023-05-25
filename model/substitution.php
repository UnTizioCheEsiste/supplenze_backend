<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Substitution extends Database
{
    /**
     * Aggiunge il supplente alla lezione
     * @param int $id_absence ID dell'assenza
     * @param int $id_user ID del supplente
     * @param bool $not_necessary serve per indicare se la lezione ha necessità di avere il supplente o meno
     * @param bool $to_pay indica se la supplenza è da retribuire 
     * @param int $hour ID dell'ora di lezione
     * @param string $substitution_date indica la data della supplenza
     * @param string $note indica la descrizione relativa alla supplenza
     * @return bool ritorna 1 se va a buon fine, altrimenti 0
     */
    public function addSubstitute($id_absence, $id_user, $not_necessary, $to_pay, $hour, $substitution_date, $note)
    {
        $sql = "INSERT INTO supplenza (assenza, supplente, non_necessaria, da_retribuire, ora, data_supplenza, nota)
                VALUES (:id_absence, :id_user, :not_necessary, :to_pay, :hourr, :substitution_date, :note)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id_absence", $id_absence, PDO::PARAM_INT);
        $stmt->bindValue(":id_user", $id_user, PDO::PARAM_INT);
        $stmt->bindValue(":not_necessary", $not_necessary, PDO::PARAM_INT);
        $stmt->bindValue(":to_pay", $to_pay, PDO::PARAM_INT);
        $stmt->bindValue(":hourr", $hour, PDO::PARAM_INT);
        $stmt->bindValue(":substitution_date", $substitution_date, PDO::PARAM_STR);
        $stmt->bindValue(":note", $note, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Restituisce la lista delle supplenze
     * @return Substitution tutte le informazioni relative alla supplenza
     */
    public function getArchiveSubstitution()
    {
        $sql = "SELECT s.id, CONCAT(u1.nome, ' ', u1.cognome) as assente, 
        CONCAT(u2.nome, ' ', u2.cognome) as supplente, 
        CONCAT(o.data_inizio, ' - ', o.data_fine) as ora, s.da_retribuire
        FROM supplenza s
        LEFT JOIN utente u1 ON u1.id = s.supplente
        LEFT JOIN utente u2 ON u2.id = s.supplente
        INNER JOIN ora o ON o.id = s.ora
        UNION 
        SELECT s.id, CONCAT(u1.nome, ' ', u1.cognome) as assente, 
        CONCAT(u2.nome, ' ', u2.cognome) as supplente, 
        CONCAT(o.data_inizio, ' - ', o.data_fine) as ora, s.da_retribuire
        FROM supplenza s
        LEFT JOIN utente u1 ON u1.id = s.supplente
        LEFT JOIN utente u2 ON u2.id = s.supplente
        INNER JOIN ora o ON o.id = s.ora
        WHERE u1.id IS NULL OR u2.id IS NULL;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $archivesub = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $archivesub;
    }

    /**
     * Restituisce tutte le supplenze fatte da un docente
     * @param int $id_user ID del docente
     * @return Substitution tutte le informazioni utili relative alla supplenza
     */
    public function getArchiveUserSubstitution($id_user)
    {
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

    /**
     * Restituisce tutte le supplenze fatte da un docente
     * @param int $id ID della supplenza
     * @return bool ritorna 1 se va a buon fine, altrimenti 0
     */
    public function removeSubstitution($id)
    {
        // Ritorno l'email del docente per poi inviare l'email 
        $sql1 = "SELECT u.email
        from utente u
        inner join supplenza s
        on s.supplente = u.id
        where s.id = :id";

        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->bindValue(":id", $id, PDO::PARAM_INT);
        try{
            $stmt1->execute();
        } catch (Exception $e){
            return 0;
        }
        $email = $stmt1->fetch(PDO::FETCH_ASSOC);

        // Elimino la supplenza
        $sql = "DELETE
        from supplenza s
        WHERE s.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            return 0;
        }
        return $email;

    }

}