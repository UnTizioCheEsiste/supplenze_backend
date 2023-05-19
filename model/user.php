<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class User extends Database
{
    /**
     * Ottieni gli elementi del URI.
     * 
     * @param int $userId ID dell'utente.
     * 
     * @return User
     */
    public function getUser($userId)
    {
        $sql = "SELECT nome, cognome, email, privilegio, telefono
                FROM utente
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $userId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Login dell'utente.
     * 
     * @param string $email Email dell'utente.
     * @param string $password Password dell'utente.
     * 
     * @return int Id dell'utente loggato.
     */
    public function login($email, $password)
    {
        // Controllo le credenziali dell'utente
        $sql = "SELECT id
                FROM utente
                WHERE email = :email AND `password` = :password";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);

        $stmt->execute();
        $pwdLoginNormale = $stmt->fetch(PDO::FETCH_ASSOC);

        // Controllo la presenza dell'id dell'utente nella tabella reset
        $sql = "SELECT r.id_utente  AS id, r.data_scadenza, r.completato
                FROM `reset` r
                INNER JOIN utente u ON u.id = r.id_utente
                WHERE u.email = :email AND r.`password` = :password";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);

        $stmt->execute();
        $pwdLoginReset = $stmt->fetch(PDO::FETCH_ASSOC);

        // Controllo che esista l'user_id, che la data dell'expires sia maggiore del giorno attuale e che la reset non sia completed
        if (!empty($pwdLoginReset["id"]) && strtotime($pwdLoginReset["data_scadenza"]) > strtotime(date("Y-m-d")) && $pwdLoginReset["completato"] == 0){
            return $pwdLoginReset["id"];
        }

        return $pwdLoginNormale;
    }

    /**
     * Registra l'utente.
     * 
     * @param string $nome Nome dell'utente.
     * @param string $cognome Cognome dell'utente.
     * @param string $email Email dell'utente.
     * @param string $telefono Numero di telefono dell'utente.
     * @param int $privilegio Il tipo di privilegio che l'utente avrà.
     */
    public function register($nome, $cognome, $email, $telefono, $privilegio)
    {
        // Generazione di una password casuale
        $bytes = random_bytes(5);
        $password = bin2hex($bytes);


        $sql = "insert into utente  (nome, cognome, email, `password` , telefono, privilegio)
                values (:nome, :cognome, :email, :password, :telefono, :privilegio);";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":nome", $nome, PDO::PARAM_STR);
        $stmt->bindValue(":cognome", $cognome, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);
        $stmt->bindValue(":telefono", $telefono, PDO::PARAM_STR);
        $stmt->bindValue(":privilegio", $privilegio, PDO::PARAM_INT);


        if ($stmt->execute())
        {
            return $password;
        }
        else
        {
            return 0;
        }
    }

    /**
     * Cambia password.
     * 
     * @param int $userId ID dell'utente.
     * @param string $oldPassword Vecchia password dell'utente.
     * @param string $newPassword Nuova password dell'utente.
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $sql = "update utente 
                set utente.`password` = :newPassword
                where utente.id = :userId and utente.`password` = :oldPassword;";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindValue(":oldPassword", $oldPassword, PDO::PARAM_STR);
        $stmt->bindValue(":newPassword", $newPassword, PDO::PARAM_STR);

        $stmt->execute();

        if($stmt->rowCount()==1)
        {
            return $stmt->rowCount();
        }else{
            $sql2="UPDATE utente u
            SET u.password = :newPassword
            WHERE u.id = :userId
            AND EXISTS (
            SELECT * FROM reset r
            WHERE r.id_utente = u.id
            AND r.password = :oldPassword
            AND r.completato  = 0
            AND now()<r.data_scadenza
            and now()>r.data_richiesta
            )";
            
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bindValue(":userId", $userId, PDO::PARAM_INT);
            $stmt2->bindValue(":oldPassword", $oldPassword, PDO::PARAM_STR);
            $stmt2->bindValue(":newPassword", $newPassword, PDO::PARAM_STR);

            $stmt2->execute();

            if($stmt2->rowCount()==1)
            {
                $sql3="update reset 
                set reset.completed=1
                where reset.id_utente=:id_utente";
            
                $stmt3 = $this->conn->prepare($sql3);
                $stmt3->bindValue(":userId", $userId, PDO::PARAM_INT);
                $stmt3->bindValue(":oldPassword", $oldPassword, PDO::PARAM_STR);
                $stmt3->bindValue(":newPassword", $newPassword, PDO::PARAM_STR);

                $stmt3->execute();

                return $stmt3->rowCount();
            }
        }
    }

    public function resetPassword($userId, $email)
    {
        $bytes = random_bytes(5); // 10 bytes will generate a string of length 20.
        $password = bin2hex($bytes); // converts binary data to hexadecimal representation

        $sql = "insert into reset(id_utente, `password`)
                values (:userId, :password);";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->rowCount();
    }
    
    public function getArchiveUser()
    {
        $sql = "SELECT utente.nome, utente.cognome, utente.email, p.nome as privilegio , utente.telefono
        FROM utente
        inner join privilegio p on p.id=utente.privilegio
        WHERE 1=1";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchiveUserAbsence($id)
    {
        $sql = "SELECT concat(u.nome,' ',u.cognome) as utente, m.nome, a.certificato_medico, a.data_inizio, a.data_fine, a.nota
        FROM assenza a 
        inner join utente u on u.id=a.docente
        inner join motivazione m on m.id=a.motivazione
        WHERE u.id=:id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}