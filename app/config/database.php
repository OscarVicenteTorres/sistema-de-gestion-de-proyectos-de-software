<?php
date_default_timezone_set('America/Lima');
class Database {
    private static $host = "localhost";
    private static $db_name = "prueba_proyectos";
    private static $username = "root";  
    private static $password = ""; // sin contra en local host
    private static $conn;

    public static function connect() {
        if (self::$conn == null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
                    self::$username,
                    self::$password
                );
                self::$conn->exec("set names utf8mb4");
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
