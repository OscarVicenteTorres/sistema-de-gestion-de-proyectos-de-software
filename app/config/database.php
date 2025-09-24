<?php
class Database {
    private $host = "localhost";
    private $db_name = "project_management";
    private $username = "root"; // en XAMPP normalmente es root
    private $password = "";     // en XAMPP normalmente sin contraseña
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                                   $this->username,
                                   $this->password);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Error en la conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
