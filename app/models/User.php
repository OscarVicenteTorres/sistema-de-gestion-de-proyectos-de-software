<?php
class User
{
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function login($email, $password)
    {
        // OJO: la columna en la BD se llama "correo", no "email"
        $query = "SELECT * FROM " . $this->table_name . " WHERE correo = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validación de contraseña contra la columna "contrasena_hash"
        if ($user && password_verify($password, $user['contrasena_hash'])) {
            return $user;
        }
        return false;
    }

}
