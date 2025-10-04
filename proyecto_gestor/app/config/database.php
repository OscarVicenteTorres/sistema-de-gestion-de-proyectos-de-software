<?php
class Database {
    private static $host = "localhost";
    private static $db_name = "prueba_proyectos";
    private static $username = "root";  // cambia si usas otro usuario
    private static $password = "";      // cambia si tu MySQL tiene contraseña
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
