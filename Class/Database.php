<?php
require_once __DIR__ . '/EnvLoader.php';

class Database {
    private $conn;

    public function __construct() {
        
        if (!getenv('DB_HOST')) {
            EnvLoader::load(__DIR__ . '/../.env');
        }

        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $db   = getenv('DB_NAME');

        try {
            $this->conn = new mysqli($host, $user, $pass, $db);
            if ($this->conn->connect_error) {
                throw new Exception("Koneksi gagal: " . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>