// ============================================
// DASHBOARD DESARROLLADOR - JAVASCRIPT
// ============================================

// Variables globales
let tareaActual = null;
let tareaActualNombre = '';

// ================================
// FUNCIONES PARA MODALES PERSONALIZADOS
// ================================
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.classList.add('active');
        overlay.classList.add('active');
    }
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.classList.remove('active');
        overlay.classList.remove('active');
    }
}

function cerrarTodosModales() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('active');
    });
    document.getElementById('modal-overlay')?.classList.remove('active');
}

// Event listeners para cerrar modales
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer clic en la X
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                cerrarModal(modal.id);
            }
        });
    });

    // Cerrar modal al hacer clic en el overlay
    document.getElementById('modal-overlay')?.addEventListener('click', function() {
        cerrarTodosModales();
    });
});

// ================================
// FUNCIÓN: Toggle Panel de Notificaciones
// ================================
function toggleNotifications() {
    const sidebar = document.getElementById('notificationsSidebar');
    const overlay = document.getElementById('overlayNotifications');
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// Función para cerrar notificaciones
function cerrarNotificaciones() {
    const sidebar = document.getElementById('notificationsSidebar');
    const overlay = document.getElementById('overlayNotifications');
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
}

// Cerrar notificaciones cuando se abre cualquier modal de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Obtener todos los modales
    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            cerrarNotificaciones();
        });
    });
});

// ================================
// FUNCIÓN: Seleccionar Fase (modal de fases)
// ================================
function seleccionarFase(porcentaje, nombreFase) {
    // Cerrar modal de fases
    cerrarModal('modalFasesTarea');
    
    // Pequeña pausa para transición suave
    setTimeout(() => {
        // Abrir modal de formulario con el porcentaje pre-seleccionado
        document.getElementById('inputPorcentaje').value = porcentaje;
        document.getElementById('badgePorcentaje').textContent = porcentaje + '%';
        abrirModal('modalAvanceTarea');
    }, 300);
}

// ================================
// EVENT LISTENERS
// ================================

// Actualizar badge de porcentaje
document.getElementById('inputPorcentaje').addEventListener('input', function() {
    document.getElementById('badgePorcentaje').textContent = this.value + '%';
});

// Mostrar/Ocultar campo de nueva fecha
document.getElementById('checkNoPudeCompletar').addEventListener('change', function() {
    const seccion = document.getElementById('seccionNuevaFecha');
    if (this.checked) {
        seccion.style.display = 'block';
        document.getElementById('inputNuevaFecha').required = true;
    } else {
        seccion.style.display = 'none';
        document.getElementById('inputNuevaFecha').required = false;
    }
});

