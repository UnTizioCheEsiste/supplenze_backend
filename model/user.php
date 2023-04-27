<?php
require_once PROJECT_ROOT_PATH . "/model/database.php";

class User extends Database
{
    /**
     * Ottieni gli elementi del URI.
     * 
     * @return User
     */
    public function getUser($id)
    {
        $sql = "SELECT nome, cognome, email, privilegio, telefono
                FROM utente
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
        $sql = "SELECT r.user_id AS id, r.expires, r.completed
                FROM `reset` r
                INNER JOIN utente u ON u.id = r.user_id
                WHERE u.email = :email AND r.`password` = :password";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);

        $stmt->execute();
        $pwdLoginReset = $stmt->fetch(PDO::FETCH_ASSOC);

        // Controllo che esista l'user_id, che la data dell'expires sia maggiore del giorno attuale e che la reset non sia completed
        if (!empty($pwdLoginReset["id"]) && strtotime($pwdLoginReset["expires"]) > strtotime(date("Y-m-d")) && $pwdLoginReset["completed"] == 0){
            return $pwdLoginReset["id"];
        }

        return $pwdLoginNormale["id"];
    }

    public function register($nome, $cognome, $email, $telefono, $privilegio)
    {
        $bytes = random_bytes(5); // 10 bytes will generate a string of length 20.
        $password = bin2hex($bytes); // converts binary data to hexadecimal representation


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

}
