/**
 * Script para la gestión de tareas del administrador.
 * Versión final y corregida por CorpiStar.
 *
 * ESTRUCTURA:
 * 1. Un único bloque `DOMContentLoaded` que espera a que toda la página esté cargada.
 * - Dentro, se asignan todos los event listeners a los botones y formularios.
 * - Se realiza la llamada inicial para cargar los proyectos.
 * 2. Definiciones de todas las funciones que serán llamadas por los event listeners.
 */

// ===================================================================================
// 1. INICIALIZACIÓN Y ASIGNACIÓN DE EVENTOS
// ===================================================================================
document.addEventListener('DOMContentLoaded', function() {

    // Asignar eventos a los elementos que existen desde el inicio.
    // Usamos una comprobación (if) para que el código no falle si un elemento no existe.

    const btnNuevaTarea = document.getElementById('btnNuevaTarea');
    if (btnNuevaTarea) {
        btnNuevaTarea.addEventListener('click', () => abrirModalTarea('crear'));
    }

    const formTarea = document.getElementById('formTarea');
    if (formTarea) {
        formTarea.addEventListener('submit', manejarSubmitFormulario);
    }

    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', ejecutarEliminacion);
    }
    
    // Botón para abrir Justificaciones (sin Bootstrap)
    const btnJustificaciones = document.getElementById('btnJustificaciones');
    if (btnJustificaciones) {
        btnJustificaciones.addEventListener('click', () => openModal('modalJustificaciones'));
    }
    
    const inputBuscarProyecto = document.getElementById('buscarProyecto');
    if (inputBuscarProyecto) {
        inputBuscarProyecto.addEventListener('input', filtrarTablaProyectos);
    }
    
    const inputBuscarTarea = document.getElementById('buscarTarea');
    if (inputBuscarTarea) {
        inputBuscarTarea.addEventListener('input', filtrarTablaTareas);
    }

    // Cuando se cambia el área en el formulario, repoblar el select de usuarios según el área seleccionada
    const selectArea = document.getElementById('tareaArea');
    if (selectArea) {
        selectArea.addEventListener('change', function() {
            const areaSeleccionada = this.value;
            poblarUsuarios(areaSeleccionada);
        });
    }

    // Delegación de eventos para los botones de la tabla de tareas
    const tareasTableBody = document.getElementById('tareasTableBody');
    if (tareasTableBody) {
        tareasTableBody.addEventListener('click', function(e) {
            const target = e.target;
            
            // Buscar el botón o el ícono dentro del botón
            const btnEdit = target.closest('.btn-edit');
            const btnDelete = target.closest('.btn-delete');
            
            // Buscar la fila (tr) más cercana
            const tr = target.closest('tr');

            if (tr) {
                const tareaId = tr.dataset.id;
                const proyectoId = document.getElementById('tareaProyectoId').value;
                
                if (btnEdit && tareaId) {
                    // Si se hizo clic en editar
                    e.stopPropagation(); // Evitar que el clic se propague a la fila
                    abrirEditarTarea(tareaId);
                } else if (btnDelete && tareaId) {
                    // Si se hizo clic en eliminar
                    e.stopPropagation(); // Evitar que el clic se propague a la fila
                    confirmarEliminar(tareaId, proyectoId);
                }
            }
        });
    }

    // Llamada inicial para poblar la primera vista.
    cargarProyectos();
    cargarEstadisticasProyectos(); // <-- ¡AQUÍ ESTÁ LA MAGIA!

    // Cerrar modales al hacer clic en el overlay o con ESC
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.addEventListener('click', () => closeAllModals());
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllModals();
    });
    // Cerrar con botones .close-modal-btn
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal');
            if (modal) closeModal(modal.id);
        });
    });
});


// ===================================================================================
// 2. DEFINICIÓN DE FUNCIONES
// ===================================================================================

const AJAX_HEADERS = {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json'
};

let tareaAEliminar = null; // Variable global para guardar la tarea a eliminar.

