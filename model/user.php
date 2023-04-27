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
        $pwd1 = $stmt->fetch(PDO::FETCH_ASSOC);

        // Controllo la presenza dell'id dell'utente nella tabella reset
        $sql = "SELECT r.user_id, r.expires, r.completed
                FROM `reset` r
                INNER JOIN user u ON u.id = r.user_id
                WHERE u.email = :email AND r.`password` = :password";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);

        $stmt->execute();
        $pwd2 = $stmt->fetch(PDO::FETCH_ASSOC);

        // Controllo che esista l'user_id, che la data dell'expires sia maggiore del giorno attuale e che la reset non sia completed
        echo json_encode(date());
        if (!empty($pwd2["user_id"]) && strtotime($pwd2["expires"]) > strtotime(date("Y-m-d")) && pwd2["completed"] == 0){
            // Controllo l'esistenza dell'utente nella tabella user controllando le credenziali
            if(!empty($pwd1["id"])){
                // In questo caso, posso effettuare il login
                // return json_encode(pwd1["id"] + pwd1["password"]);
            } else {
                //è sbagliato il login
                return 0;
            }
        }
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
