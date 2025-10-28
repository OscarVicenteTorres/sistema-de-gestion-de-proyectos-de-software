<?php
require_once __DIR__ . '/../../core/BaseApiController.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Proyecto.php'; // Necesario para los dashboards

class UsuarioController extends BaseApiController {

    private Usuario $usuarioModel;

    public function __construct() {
        // La sesión se verifica en los métodos que lo requieren
        $this->usuarioModel = new Usuario();
    }

    // --- Vistas y Dashboards ---

    public function index(): void {
        $this->verificarAdmin();
        $this->render('admin/usuarios', [
            'usuarios' => $this->usuarioModel->obtenerTodos()
        ]);
    }

    public function dashboardAdmin(): void {
        $this->verificarAdmin();
        $proyectoModel = new Proyecto();
        $this->render('admin/dashboard', [
            'proyectos' => $proyectoModel->obtenerTodosConEncargados()
        ]);
    }

    public function dashboardDesarrollador(): void {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Desarrollador') {
            redirect('Auth', 'login');
        }
        $this->render('desarrollador/dashboard');
    }

    // --- API Endpoints para Gestión de Usuarios (JSON) ---

    public function listar(): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() {
            $filtros = $this->obtenerFiltros(['area_trabajo']);
            return ['usuarios' => $this->usuarioModel->obtenerTodos($filtros)];
        });
    }

    public function obtenerDetalles(): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return ['exito' => false, 'mensaje' => 'ID de usuario no proporcionado'];
            }
            $usuario = $this->usuarioModel->obtenerPorId($id);
            if ($usuario) {
                return ['usuario' => $usuario];
            }
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado'];
        });
    }

    public function crear(): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() {
            $datos = $this->getInput();
            $this->validarCampos($datos, ['nombres', 'apellidos', 'documento', 'correo', 'contrasena', 'tecnologias', 'rol']);

            // Aquí puedes agregar validaciones más específicas si lo deseas
            if ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
                return ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden'];
            }

            // Mapeo de rol a id_rol
            $id_rol = ($datos['rol'] === 'Administrador' || $datos['rol'] === 'Admin') ? 1 : 2;

            $resultado = $this->usuarioModel->crear([
                'nombre' => $this->sanitizar($datos['nombres']),
                'apellido' => $this->sanitizar($datos['apellidos']),
                'documento' => $datos['documento'],
                'tipo_documento' => $this->sanitizar($datos['tipo_documento']),
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'],
                'area_trabajo' => $this->sanitizar($datos['area_trabajo']),
                'fecha_inicio' => $datos['fecha_inicio'],
                'contrasena' => $datos['contrasena'],
                'tecnologias' => $this->sanitizar($datos['tecnologias']),
                'id_rol' => $id_rol,
                'activo' => 1
            ]);

            return $resultado
                ? ['mensaje' => 'Usuario creado exitosamente']
                : ['exito' => false, 'mensaje' => 'Error al crear el usuario'];
        });
    }

    public function actualizar(): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() {
            $datos = $this->getInput();
            $id_usuario = $datos['id_usuario'] ?? null;
            if (!$id_usuario) {
                return ['exito' => false, 'mensaje' => 'ID de usuario no proporcionado'];
            }

            // Validaciones
            $this->validarCampos($datos, ['nombres', 'apellidos', 'correo', 'rol']);
            if (!empty($datos['contrasena']) && $datos['contrasena'] !== $datos['confirmar_contrasena']) {
                return ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden'];
            }

            // Preparar datos para actualizar
            $datosActualizar = [
                'nombre' => $this->sanitizar($datos['nombres']),
                'apellido' => $this->sanitizar($datos['apellidos']),
                'documento' => $datos['documento'],
                'tipo_documento' => $this->sanitizar($datos['tipo_documento']),
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'],
                'area_trabajo' => $this->sanitizar($datos['area_trabajo']),
                'fecha_inicio' => $datos['fecha_inicio'],
                'tecnologias' => $this->sanitizar($datos['tecnologias']),
                'id_rol' => ($datos['rol'] === 'Administrador' || $datos['rol'] === 'Admin') ? 1 : 2,
            ];

            if (!empty($datos['contrasena'])) {
                $datosActualizar['contrasena'] = $datos['contrasena'];
            }

            $resultado = $this->usuarioModel->actualizar($id_usuario, $datosActualizar);

            return $resultado
                ? ['mensaje' => 'Usuario actualizado exitosamente']
                : ['exito' => false, 'mensaje' => 'Error al actualizar el usuario'];
        });
    }

    private function cambiarEstado(int $estado, string $mensajeExito, string $mensajeError): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() use ($estado, $mensajeExito, $mensajeError) {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return ['exito' => false, 'mensaje' => 'ID de usuario no proporcionado'];
            }
            $resultado = $this->usuarioModel->actualizarEstado($id, $estado);
            return $resultado
                ? ['mensaje' => $mensajeExito]
                : ['exito' => false, 'mensaje' => $mensajeError];
        });
    }

    public function bloquear(): void {
        $this->cambiarEstado(0, 'Usuario bloqueado exitosamente', 'Error al bloquear el usuario');
    }

    public function activar(): void {
        $this->cambiarEstado(1, 'Usuario activado exitosamente', 'Error al activar el usuario');
    }

    public function eliminar(): void {
        $this->verificarAdmin();
        $this->ejecutarOperacion(function() {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return ['exito' => false, 'mensaje' => 'ID de usuario no proporcionado'];
            }
            // Eliminación segura en transacción: desvincula tareas y borra el usuario.
            $resultado = $this->usuarioModel->eliminarConTransaccion($id);
            return $resultado
                ? ['mensaje' => 'Usuario eliminado permanentemente']
                : ['exito' => false, 'mensaje' => 'Error al eliminar el usuario'];
        });
    }
}