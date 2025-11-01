<?php
require_once __DIR__ . '/../config/database.php';

class Asistencia
{
    private $db;

    public function __construct()
    {
        // Asegurar zona horaria para PHP (si no la pones en index.php/config)
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('America/Lima');
        }
        $this->db = Database::connect();
    }

    public function registrarEntrada($usuario_id)
    {
        $fecha = date('Y-m-d');
        $hora_entrada = date('H:i:s'); // 24h, por ejemplo 14:30:00

        // Comprobar existencia
        $queryCheck = "SELECT 1 FROM asistencias WHERE id_usuario = :id_usuario AND fecha = :fecha";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->execute([
            ':id_usuario' => $usuario_id,
            ':fecha' => $fecha
        ]);

        if ($stmtCheck->rowCount() > 0) {
            return false;
        }

        $query = "INSERT INTO asistencias (id_usuario, fecha, hora_entrada, estado)
                  VALUES (:id_usuario, :fecha, :hora_entrada, 'Presente')";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_usuario' => $usuario_id,
            ':fecha' => $fecha,
            ':hora_entrada' => $hora_entrada
        ]);
    }

    public function registrarSalida($usuario_id)
    {
        $fecha = date('Y-m-d');
        $hora_salida = date('H:i:s');

        $query = "UPDATE asistencias 
                  SET hora_salida = :hora_salida 
                  WHERE id_usuario = :id_usuario AND fecha = :fecha";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':hora_salida' => $hora_salida,
            ':id_usuario' => $usuario_id,
            ':fecha' => $fecha
        ]);
    }

    public function obtenerHistorial($id_usuario)
    {
        $query = "SELECT fecha, hora_entrada, hora_salida, estado
                  FROM asistencias
                  WHERE id_usuario = :id_usuario
                  ORDER BY fecha DESC
                  LIMIT 10";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_usuario' => $id_usuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

