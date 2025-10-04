<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsuarioController extends Controller {

    public function index() {
        // Verificar que solo admins puedan ver usuarios
        AuthMiddleware::verificarRol(['Admin']);
        
        try {
            $usuarioModel = new Usuario();
            $usuarios = $usuarioModel->obtenerTodos();
            
            // Renderiza la vista usuarios.php con los datos
            $this->render('admin/usuarios', ['usuarios' => $usuarios]);
        } catch (Exception $e) {
            // En caso de error, renderizar con array vacío
            $this->render('admin/usuarios', ['usuarios' => []]);
        }
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
            // Validación backend
            $errores = [];
            
            // Validar nombres (solo letras, 2-50 caracteres)
            $nombre = trim($_POST['nombres'] ?? '');
            if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 50 || !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) {
                $errores[] = 'Nombres inválidos (solo letras, 2-50 caracteres)';
            }
            
            // Validar apellidos (solo letras, 2-50 caracteres)
            $apellido = trim($_POST['apellidos'] ?? '');
            if (empty($apellido) || strlen($apellido) < 2 || strlen($apellido) > 50 || !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido)) {
                $errores[] = 'Apellidos inválidos (solo letras, 2-50 caracteres)';
            }
            
            // Validar documento (solo números, 8-12 dígitos)
            $documento = trim($_POST['documento'] ?? '');
            if (empty($documento) || strlen($documento) < 8 || strlen($documento) > 12 || !preg_match('/^[0-9]+$/', $documento)) {
                $errores[] = 'Documento inválido (solo números, 8-12 dígitos)';
            }
            
            // Validar correo
            $correo = trim($_POST['correo'] ?? '');
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 100) {
                $errores[] = 'Correo electrónico inválido';
            }
            
            // Validar teléfono (opcional pero si viene validar)
            $telefono = trim($_POST['telefono'] ?? '');
            if (!empty($telefono) && (strlen($telefono) < 7 || strlen($telefono) > 15 || !preg_match('/^[0-9+\s()-]+$/', $telefono))) {
                $errores[] = 'Teléfono inválido (7-15 caracteres)';
            }
            
            // Validar contraseña (mínimo 6 caracteres)
            $contrasena = $_POST['contrasena'] ?? '';
            if (empty($contrasena) || strlen($contrasena) < 6 || strlen($contrasena) > 50) {
                $errores[] = 'La contraseña debe tener entre 6 y 50 caracteres';
            }
            
            // Validar tecnologías
            $tecnologias = trim($_POST['tecnologias'] ?? '');
            if (empty($tecnologias) || strlen($tecnologias) < 2 || strlen($tecnologias) > 200) {
                $errores[] = 'Tecnologías inválidas (2-200 caracteres)';
            }
            
            $tipo_documento = trim($_POST['tipo_documento'] ?? '');
            $area_trabajo = trim($_POST['area_trabajo'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $rol = $_POST['rol'] ?? 'Desarrollador';
            
            // Si hay errores de validación, retornar
            if (!empty($errores)) {
                echo json_encode([
                    'success' => false,
                    'message' => implode(', ', $errores)
                ]);
                return;
            }
            
            // Convertir nombre de rol a id_rol
            $id_rol = 2; // Por defecto Desarrollador
            if ($rol === 'Administrador' || $rol === 'Admin') {
                $id_rol = 1;
            } elseif ($rol === 'Desarrollador') {
                $id_rol = 2;
            }
            
            // Crear usuario con datos sanitizados
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->crear([
                'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
                'apellido' => htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'),
                'documento' => $documento,
                'tipo_documento' => htmlspecialchars($tipo_documento, ENT_QUOTES, 'UTF-8'),
                'correo' => $correo,
                'telefono' => $telefono,
                'area_trabajo' => htmlspecialchars($area_trabajo, ENT_QUOTES, 'UTF-8'),
                'fecha_inicio' => $fecha_inicio,
                'contrasena' => $contrasena,
                'tecnologias' => htmlspecialchars($tecnologias, ENT_QUOTES, 'UTF-8'),
                'id_rol' => $id_rol,
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

    public function actualizar() {
        AuthMiddleware::verificarRol(['Admin']);
        header('Content-Type: application/json');
        
        try {
            $id_usuario = $_POST['id_usuario'] ?? '';
            
            if (empty($id_usuario)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de usuario no proporcionado'
                ]);
                return;
            }
            
            // Validación backend
            $errores = [];
            
            // Validar nombres (solo letras, 2-50 caracteres)
            $nombre = trim($_POST['nombres'] ?? '');
            if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 50 || !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) {
                $errores[] = 'Nombres inválidos (solo letras, 2-50 caracteres)';
            }
            
            // Validar apellidos (solo letras, 2-50 caracteres)
            $apellido = trim($_POST['apellidos'] ?? '');
            if (empty($apellido) || strlen($apellido) < 2 || strlen($apellido) > 50 || !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido)) {
                $errores[] = 'Apellidos inválidos (solo letras, 2-50 caracteres)';
            }
            
            // Validar documento (solo números, 8-12 dígitos)
            $documento = trim($_POST['documento'] ?? '');
            if (!empty($documento) && (strlen($documento) < 8 || strlen($documento) > 12 || !preg_match('/^[0-9]+$/', $documento))) {
                $errores[] = 'Documento inválido (solo números, 8-12 dígitos)';
            }
            
            // Validar correo
            $correo = trim($_POST['correo'] ?? '');
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 100) {
                $errores[] = 'Correo electrónico inválido';
            }
            
            // Validar teléfono (opcional pero si viene validar)
            $telefono = trim($_POST['telefono'] ?? '');
            if (!empty($telefono) && (strlen($telefono) < 7 || strlen($telefono) > 15 || !preg_match('/^[0-9+\s()-]+$/', $telefono))) {
                $errores[] = 'Teléfono inválido (7-15 caracteres)';
            }
            
            // Validar contraseña solo si se proporciona
            $contrasena = $_POST['contrasena'] ?? '';
            if (!empty($contrasena) && (strlen($contrasena) < 6 || strlen($contrasena) > 50)) {
                $errores[] = 'La contraseña debe tener entre 6 y 50 caracteres';
            }
            
            // Validar tecnologías
            $tecnologias = trim($_POST['tecnologias'] ?? '');
            if (!empty($tecnologias) && (strlen($tecnologias) < 2 || strlen($tecnologias) > 200)) {
                $errores[] = 'Tecnologías inválidas (2-200 caracteres)';
            }
            
            $tipo_documento = trim($_POST['tipo_documento'] ?? '');
            $area_trabajo = trim($_POST['area_trabajo'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $rol = $_POST['rol'] ?? '';
            
            // Si hay errores de validación, retornar
            if (!empty($errores)) {
                echo json_encode([
                    'success' => false,
                    'message' => implode(', ', $errores)
                ]);
                return;
            }
            
            // Convertir nombre de rol a id_rol si se proporciona
            $id_rol = null;
            if (!empty($rol)) {
                if ($rol === 'Administrador' || $rol === 'Admin') {
                    $id_rol = 1;
                } elseif ($rol === 'Desarrollador') {
                    $id_rol = 2;
                }
            }
            
            $datos = [
                'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
                'apellido' => htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'),
                'documento' => $documento,
                'tipo_documento' => htmlspecialchars($tipo_documento, ENT_QUOTES, 'UTF-8'),
                'correo' => $correo,
                'telefono' => $telefono,
                'area_trabajo' => htmlspecialchars($area_trabajo, ENT_QUOTES, 'UTF-8'),
                'fecha_inicio' => $fecha_inicio,
                'tecnologias' => htmlspecialchars($tecnologias, ENT_QUOTES, 'UTF-8')
            ];
            
            // Agregar id_rol si se proporcionó
            if ($id_rol !== null) {
                $datos['id_rol'] = $id_rol;
            }
            
            // Solo actualizar contraseña si se proporciona
            if (!empty($contrasena)) {
                $datos['contrasena'] = $contrasena;
            }
            
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->actualizar($id_usuario, $datos);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el usuario'
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

    public function dashboard() {
        // Método principal del dashboard que redirige según el rol
        session_start();
        
        if (!isset($_SESSION['usuario'])) {
            redirect('Auth', 'login');
        }

        $rol = $_SESSION['usuario']['rol'] ?? '';
        
        switch ($rol) {
            case 'Admin':
                $this->dashboardAdmin();
                break;
            case 'Desarrollador':
                $this->dashboardDesarrollador();
                break;
            default:
                redirect('Auth', 'login');
        }
    }
}
