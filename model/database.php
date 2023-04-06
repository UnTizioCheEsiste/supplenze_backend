<?php
class Database
{
    protected $conn = null;

    public function __construct() {
        $host = DB_HOST;
        $port = "3306";
        $dbname = DB_NAME;

        try {
            $this->conn = new PDO("mysql:host=$host;port=$port;charset=utf8mb4;dbname=$dbname",
            DB_USERNAME,
            DB_PASSWORD
        );
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
}
