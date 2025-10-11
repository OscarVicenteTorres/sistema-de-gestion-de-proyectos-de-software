<?php
require_once __DIR__ . "/../models/Tarea.php";
require_once __DIR__ . "/../models/JustificacionTarea.php";
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../core/BaseApiController.php';

/*
CONTROLADOR TAREA OPTIMIZADO - BACKEND PARA ADMINISTRADOR  
Refactorizado con POO, elimina duplicación de código y mejora mantenibilidad.
Usa BaseApiController para funcionalidad común.
 */
class TareaController extends BaseApiController {
    private Tarea $tareaModel;
    private JustificacionTarea $justificacionModel;
    private Usuario $usuarioModel;

    public function __construct() {
        $this->tareaModel = new Tarea();
        $this->justificacionModel = new JustificacionTarea();
        $this->usuarioModel = new Usuario();
        $this->verificarAdmin();
    }

    // Vista principal de tareas
    public function index(): void {
        require __DIR__ . "/../views/admin/tareas.php";
    }

    // Lista todas las tareas con filtros
    // GET /index.php?c=Tarea&a=listar
    public function listar(): void {
        $this->ejecutarOperacion(function() {
            $filtros = $this->obtenerFiltros(['proyecto_id', 'area', 'estado', 'usuario_id']);
            $tareas = $this->tareaModel->obtenerTodas($filtros);
            
            return [
                'tareas' => $tareas,
                'total' => count($tareas),
                'filtros_aplicados' => $filtros
            ];
        });
    }

    // Obtiene estadísticas de tareas
    // GET /index.php?c=Tarea&a=estadisticas
    public function estadisticas(): void {
        $this->ejecutarOperacion(function() {
            return $this->tareaModel->obtenerEstadisticas();
        });
    }

    // Obtiene opciones para formularios (usuarios, proyectos)
    // GET /index.php?c=Tarea&a=opcionesFormulario
    public function opcionesFormulario(): void {
        $this->ejecutarOperacion(function() {
            return $this->tareaModel->obtenerOpcionesFormulario();
        });
    }

    // Crea nueva tarea
    // POST /index.php?c=Tarea&a=guardar
    public function guardar(): void {
        $datos = $this->getInput();
        
        // Validar campos requeridos
        $this->validarCampos($datos, [
            'titulo', 'descripcion', 'id_usuario', 'area_asignada', 
            'fecha_limite', 'id_proyecto'
        ]);

        // Sanitizar datos
        $datosSanitizados = $this->sanitizarDatosTarea($datos);
        
        $this->ejecutarOperacion(function() use ($datosSanitizados) {
            return $this->tareaModel->crear($datosSanitizados);
        });
    }

    // Actualiza tarea existente
    // POST /index.php?c=Tarea&a=actualizar
    public function actualizar(): void {
        $datos = $this->getInput();
        
        // Validar ID de tarea
        $id = $datos['id_tarea'] ?? $datos['id'] ?? null;
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        // Validar campos requeridos
        $this->validarCampos($datos, ['titulo', 'descripcion']);
        
        // Sanitizar datos
        $datosSanitizados = $this->sanitizarDatosTarea($datos);
        $datosSanitizados['id_tarea'] = $id;

        $this->ejecutarOperacion(function() use ($datosSanitizados, $id) {
            return $this->tareaModel->actualizar($id, $datosSanitizados);
        });
    }

    // Elimina tarea
    // POST /index.php?c=Tarea&a=eliminar
    public function eliminar(): void {
        $datos = $this->getInput();
        $id = $datos['id_tarea'] ?? $datos['id'] ?? null;
        
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($id) {
            return $this->tareaModel->eliminar($id);
        });
    }

    // Obtiene justificaciones de una tarea
    // GET /index.php?c=Tarea&a=justificaciones&id_tarea=X
    public function justificaciones(): void {
        $idTarea = $_GET['id_tarea'] ?? null;
        
        if (!$idTarea) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($idTarea) {
            return $this->justificacionModel->obtenerTodas(['id_tarea' => $idTarea]);
        });
    }

    // Obtiene una tarea por ID
    // GET /index.php?c=Tarea&a=obtenerPorId&id=X
    public function obtenerPorId(): void {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->jsonError('ID de tarea requerido');
        }

        $this->ejecutarOperacion(function() use ($id) {
            return $this->tareaModel->obtenerPorId($id);
        });
    }

    // Actualiza porcentaje de avance de tarea
    // POST /index.php?c=Tarea&a=actualizarProgreso
    public function actualizarProgreso(): void {
        $datos = $this->getInput();
        
        $this->validarCampos($datos, ['id_tarea', 'porcentaje_avance']);
        
        $porcentaje = (int)$datos['porcentaje_avance'];
        if ($porcentaje < 0 || $porcentaje > 100) {
            $this->jsonError('Porcentaje debe estar entre 0 y 100');
        }

        $this->ejecutarOperacion(function() use ($datos, $porcentaje) {
            return $this->tareaModel->actualizar($datos['id_tarea'], [
                'porcentaje_avance' => $porcentaje,
                'estado' => $porcentaje == 100 ? 'Completado' : 'En Progreso'
            ]);
        });
    }

    // Sanitiza y valida datos de tarea
    private function sanitizarDatosTarea(array $datos): array {
        $sanitizado = [
            'titulo' => $this->sanitizar($datos['titulo']),
            'descripcion' => $this->sanitizar($datos['descripcion']),
            'area_asignada' => $this->sanitizar($datos['area_asignada']),
            'estado' => $datos['estado'] ?? 'Pendiente',
            'porcentaje_avance' => (int)($datos['porcentaje_avance'] ?? 0)
        ];

        // Validar y asignar fechas
        if (!empty($datos['fecha_inicio']) && $this->validarFecha($datos['fecha_inicio'])) {
            $sanitizado['fecha_inicio'] = $datos['fecha_inicio'];
        }
        
        if (!empty($datos['fecha_limite']) && $this->validarFecha($datos['fecha_limite'])) {
            $sanitizado['fecha_limite'] = $datos['fecha_limite'];
        }

        // Validar IDs numéricos
        foreach (['id_usuario', 'id_proyecto'] as $campo) {
            if (isset($datos[$campo]) && is_numeric($datos[$campo])) {
                $sanitizado[$campo] = (int)$datos[$campo];
            }
        }

        // Validar área
        $areasValidas = ['Frontend', 'Backend', 'Infraestructura', 'UI/UX', 'QA'];
        if (!in_array($sanitizado['area_asignada'], $areasValidas)) {
            $this->jsonError('Área asignada no válida');
        }

        return $sanitizado;
    }
}
?>