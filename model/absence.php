<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class Absence extends Database
{
    public function getArchiveAbsence() {
        // Get delle assenze dei docenti
        $sql = "SELECT a.data_inizio, a.data_fine, u.nome, u.cognome, a.certificato_medico, a.motivazione
                FROM assenza a
                INNER JOIN utente u ON u.id = a.docente
                WHERE 1=1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get delle supplenze
        $sql = "SELECT id
                FROM supplenza
                WHERE 1 = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $substitutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Array associativo con tutte le informazioni necessarie
        $users = array();

        // Copertura assenza
        $coperta = false;

        // Controllo se l'assenza del docente è stata coperta da una supplenza
        foreach ($absences as $obj1) {
            foreach ($substitutions as $obj2) {

                // Se l'assenza è coperta
                if ($obj1->id === $obj2->assenza) {
                    $coperta = true;
                } else {
                    $coperta = false;
                }

                $temp = new class {
                    public $nome = $obj1->nome;
                    public $cognome = $obj1->cognome;
                    public $data_inizio = $obj1->data_inizio;
                    public $data_fine = $obj1->data_fine;
                    public $motivazione = $obj1->motivazione;
                    public $certificato_medico = $obj1->certificato_medico;
                    public $supplenza = $this->$coperta;
                };
                
                array_push($users, $temp);
                
            }
        }
    }
}