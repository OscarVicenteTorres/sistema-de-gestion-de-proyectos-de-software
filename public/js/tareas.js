// public/js/tareas.js
// Script para cargar y manejar CRUD de tareas en la vista de administrador

document.addEventListener('DOMContentLoaded', function() {
    cargarProyectos();
});

const AJAX_HEADERS = {
    'X-Requested-With': 'XMLHttpRequest'
};

function cargarProyectos() {
    const tbody = document.getElementById('proyectosTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="4" class="text-center">Cargando proyectos...</td></tr>`;

    fetch('?c=Proyecto&a=listarAjax', { headers: AJAX_HEADERS })
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                tbody.innerHTML = '';
                data.forEach(proyecto => {
                        // Crear fila con contenido: Proyecto	Categoría	Progreso
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${proyecto.id_proyecto ?? ''}</td>
                            <td>${escapeHtml(proyecto.nombre || '')}</td>
                            <td>${escapeHtml(proyecto.categoria ?? proyecto.area ?? '')}</td>
                            <td>
                                <div class="progress" style="height:12px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: ${Number(proyecto.porcentaje_avance ?? proyecto.progreso ?? 0)}%;" aria-valuenow="${Number(proyecto.porcentaje_avance ?? proyecto.progreso ?? 0)}" aria-valuemin="0" aria-valuemax="100">${Number(proyecto.porcentaje_avance ?? proyecto.progreso ?? 0)}%</div>
                                </div>
                            </td>
                        `;
                        // Indicador visual de que la fila es interactiva
                        tr.style.cursor = 'pointer';
                        // Al hacer click en la fila, seleccionamos el proyecto (sin usar inline onclick ni encode/decode)
                        tr.addEventListener('click', () => {
                            seleccionarProyecto(proyecto.id_proyecto, proyecto.nombre || '', proyecto.descripcion || '');
                        });
                        tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">No hay proyectos disponibles.</td></tr>`;
            }
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error al cargar proyectos.</td></tr>`;
        });
}

function seleccionarProyecto(id, nombre, descripcion) {
    // Mostrar la vista de tareas y ocultar la de proyectos
    const vistaProyectos = document.getElementById('vistaProyectos');
    const vistaTareas = document.getElementById('vistaTareas');
    if (vistaProyectos) vistaProyectos.style.display = 'none';
    if (vistaTareas) vistaTareas.style.display = 'block';

    // Mostrar info del proyecto seleccionado
    const info = document.getElementById('proyectoInfo');
    if (info) {
        info.classList.add('active');
        info.querySelector('h3').textContent = nombre;
        info.querySelector('p').textContent = descripcion;
    }

    // Guardar id de proyecto en el form oculto
    const proyectoInput = document.getElementById('tareaProyectoId');
    if (proyectoInput) proyectoInput.value = id;

    // Cargar las tareas del proyecto seleccionado y poblar usuarios para el formulario
    cargarTareas(id);
    poblarUsuarios();
}

function volverAProyectos() {
    const vistaProyectos = document.getElementById('vistaProyectos');
    const vistaTareas = document.getElementById('vistaTareas');
    if (vistaTareas) vistaTareas.style.display = 'none';
    if (vistaProyectos) vistaProyectos.style.display = 'block';
}

// Cargar tareas de un proyecto y renderizarlas en la tabla
function cargarTareas(proyectoId) {
    const tbody = document.getElementById('tareasTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="10" class="text-center">Cargando tareas...</td></tr>`;

    // Usamos el endpoint listar con filtro proyecto_id
    fetch(`?c=Tarea&a=listar&proyecto_id=${encodeURIComponent(proyectoId)}`, { headers: AJAX_HEADERS })
        .then(r => r.json())
        .then(data => {
            // data puede venir envuelto por BaseApiController: { exito: true, datos: { tareas: [...] } }
            let tareas = [];
            if (data) {
                if (data.datos && Array.isArray(data.datos.tareas)) tareas = data.datos.tareas;
                else if (Array.isArray(data)) tareas = data;
                else if (Array.isArray(data.tareas)) tareas = data.tareas;
                else if (Array.isArray(data.datos)) tareas = data.datos;
            }

            if (tareas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No hay tareas para este proyecto.</td></tr>`;
                return;
            }

            tbody.innerHTML = '';
                tareas.forEach(t => {
                const tr = document.createElement('tr');
                const tareaId = t.id_tarea ?? t.id ?? '';
                tr.dataset.id = tareaId;
                const porcentaje = (t.porcentaje_avance ?? t.porcentaje ?? 0);

                // Project progress (visual)
                const proyectoProg = Number(t.proyecto_progreso ?? t.proyecto_porcentaje_avance ?? 0) || 0;

                tr.innerHTML = `
                    <td>${escapeHtml(t.proyecto_nombre ?? t.nombre_proyecto ?? '')}</td>
                    <td>${escapeHtml(t.proyecto_area ?? '')}</td>
                    <td>
                        <div class="progress" style="height:14px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: ${proyectoProg}%;" aria-valuenow="${proyectoProg}" aria-valuemin="0" aria-valuemax="100">${proyectoProg}%</div>
                        </div>
                    </td>

                    <td>${tareaId}</td>
                    <td>${escapeHtml(t.titulo ?? t.nombre ?? '')}</td>
                    <td>${escapeHtml(((t.usuario_nombre ?? '') + ' ' + (t.usuario_apellido ?? '')).trim())}</td>
                    <td>${escapeHtml(t.area_asignada ?? '')}</td>
                    <td>
                        <div class="progress" style="height:18px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: ${porcentaje}%;" aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">${porcentaje}%</div>
                        </div>
                    </td>
                    <td>${escapeHtml(t.estado ?? '')}</td>
                    <td>${escapeHtml(t.fecha_limite ?? '')} <div class="btn-group ms-2" role="group">
                        <button class="btn btn-sm btn-outline-secondary btn-edit" type="button"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" type="button"><i class="fas fa-trash"></i></button>
                    </div></td>
                `;

                // Make the entire row clickable to edit the task
                tr.style.cursor = 'pointer';
                tr.addEventListener('click', () => {
                    abrirEditarTarea(tareaId, proyectoId);
                });

                // After appending, attach handlers to buttons to prevent event bubbling
                tbody.appendChild(tr);

                const editBtn = tr.querySelector('.btn-edit');
                if (editBtn) {
                    editBtn.addEventListener('click', (e) => { e.stopPropagation(); abrirEditarTarea(tareaId, proyectoId); });
                }
                const delBtn = tr.querySelector('.btn-delete');
                if (delBtn) {
                    delBtn.addEventListener('click', (e) => { e.stopPropagation(); confirmarEliminar(tareaId, proyectoId); });
                }
            });
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al cargar tareas.</td></tr>`;
        });
}


// Poblar select de usuarios (desarrolladores) para asignar tareas
function poblarUsuarios() {
    const select = document.getElementById('tareaUsuario');
    if (!select) return;
    select.innerHTML = `<option value="">Cargando...</option>`;

    fetch('?c=Usuario&a=listar', { headers: AJAX_HEADERS })
        .then(r => r.json())
        .then(data => {
            let usuarios = [];
            if (data && Array.isArray(data.usuarios)) usuarios = data.usuarios;
            else if (Array.isArray(data)) usuarios = data;

            if (usuarios.length === 0) {
                select.innerHTML = `<option value="">No hay usuarios</option>`;
                return;
            }

            let html = `<option value="">Selecciona un desarrollador</option>`;
            usuarios.forEach(u => {
                const id = u.id_usuario ?? u.id ?? u.idUsuario ?? '';
                const nombre = ((u.nombre ?? '') + ' ' + (u.apellido ?? '')).trim();
                html += `<option value="${id}">${escapeHtml(nombre)}</option>`;
            });
            select.innerHTML = html;
        })
        .catch(() => {
            select.innerHTML = `<option value="">Error al cargar usuarios</option>`;
        });
}


// Abrir modal para crear nueva tarea
document.getElementById('btnNuevaTarea')?.addEventListener('click', function() {
    abrirModalTarea('crear');
});

// Abrir modal para modificar tarea (consulta al backend)
function abrirEditarTarea(tareaId, proyectoId) {
    if (!tareaId) return;
    fetch(`?c=Tarea&a=obtenerPorId&id=${encodeURIComponent(tareaId)}`, { headers: AJAX_HEADERS })
        .then(r => r.json())
        .then(data => {
            let tarea = null;
            if (data && data.datos) tarea = data.datos;
            else if (data && data.tarea) tarea = data.tarea;
            else tarea = data;

            if (!tarea) {
                alert('No se pudo obtener la tarea');
                return;
            }

            abrirModalTarea('editar', tarea, proyectoId);
        })
        .catch(err => {
            console.error(err);
            alert('Error al obtener la tarea');
        });
}

// Abrir modal con modo ('crear'|'editar') y tarea opcional
function abrirModalTarea(modo, tarea = null, proyectoId = null) {
    poblarUsuarios();
    const modal = document.getElementById('modalTarea');
    const title = document.getElementById('modalTareaTitle');
    const form = document.getElementById('formTarea');
    if (!modal || !form) return;

    // limpiar formulario
    form.reset();
    if (document.getElementById('tareaId')) document.getElementById('tareaId').value = '';
    if (proyectoId && document.getElementById('tareaProyectoId')) document.getElementById('tareaProyectoId').value = proyectoId;

    if (modo === 'crear') {
        if (title) title.textContent = 'Crear Tarea';
        const btn = document.getElementById('btnGuardarTarea'); if (btn) btn.textContent = 'Crear Tarea';
    } else {
        if (title) title.textContent = 'Editar Tarea';
        const btn = document.getElementById('btnGuardarTarea'); if (btn) btn.textContent = 'Guardar cambios';
        if (tarea) {
            if (document.getElementById('tareaId')) document.getElementById('tareaId').value = tarea.id_tarea ?? tarea.id ?? '';
            if (document.getElementById('tareaTitulo')) document.getElementById('tareaTitulo').value = tarea.titulo ?? '';
            if (document.getElementById('tareaDescripcion')) document.getElementById('tareaDescripcion').value = tarea.descripcion ?? '';
            if (document.getElementById('tareaArea')) document.getElementById('tareaArea').value = tarea.area_asignada ?? '';
            if (document.getElementById('tareaFechaInicio')) document.getElementById('tareaFechaInicio').value = tarea.fecha_inicio ? tarea.fecha_inicio.split(' ')[0] : '';
            if (document.getElementById('tareaFechaLimite')) document.getElementById('tareaFechaLimite').value = tarea.fecha_limite ? tarea.fecha_limite.split(' ')[0] : '';
            const usuarioId = tarea.id_usuario ?? tarea.idUsuario ?? '';
            // Esperar un tick para que el select se llene
            setTimeout(() => { try { if (document.getElementById('tareaUsuario')) document.getElementById('tareaUsuario').value = usuarioId; } catch(e){} }, 250);
        }
    }

    modal.style.display = 'block';
}

// Cerrar cualquier modal por id
function cerrarModal(id) {
    const m = document.getElementById(id);
    if (m) m.style.display = 'none';
}

// Manejar envío del formulario (crear o actualizar)
document.getElementById('formTarea')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('tareaId') ? document.getElementById('tareaId').value : '';
    const proyectoId = document.getElementById('tareaProyectoId') ? document.getElementById('tareaProyectoId').value : '';
    const payload = {
        titulo: document.getElementById('tareaTitulo') ? document.getElementById('tareaTitulo').value : '',
        descripcion: document.getElementById('tareaDescripcion') ? document.getElementById('tareaDescripcion').value : '',
        id_usuario: document.getElementById('tareaUsuario') ? document.getElementById('tareaUsuario').value : '',
        area_asignada: document.getElementById('tareaArea') ? document.getElementById('tareaArea').value : '',
        fecha_inicio: document.getElementById('tareaFechaInicio') ? document.getElementById('tareaFechaInicio').value : '',
        fecha_limite: document.getElementById('tareaFechaLimite') ? document.getElementById('tareaFechaLimite').value : '',
        id_proyecto: proyectoId
    };

    // Frontend validation
    const allowedAreas = ['Frontend', 'Backend', 'Infraestructura'];
    if (!payload.titulo || payload.titulo.trim().length < 3) {
        alert('El título de la tarea es requerido y debe tener al menos 3 caracteres');
        return;
    }
    if (!payload.id_usuario) {
        alert('Selecciona un desarrollador para la tarea');
        return;
    }
    if (!allowedAreas.includes(payload.area_asignada)) {
        alert('Selecciona un área válida: Frontend, Backend o Infraestructura');
        return;
    }
    if (!payload.fecha_limite) {
        alert('La fecha límite es requerida');
        return;
    }
    if (!payload.id_proyecto) {
        alert('Proyecto no seleccionado');
        return;
    }

    const url = id ? `?c=Tarea&a=actualizar` : `?c=Tarea&a=guardar`;
    if (id) payload.id_tarea = id;

    // Enviar como JSON (BaseApiController soporta JSON o POST)
    fetch(url, {
        method: 'POST',
        headers: Object.assign({ 'Content-Type': 'application/json' }, AJAX_HEADERS),
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res && res.exito) {
            // cerrar modal, recargar tareas y mostrar exito
            cerrarModal('modalTarea');
            abrirExito(res.mensaje || 'Operación exitosa');
            if (proyectoId) cargarTareas(proyectoId);
        } else {
            alert(res.mensaje || 'Error en la operación');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al enviar datos');
    });
});

// Confirmar eliminación y llamar al endpoint
let tareaAEliminar = null;
function confirmarEliminar(id, proyectoId) {
    tareaAEliminar = { id, proyectoId };
    const modal = document.getElementById('modalConfirmar');
    if (modal) modal.style.display = 'block';
}

document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    if (!tareaAEliminar) return;
    const { id, proyectoId } = tareaAEliminar;
    fetch(`?c=Tarea&a=eliminar`, {
        method: 'POST',
        headers: Object.assign({ 'Content-Type': 'application/json' }, AJAX_HEADERS),
        body: JSON.stringify({ id_tarea: id })
    })
    .then(r => r.json())
    .then(res => {
        cerrarModal('modalConfirmar');
        if (res && res.exito) {
            abrirExito(res.mensaje || 'Tarea eliminada');
            if (proyectoId) cargarTareas(proyectoId);
        } else {
            alert(res.mensaje || 'Error al eliminar');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error al eliminar');
    })
    .finally(() => { tareaAEliminar = null; });
});

// Mostrar modal de exito con mensaje
function abrirExito(mensaje) {
    const modal = document.getElementById('modalExito');
    if (!modal) return;
    const titulo = document.getElementById('exitoTitulo');
    const mensajeEl = document.getElementById('exitoMensaje');
    if (mensajeEl) mensajeEl.textContent = mensaje;
    if (titulo) titulo.textContent = '¡Operación Exitosa!';
    modal.style.display = 'block';
    setTimeout(() => { modal.style.display = 'none'; }, 1800);
}

// Utilidad: escapar html simple
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