// Botón: Ver Detalles de Tarea
function verDetallesTarea(idTarea, nombreTarea) {
    // Cerrar panel de notificaciones si está abierto
    cerrarNotificaciones();
    
    tareaActual = idTarea;
    document.getElementById('modalDetallesTareaTitle').innerHTML = `<i class="fa-solid fa-info-circle me-2"></i>${nombreTarea}`;

    fetch(`?c=DashboardDesarrollador&a=obtenerNotasTarea&id_tarea=${idTarea}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Obtener información de la tarea actual
                const tareaCard = document.querySelector(`[data-tarea-id="${idTarea}"]`);
                let porcentaje = 0;
                let fechaInicio = '';
                let fechaFin = '';
                let descripcion = '';
                
                if (tareaCard) {
                    const porcentajeText = tareaCard.querySelector('.badge')?.textContent || '0%';
                    porcentaje = parseInt(porcentajeText);
                    
                    // Buscar fechas y descripción en el DOM
                    const tareasProyecto = JSON.parse(tareaCard.closest('.proyecto-card').dataset.tareas || '[]');
                    const tareaData = tareasProyecto.find(t => t.id_tarea == idTarea);
                    if (tareaData) {
                        fechaInicio = tareaData.fecha_inicio || '';
                        fechaFin = tareaData.fecha_fin || '';
                        descripcion = tareaData.descripcion || 'Sin descripción';
                    }
                }
                
                // Determinar estado y color
                let estadoBadge = '';
                let estadoColor = '';
                if (porcentaje === 100) {
                    estadoBadge = 'Completado';
                    estadoColor = '#10B981';
                } else if (porcentaje >= 50) {
                    estadoBadge = 'En Desarrollo';
                    estadoColor = '#3B82F6';
                } else if (porcentaje > 0) {
                    estadoBadge = 'En Progreso';
                    estadoColor = '#FBBF24';
                } else {
                    estadoBadge = 'Pendiente';
                    estadoColor = '#6B7280';
                }
                
                let html = `
                    <!-- Barra de Progreso -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark">${porcentaje}% Completado</span>
                            <span class="badge" style="background-color: ${estadoColor};">${estadoBadge}</span>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${porcentaje}%; background-color: #FBBF24; font-weight: bold;" 
                                 aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                ${porcentaje}%
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fechas -->
                    ${fechaInicio && fechaFin ? `
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">
                                    <i class="fa-solid fa-calendar-day me-1"></i>Inicio
                                </small>
                                <strong>${new Date(fechaInicio).toLocaleDateString('es-ES')}</strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">
                                    <i class="fa-solid fa-calendar-check me-1"></i>Final
                                </small>
                                <strong>${new Date(fechaFin).toLocaleDateString('es-ES')}</strong>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Descripción -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fa-solid fa-file-alt me-2" style="color: var(--dorado);"></i>Descripción
                        </h6>
                        <p class="text-muted mb-0">${descripcion}</p>
                    </div>
                    
                    <hr>
                    
                    <!-- Historial de Notas -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="fa-solid fa-history me-2" style="color: var(--dorado);"></i>Historial de Avances
                        </h6>
                `;

                if (data.notas.length === 0) {
                    html += '<p class="text-muted text-center py-3"><em>No hay reportes de avance aún</em></p>';
                } else {
                    data.notas.forEach(nota => {
                        html += `
                            <div class="card mb-2 border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary">
                                            <i class="fa-solid fa-arrow-right me-1"></i>${nota.porcentaje_anterior}% → ${nota.porcentaje_nuevo}%
                                        </span>
                                        <div class="text-end">
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-user me-1"></i>Desarrollador
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-calendar me-1"></i>${new Date(nota.fecha_envio).toLocaleDateString('es-ES')}
                                            </small>
                                        </div>
                                    </div>
                                    ${nota.nota_desarrollador ? 
                                        `<p class="mb-0 small"><i class="fa-solid fa-quote-left me-2"></i>${nota.nota_desarrollador}</p>` : 
                                        '<p class="mb-0 small text-muted"><em>Sin comentarios</em></p>'}
                                </div>
                            </div>
                        `;
                    });
                }
                
                html += '</div>';

                document.getElementById('contenidoDetallesTarea').innerHTML = html;
                abrirModal('modalDetallesTarea');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('contenidoDetallesTarea').innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle me-2"></i>Error al cargar los datos</div>';
        });
}

// Botón: Abrir Modal de Avance (primero muestra fases)
function abrirModalAvance(idTarea, nombreTarea, porcentajeActual) {
    // Cerrar panel de notificaciones si está abierto
    cerrarNotificaciones();
    
    // Guardar datos de la tarea
    tareaActual = idTarea;
    tareaActualNombre = nombreTarea;
    
    // Preparar el formulario
    document.getElementById('inputIdTarea').value = idTarea;
    document.getElementById('nombreTareaForm').textContent = nombreTarea;
    document.getElementById('inputPorcentaje').value = porcentajeActual;
    document.getElementById('badgePorcentaje').textContent = porcentajeActual + '%';
    document.getElementById('textareaNota').value = '';
    document.getElementById('checkNoPudeCompletar').checked = false;
    document.getElementById('seccionNuevaFecha').style.display = 'none';

    // Mostrar primero el modal de fases
    abrirModal('modalFasesTarea');
}

