<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsuarioController extends Controller {

    public function index() {
        // Verificar que solo admins puedan ver usuarios
        AuthMiddleware::verificarRol(['Admin']);
        
        // Renderiza la vista usuarios.php
        $this->render('admin/usuarios');
    }

    public function listar() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        try {
            $usuarioModel = new Usuario();
            $usuarios = $usuarioModel->obtenerTodos();
            
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
        }
    }

    public function obtenerDetalles() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario no proporcionado'
            ]);
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorId($id);
            
            if ($usuario) {
                echo json_encode([
                    'success' => true,
                    'usuario' => $usuario
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ]);
        }
    }

    public function crear() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        try {
            // Validar datos requeridos
            $nombre = $_POST['nombres'] ?? '';
            $apellido = $_POST['apellidos'] ?? '';
            $documento = $_POST['documento'] ?? '';
            $tipo_documento = $_POST['tipo_documento'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $area_trabajo = $_POST['area_trabajo'] ?? '';
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $tecnologias = $_POST['tecnologias'] ?? '';
            
            if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Todos los campos obligatorios deben ser completados'
                ]);
                return;
            }
            
            // Crear usuario
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->crear([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'documento' => $documento,
                'tipo_documento' => $tipo_documento,
                'correo' => $correo,
                'telefono' => $telefono,
                'area_trabajo' => $area_trabajo,
                'fecha_inicio' => $fecha_inicio,
                'contrasena' => $contrasena,
                'tecnologias' => $tecnologias,
                'id_rol' => 2, // Desarrollador por defecto
                'activo' => 1
            ]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el usuario'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function bloquear() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario no proporcionado'
            ]);
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->actualizarEstado($id, 0);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario bloqueado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al bloquear el usuario'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function activar() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario no proporcionado'
            ]);
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->actualizarEstado($id, 1);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario activado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al activar el usuario'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function eliminar() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario no proporcionado'
            ]);
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->eliminar($id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el usuario'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function dashboardAdmin() {
        // Verificar que el usuario esté autenticado y sea Admin
        AuthMiddleware::verificarRol(['Admin']);
        
        // Obtener todos los proyectos con encargados
        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->obtenerTodosConEncargados();
        
        // Pasar los datos a la vista
        $this->render('admin/dashboard', ['proyectos' => $proyectos]);
    }

    public function dashboardDesarrollador() {
        // Verificar que el usuario esté autenticado y sea Desarrollador
        AuthMiddleware::verificarRol(['Desarrollador']);
        
        // Dashboard para desarrollador
        $this->render('desarrollador/dashboard');
    }

    public function dashboardGestor() {
        // Verificar que el usuario esté autenticado y sea Gestor
        AuthMiddleware::verificarRol(['Gestor de Proyecto']);
        
        // Dashboard para gestor de proyecto
        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->obtenerTodosConEncargados();
        
        // Pasar los datos a la vista
        $this->render('admin/dashboard', ['proyectos' => $proyectos]);
    }
}