// ===================================================================================
// 2.1. SISTEMA DE MODALES CUSTOM (SIN BOOTSTRAP)
// ===================================================================================
function openModal(id) {
    const modal = document.getElementById(id);
    const overlay = document.getElementById('modal-overlay');
    if (!modal || !overlay) return;
    modal.classList.add('active');
    overlay.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const overlay = document.getElementById('modal-overlay');
    if (!modal || !overlay) return;
    modal.classList.remove('active');
    // Si no quedan otros modales activos, ocultar overlay
    const anyActive = document.querySelector('.modal.active');
    if (!anyActive) overlay.classList.remove('active');
}

function closeAllModals() {
    document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
    const overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.classList.remove('active');
}


/**
 * NUEVA FUNCIÓN: Carga las estadísticas de proyectos (Registrados, Completados, etc.)
 */
function cargarEstadisticasProyectos() {
    fetch('?c=Proyecto&a=estadisticasAjax', { 
        headers: { 'X-Requested-With': 'XMLHttpRequest' }, 
        credentials: 'same-origin' 
    })
    .then(response => {
        if (!response.ok) throw new Error('Error al cargar estadísticas de proyectos.');
        return response.json();
    })
    .then(data => {
        if (data.success && data.estadisticas) {
            const stats = data.estadisticas;
            
            const setStat = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value ?? 0;
            };

            // Actualizar las tarjetas con los IDs correctos de la vista
            setStat('statProyectosRegistrados', stats.total_registrados);
            setStat('statProyectosCompletados', stats.completados);
            setStat('statProyectosEnCurso', stats.en_curso);
            setStat('statProyectosVencidos', stats.vencidos);
        }
    })
    .catch(error => {
        console.error('[cargarEstadisticasProyectos] Error:', error);
        // Opcional: poner 'Error' en las tarjetas
        document.getElementById('statProyectosRegistrados').textContent = 'E';
        document.getElementById('statProyectosCompletados').textContent = 'E';
        document.getElementById('statProyectosEnCurso').textContent = 'E';
        document.getElementById('statProyectosVencidos').textContent = 'E';
    });
}


/**
 * Carga la lista de proyectos en la tabla inicial.
 */