// Función: Enviar Avance
function enviarAvance() {
    const idTarea = document.getElementById('inputIdTarea').value;
    const porcentajeNuevo = document.getElementById('inputPorcentaje').value;
    const nota = document.getElementById('textareaNota').value;
    const noPudeCompletar = document.getElementById('checkNoPudeCompletar').checked;
    const nuevaFecha = document.getElementById('inputNuevaFecha').value;

    if (noPudeCompletar && !nuevaFecha) {
        alert('⚠️ Debes proponer una nueva fecha de entrega');
        return;
    }

    // Primero guardar el avance
    const formData = new FormData();
    formData.append('id_tarea', idTarea);
    formData.append('porcentaje_nuevo', porcentajeNuevo);
    formData.append('nota_desarrollador', nota);

    fetch('?c=DashboardDesarrollador&a=guardarAvance', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Si marcó "No pude completar", guardar justificación
                if (noPudeCompletar) {
                    const formDataJust = new FormData();
                    formDataJust.append('id_tarea', idTarea);
                    formDataJust.append('motivo', nota);
                    formDataJust.append('nueva_fecha_limite', nuevaFecha);

                    fetch('?c=DashboardDesarrollador&a=guardarJustificacion', {
                        method: 'POST',
                        body: formDataJust
                    })
                        .then(response => response.json())
                        .then(dataJust => {
                            if (dataJust.success) {
                                alert('✅ Avance guardado y justificación enviada al administrador');
                                modalAvanceTarea.hide();
                                location.reload();
                            } else {
                                alert('⚠️ Avance guardado pero hubo un error al enviar la justificación');
                            }
                        });
                } else {
                    alert('✅ Avance guardado exitosamente');
                    modalAvanceTarea.hide();
                    location.reload();
                }
            } else {
                alert('❌ Error: ' + (data.message || 'Error al guardar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al enviar el avance');
        });
}

// Botón: Ver Tareas Completadas
document.getElementById('btnTareasCompletadas').addEventListener('click', function() {
    // Cerrar panel de notificaciones si está abierto
    cerrarNotificaciones();
    
    fetch('?c=DashboardDesarrollador&a=obtenerTareasCompletadas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';

                if (data.tareas_completadas.length === 0) {
                    html = '<div class="alert alert-info text-center py-4"><i class="fa-solid fa-inbox me-2"></i>Aún no hay tareas completadas</div>';
                } else {
                    html = `<div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i><strong>${data.tareas_completadas.length} tarea(s) completada(s)</strong></div>`;
                    
                    data.tareas_completadas.forEach(tarea => {
                        html += `
                            <div class="card mb-3 border-success border-opacity-50">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="card-title fw-bold text-success"><i class="fa-solid fa-circle-check me-1"></i>${tarea.titulo}</h6>
                                            <p class="card-text small text-muted mb-0"><i class="fa-solid fa-folder me-1"></i>${tarea.proyecto_nombre}</p>
                                        </div>
                                        <span class="badge bg-success">100%</span>
                                    </div>
                                    <small class="text-muted d-block mt-2"><i class="fa-solid fa-calendar me-1"></i>Completada: ${new Date(tarea.fecha_limite).toLocaleDateString('es-ES')}</small>
                                    ${tarea.total_notas > 0 ? `<p class="mb-0 mt-2 small text-muted"><i class="fa-solid fa-file-alt me-1"></i>${tarea.total_notas} reporte(s)</p>` : ''}
                                </div>
                            </div>
                        `;
                    });
                }

                document.getElementById('contenidoTareasCompletadas').innerHTML = html;
                abrirModal('modalTareasCompletadas');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('contenidoTareasCompletadas').innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle me-2"></i>Error al cargar los datos</div>';
        });
});

// Cargar notificaciones al abrir el modal (placeholder)
function cargarNotificaciones() {
    let html = '<div class="alert alert-info"><i class="fa-solid fa-info-circle me-2"></i>No hay notificaciones nuevas en este momento</div>';
    document.getElementById('contenidoNotificaciones').innerHTML = html;
}
