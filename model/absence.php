<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Absence extends Database
{
    public function getArchiveAbsence() {
        $sql = "SELECT a.data_inizio, a.data_fine, u.nome, u.cognome, a.certificato_medico, a.motivazione
                FROM assenza a
                INNER JOIN utente u ON u.id = a.docente
                WHERE 1=1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $absence = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // continuare con il in_array (tira giu tutte le supplenze e controlla che le assenze siano o no coperte)
        $sql = "SELECT id
                FROM supplenza
                WHERE assenza = :assenzaid";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':assenzaid', $absence["id"]);
    }
}