function cargarProyectos() {
    const tbody = document.getElementById('proyectosTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="4" class="text-center">Cargando proyectos...</td></tr>`;

    fetch('?c=Proyecto&a=listarAjax', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor al cargar proyectos.');
            return response.json();
        })
        .then(data => {
            const proyectos = Array.isArray(data) ? data : (data.datos || []);

            if (proyectos.length > 0) {
                tbody.innerHTML = '';
                proyectos.forEach(proyecto => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.innerHTML = `
                        <td>${proyecto.id_proyecto ?? ''}</td>
                        <td>${escapeHtml(proyecto.nombre || '')}</td>
                        <td>${escapeHtml(proyecto.categoria ?? proyecto.area ?? '')}</td>
                        <td>
                            <div class="progress" style="height:12px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: ${Number(proyecto.porcentaje_avance ?? 0)}%;" aria-valuenow="${Number(proyecto.porcentaje_avance ?? 0)}">${Number(proyecto.porcentaje_avance ?? 0)}%</div>
                            </div>
                        </td>
                    `;
                    tr.addEventListener('click', () => {
                        seleccionarProyecto(proyecto.id_proyecto, proyecto.nombre || '', proyecto.descripcion || '');
                    });
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">No hay proyectos disponibles.</td></tr>`;
            }
        })
        .catch(error => {
            console.error('[cargarProyectos] Error:', error);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error al cargar proyectos.</td></tr>`;
        });
}

/**
 * Cambia a la vista de tareas para un proyecto específico.
 */
function seleccionarProyecto(id, nombre, descripcion) {
    document.getElementById('vistaProyectos').style.display = 'none';
    document.getElementById('vistaTareas').style.display = 'block';
    
    document.getElementById('proyectoInfo').classList.add('active');
    document.getElementById('nombreProyecto').textContent = nombre || '';
    document.getElementById('categoriaProyecto').textContent = descripcion || '';
    document.getElementById('tareaProyectoId').value = id;

    cargarTareas(id);
    cargarEstadisticas(id);
}

/**
 * Vuelve a la vista de selección de proyectos.
 */
function volverAProyectos() {
    document.getElementById('vistaTareas').style.display = 'none';
    document.getElementById('vistaProyectos').style.display = 'block';
}

/**
 * Carga las tareas de un proyecto específico.
 */
function cargarTareas(proyectoId) {
    const tbody = document.getElementById('tareasTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="10" class="text-center">Cargando tareas...</td></tr>`;

    fetch(`?c=Tarea&a=listar&proyecto_id=${encodeURIComponent(proyectoId)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor al cargar tareas.');
            return response.json();
        })
        .then(res => {
            const tareas = res.datos?.tareas || [];

            if (tareas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No hay tareas para este proyecto.</td></tr>`;
                return;
            }

            tbody.innerHTML = '';
            tareas.forEach(t => {
                const tr = document.createElement('tr');
                const tareaId = t.id_tarea ?? '';
                tr.dataset.id = tareaId;
                
                tr.innerHTML = `
                    <td></td>
                    <td>${escapeHtml(t.proyecto_nombre ?? '')}</td>
                    <td>${escapeHtml(t.titulo ?? '')}</td>
                    <td>${escapeHtml(((t.usuario_nombre ?? '') + ' ' + (t.usuario_apellido ?? '')).trim())}</td>
                    <td>${escapeHtml(t.estado ?? '')}</td>
                    <td>${escapeHtml(t.fecha_limite ?? '')}
                        <div class="btn-group ms-2" role="group">
                            <button class="btn btn-sm btn-outline-secondary btn-edit" type="button" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" type="button" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                
                tbody.appendChild(tr);

                // Los eventos ahora se manejan por delegación en DOMContentLoaded
            });
        })
        .catch(error => {
            console.error('[cargarTareas] Error:', error);
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al cargar tareas.</td></tr>`;
        });
}

/**
 * Carga las estadísticas de un proyecto.
 */
function cargarEstadisticas(proyectoId) {
    if (!proyectoId) return;
    fetch(`?c=Tarea&a=estadisticas&proyecto_id=${encodeURIComponent(proyectoId)}`, { headers: { 'X-Requested-with': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            const stats = res.datos || {};
            const setIf = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value ?? 0; };
            setIf('statTotal', stats.total_tareas ?? stats.total ?? 0);
            setIf('statCompletada', stats.completadas ?? 0);
            setIf('statJustificaciones', stats.justificaciones ?? 0);
            setIf('statVencidas', stats.vencidas ?? 0);
        })
        .catch(err => console.error('Error al cargar estadísticas', err));
}

/**
 * Popula el <select> de usuarios en el modal.
 */
/**
 * Popula el <select> de usuarios en el modal, filtrando por área.
 * @param {string|null} area - El área por la cual filtrar (ej. 'Frontend'). Si es null, no carga usuarios.
 */
function poblarUsuarios(area = null) {
    const select = document.getElementById('tareaUsuario');
    if (!select) return Promise.reject("Select de usuario no encontrado");

    // Deshabilitar y limpiar el select mientras se cargan los datos
    select.disabled = true;
    select.innerHTML = '<option value="">Cargando...</option>';
    
    // Si no se especifica un área, no cargar nada y pedir que se seleccione una.
    if (!area) {
        select.innerHTML = '<option value="">Selecciona un área primero</option>';
        select.disabled = false; // habilitar para que el usuario vea el mensaje
        return Promise.resolve();
    }
    
    // Construimos la URL con el filtro de área
    const url = `?c=Usuario&a=listar&area_trabajo=${encodeURIComponent(area)}`;

    console.log('[poblarUsuarios] solicitando', url);
    return fetch(url, { headers: { 'X-Requested-with': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            const usuarios = res.usuarios || res.datos?.usuarios || res.datos || [];
            console.log('[poblarUsuarios] recibidos usuarios:', usuarios.length);
            
            if (usuarios.length === 0) {
                select.innerHTML = `<option value="">No hay usuarios para el área "${area}"</option>`;
                return;
            }

            let html = `<option value="">Selecciona un desarrollador</option>`;
            usuarios.forEach(u => {
                const id = u.id_usuario ?? u.id ?? '';
                const nombre = ((u.nombre ?? '') + ' ' + (u.apellido ?? '')).trim();
                html += `<option value="${id}">${escapeHtml(nombre)}</option>`;
            });
            select.innerHTML = html;
        })
        .catch(() => {
            select.innerHTML = `<option value="">Error al cargar usuarios</option>`;
        })
        .finally(() => {
            // Habilitar el select al final del proceso
            select.disabled = false;
        });
}

/**
 * Configura y abre el modal de tareas en modo 'crear' o 'editar'.
 */
function abrirModalTarea(modo, tarea = null) {
    const modalEl = document.getElementById('modalTarea');
    if(!modalEl) return;
    
    const form = document.getElementById('formTarea');
    form.reset();
    document.getElementById('tareaId').value = '';

    // Resetear y deshabilitar el select de usuarios inicialmente
    const selectUsuario = document.getElementById('tareaUsuario');
    if (selectUsuario) {
        selectUsuario.innerHTML = '<option value="">Selecciona un área primero</option>';
        selectUsuario.disabled = true;
    }

    if (modo === 'crear') {
        document.getElementById('modalTareaTitle').textContent = 'Crear Tarea';
        document.getElementById('btnGuardarTarea').textContent = 'Crear Tarea';
        // Mostrar modal en crear: el select se habilitará cuando el admin seleccione un área
        openModal('modalTarea');
    } else { // modo 'editar'
        document.getElementById('modalTareaTitle').textContent = 'Editar Tarea';
        document.getElementById('btnGuardarTarea').textContent = 'Guardar cambios';
        
        if (tarea) {
            document.getElementById('tareaId').value = tarea.id_tarea ?? '';
            document.getElementById('tareaTitulo').value = tarea.titulo ?? '';
            document.getElementById('tareaDescripcion').value = tarea.descripcion ?? '';
            document.getElementById('tareaArea').value = tarea.area_asignada ?? '';
            document.getElementById('tareaFechaInicio').value = tarea.fecha_inicio ? tarea.fecha_inicio.split(' ')[0] : '';
            document.getElementById('tareaFechaLimite').value = tarea.fecha_limite ? tarea.fecha_limite.split(' ')[0] : '';
            
            // Cargamos los usuarios del área de la tarea y luego seleccionamos el correcto
            poblarUsuarios(tarea.area_asignada).then(() => {
                if (selectUsuario) selectUsuario.value = tarea.id_usuario ?? '';
                openModal('modalTarea');
            });
        }
    }
}


  //Obtiene los datos de una tarea y abre el modal para editarla.
 
function abrirEditarTarea(tareaId) {
    if (!tareaId) return;
    fetch(`?c=Tarea&a=obtenerPorId&id=${encodeURIComponent(tareaId)}`, { headers: { 'X-Requested-with': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            if (res.exito && res.datos) {
                abrirModalTarea('editar', res.datos);
            } else {
                alert(res.mensaje || 'No se pudo obtener la tarea');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al obtener la tarea');
        });
}


  //Maneja el envío del formulario de Tareas (Crear/Actualizar).
 
function manejarSubmitFormulario(e) {
    e.preventDefault();
    const id = document.getElementById('tareaId').value;
    const proyectoId = document.getElementById('tareaProyectoId').value;
    
    const payload = {
        titulo: document.getElementById('tareaTitulo').value,
        descripcion: document.getElementById('tareaDescripcion').value,
        id_usuario: document.getElementById('tareaUsuario').value,
        area_asignada: document.getElementById('tareaArea').value,
        fecha_inicio: document.getElementById('tareaFechaInicio').value,
        fecha_limite: document.getElementById('tareaFechaLimite').value,
        id_proyecto: proyectoId
    };

    if (!payload.titulo.trim() || !payload.id_usuario || !payload.area_asignada || !payload.fecha_limite) {
        alert('Por favor, completa todos los campos requeridos.');
        return;
    }
    
    const url = id ? `?c=Tarea&a=actualizar` : `?c=Tarea&a=guardar`;
    if (id) payload.id_tarea = id;

    fetch(url, { method: 'POST', headers: AJAX_HEADERS, body: JSON.stringify(payload) })
    .then(r => r.json())
    .then(res => {
        if (res.exito) {
            closeModal('modalTarea');
            abrirExito(res.mensaje || 'Operación exitosa');
            if (proyectoId) {
                cargarTareas(proyectoId);
                cargarEstadisticas(proyectoId);
            }
        } else {
            alert(res.mensaje || 'Error en la operación');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error fatal al enviar datos. Revisa la consola.');
    });
}

/**
 * Muestra el modal de confirmación para eliminar una tarea.
 */
function confirmarEliminar(id, proyectoId) {
    tareaAEliminar = { id, proyectoId };
    const modalEl = document.getElementById('modalConfirmar');
    if (!modalEl) return;
    openModal('modalConfirmar');
}

/**
 * Ejecuta la eliminación de la tarea tras la confirmación.
 */
function ejecutarEliminacion() {
    if (!tareaAEliminar) return;
    const { id, proyectoId } = tareaAEliminar;

    fetch(`?c=Tarea&a=eliminar`, { method: 'POST', headers: AJAX_HEADERS, body: JSON.stringify({ id_tarea: id }) })
    .then(r => r.json())
    .then(res => {
        closeModal('modalConfirmar');
        if (res.exito) {
            abrirExito(res.mensaje || 'Tarea eliminada');
            if (proyectoId) {
                cargarTareas(proyectoId);
                cargarEstadisticas(proyectoId);
            }
        } else {
            alert(res.mensaje || 'Error al eliminar');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error fatal al eliminar. Revisa la consola.');
    })
    .finally(() => { tareaAEliminar = null; });
}

/**
 * Muestra un modal de éxito temporal.
 */
function abrirExito(mensaje) {
    const modalEl = document.getElementById('modalExito');
    if (!modalEl) return;
    document.getElementById('exitoMensaje').textContent = mensaje;
    openModal('modalExito');
    setTimeout(() => closeModal('modalExito'), 1000);
}

/**
 * Filtra las filas de la tabla de proyectos localmente.
 */
function filtrarTablaProyectos(e) {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('#proyectosTableBody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/**
 * Filtra las filas de la tabla de tareas localmente.
 */
function filtrarTablaTareas(e) {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('#tareasTableBody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/**
 * Utilidad para escapar HTML y prevenir XSS.
 */
function escapeHtml(str) {
    if (!str) return '';
    const p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
}

// ------------------------
// Interacción de tarjetas: abrir modal con filtros/resultado
// ------------------------
document.addEventListener('DOMContentLoaded', function() {
    // Hacer clicables las tarjetas de estadística (si existen)
    const estadIds = [ 'statTotal', 'statCompletada', 'statJustificaciones', 'statVencidas' ];
    estadIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.style.cursor = 'pointer';
            el.addEventListener('click', () => abrirModalFiltroPorTipo(id));
        }
    });

    // Formulario de filtro dentro del modal
    const formFiltro = document.getElementById('formFiltroTareas');
    if (formFiltro) {
        formFiltro.addEventListener('submit', function(e) {
            e.preventDefault();
            // Ejecutar búsqueda con los valores del formulario
            const tipo = formFiltro.dataset.tipo || 'total';
            const filtros = recogerFiltrosModal();
            cargarTareasFiltro(filtros, tipo);
        });
    }
});

function abrirModalFiltroPorTipo(statId) {
    const tipoMap = {
        'statTotal': 'total',
        'statCompletada': 'completada',
        'statJustificaciones': 'justificaciones',
        'statVencidas': 'vencidas'
    };
    const tipo = tipoMap[statId] || 'total';

    // Reset form
    const form = document.getElementById('formFiltroTareas');
    if (form) {
        form.reset();
        form.dataset.tipo = tipo;
    }

    // Abrir modal
    const modalEl = document.getElementById('modalFiltroTareas');
    if (!modalEl) return;
    openModal('modalFiltroTareas');

    // Ejecutar búsqueda inicial dependiendo del tipo
    const filtrosIniciales = {};
    if (tipo === 'completada') filtrosIniciales.estado = 'Completado';
    // Para vencidas haremos filtrado en cliente según fecha_limite

    cargarTareasFiltro(filtrosIniciales, tipo);
}

function recogerFiltrosModal() {
    return {
        titulo_like: document.getElementById('filtroTitulo')?.value || '',
        descripcion_like: document.getElementById('filtroDescripcion')?.value || '',
        fecha_creacion_from: document.getElementById('filtroFechaCreacionFrom')?.value || '',
        fecha_creacion_to: document.getElementById('filtroFechaCreacionTo')?.value || '',
        fecha_limite_from: document.getElementById('filtroFechaLimiteFrom')?.value || '',
        fecha_limite_to: document.getElementById('filtroFechaLimiteTo')?.value || ''
    };
}

function cargarTareasFiltro(filtros = {}, tipo = 'total') {
    const proyectoId = document.getElementById('tareaProyectoId')?.value;
    if (!proyectoId) {
        document.getElementById('resultadoFiltroTareas').innerHTML = '<div class="text-danger">Proyecto no seleccionado.</div>';
        return;
    }

    // Construir querystring
    const params = new URLSearchParams();
    params.append('proyecto_id', proyectoId);
    if (filtros.titulo_like) params.append('titulo_like', filtros.titulo_like);
    if (filtros.descripcion_like) params.append('descripcion_like', filtros.descripcion_like);
    if (filtros.fecha_creacion_from) params.append('fecha_creacion_from', filtros.fecha_creacion_from);
    if (filtros.fecha_creacion_to) params.append('fecha_creacion_to', filtros.fecha_creacion_to);
    if (filtros.fecha_limite_from) params.append('fecha_limite_from', filtros.fecha_limite_from);
    if (filtros.fecha_limite_to) params.append('fecha_limite_to', filtros.fecha_limite_to);
    if (tipo === 'completada') params.append('estado', 'Completado');

    const url = `?c=Tarea&a=listar&${params.toString()}`;
    document.getElementById('resultadoFiltroTareas').innerHTML = '<div class="text-center py-4">Cargando tareas...</div>';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            const tareas = res.tareas || res.datos?.tareas || [];

            let filtradas = tareas;
            if (tipo === 'vencidas') {
                const hoy = new Date();
                filtradas = tareas.filter(t => {
                    if (!t.fecha_limite) return false;
                    const fechaLim = new Date(t.fecha_limite.split(' ')[0]);
                    const estado = (t.estado || '').toLowerCase();
                    return fechaLim < hoy && estado !== 'completado';
                });
            }

            renderizarResultadoFiltroTareas(filtradas);
        })
        .catch(err => {
            console.error('[cargarTareasFiltro] Error:', err);
            document.getElementById('resultadoFiltroTareas').innerHTML = '<div class="text-danger">Error al cargar tareas.</div>';
        });
}

function renderizarResultadoFiltroTareas(tareas) {
    const cont = document.getElementById('resultadoFiltroTareas');
    if (!cont) return;
    if (!tareas || tareas.length === 0) {
        cont.innerHTML = '<div class="text-center py-4 text-muted">No se encontraron tareas con los filtros aplicados.</div>';
        return;
    }

    let html = '<div class="list-group">';
    tareas.forEach(t => {
        const titulo = escapeHtml(t.titulo || t.nombre || '');
        const fechaCreacion = escapeHtml((t.fecha_creacion || '').split(' ')[0] || '');
        const fechaLimite = escapeHtml((t.fecha_limite || '').split(' ')[0] || '');
        const descripcion = escapeHtml(t.descripcion || '');

        html += `
            <div class="list-group-item">
                <h5 class="mb-1">${titulo}</h5>
                <p class="mb-1 small text-muted">Creación: ${fechaCreacion} &nbsp; | &nbsp; Fecha límite: ${fechaLimite}</p>
                <p class="mb-0">${descripcion}</p>
            </div>
        `;
    });
    html += '</div>';
    cont.innerHTML = html;
}

document.addEventListener("DOMContentLoaded", () => {
    cargarProyectosExportar();
});

function cargarProyectosExportar() {
    fetch("?c=Proyecto&a=listarAjax")
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById("exportarTableBody");
            tbody.innerHTML = "";

            data.datos.forEach(p => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${p.nombre}</td>
                    <td>${p.herramienta ?? "Sin dato"}</td>
                    <td>${p.progreso}%</td>
                    <td>${p.estado}</td>
                `;

                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error("Error cargando exportación:", err));
}