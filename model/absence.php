<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

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
}

// Controllo se l'assenza del docente è stata coperta da una supplenza
        /*foreach ($absences as $obj1) {
            foreach ($substitutions as $obj2) {


                // Da controllare il suo comportamento da array anche se è un oggetto
                echo json_encode($obj1);
                // Se l'assenza è coperta
                if ($obj1["id"] === $obj2["assenza"]) {
                    $coperta = true;
                    break;
                } else {
                    $coperta = false;
                }


                $temp = new class ($coperta, $obj1){
                    public $nome;
                    public $cognome;
                    public $data_inizio;
                    public $data_fine;
                    public $motivazione;
                    public $certificato_medico;
                    public $supplenza;


                    public function __construct($coperta, $obj) {
                        $this->supplenza = $coperta;
                        $nome = $obj["nome"];
                        $cognome = $obj["cognome"];
                        $data_inizio = $obj["data_inizio"];
                        $data_fine = $obj["data_fine"];
                        $motivazione = $obj["motivazione"];
                        $certificato_medico = $obj["certificato_medico"];
                    }
                };


                //echo json_encode($temp);
                array_push($users, $temp);
            }
        }*/