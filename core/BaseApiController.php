<?php
/*
BaseApiController - Controlador base para APIs con funcionalidad común

Proporciona métodos reutilizables para:
- Validación de entrada
- Respuestas JSON estandarizadas
- Manejo centralizado de errores
- Verificación de autenticación
*/
abstract class BaseApiController extends Controller {
    
    // Envía respuesta JSON exitosa
    protected function jsonSuccess($data = [], string $mensaje = 'Operación exitosa'): void {
        $this->jsonResponse([
            'exito' => true,
            'mensaje' => $mensaje,
            'datos' => $data
        ]);
    }

    // Envía respuesta JSON de error
    protected function jsonError(string $mensaje = 'Error interno', array $detalles = [], int $codigo = 400): void {
        http_response_code($codigo);
        $this->jsonResponse([
            'exito' => false,
            'mensaje' => $mensaje,
            'detalles' => $detalles
        ]);
    }

    // Envía respuesta JSON y termina ejecución
    private function jsonResponse(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Obtiene y valida entrada JSON del request
    protected function getJsonInput(): array {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonError('Formato JSON inválido', ['error' => json_last_error_msg()]);
        }
        
        return $input ?: [];
    }

    // Obtiene entrada híbrida (JSON o POST)
    protected function getInput(): array {
        $jsonInput = $this->getJsonInput();
        return !empty($jsonInput) ? $jsonInput : $_POST;
    }

    // Valida campos requeridos
    protected function validarCampos(array $datos, array $requeridos): array {
        $errores = [];
        
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
                $errores[] = "El campo '{$campo}' es requerido";
            }
        }
        
        if (!empty($errores)) {
            $this->jsonError('Datos incompletos', $errores, 422);
        }
        
        return $datos;
    }

    // Ejecuta operación con manejo centralizado de errores
    protected function ejecutarOperacion(callable $operacion): void {
        try {
            $resultado = $operacion();
            
            if (is_array($resultado) && isset($resultado['exito'])) {
                if ($resultado['exito']) {
                    $this->jsonSuccess($resultado['datos'] ?? [], $resultado['mensaje'] ?? 'Operación exitosa');
                } else {
                    $this->jsonError($resultado['mensaje'] ?? 'Error en la operación');
                }
            } else {
                $this->jsonSuccess($resultado);
            }
            
        } catch (Exception $e) {
            error_log("Error en operación: " . $e->getMessage());
            $this->jsonError('Error interno del servidor');
        }
    }

    // Verifica autenticación de administrador
    protected function verificarAdmin(): void {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Admin') {
            if ($this->esRequestAjax()) {
                $this->jsonError('No autorizado', [], 401);
            } else {
                redirect('Auth', 'login');
            }
        }
    }

    // Verifica si la request es AJAX
    protected function esRequestAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // Obtiene filtros de la URL limpiando valores vacíos
    protected function obtenerFiltros(array $camposPermitidos): array {
        $filtros = [];
        
        foreach ($camposPermitidos as $campo) {
            $valor = $_GET[$campo] ?? '';
            if (!empty($valor)) {
                $filtros[$campo] = $valor;
            }
        }
        
        return $filtros;
    }

    // Sanitiza entrada de texto
    protected function sanitizar(string $texto): string {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }

    // Valida email
    protected function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Valida fecha
    protected function validarFecha(string $fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}
?>