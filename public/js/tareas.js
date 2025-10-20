// public/js/tareas.js
// Script para cargar y mostrar proyectos en la página de tareas del administrador

document.addEventListener('DOMContentLoaded', function() {
    cargarProyectos();
});

function cargarProyectos() {
    const tbody = document.getElementById('proyectosTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="6" class="text-center">Cargando proyectos...</td></tr>`;

    fetch('?c=Proyecto&a=listarAjax')
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                tbody.innerHTML = '';
                data.forEach(proyecto => {
                    const nombreEnc = encodeURIComponent(proyecto.nombre || '');
                    const descEnc = encodeURIComponent(proyecto.descripcion || '');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${proyecto.id_proyecto}</td>
                        <td>${proyecto.nombre}</td>
                        <td>${proyecto.descripcion || ''}</td>
                        <td>${proyecto.estado || ''}</td>
                        <td>${proyecto.fecha_inicio || ''}</td>
                        <td><button class="btn btn-sm btn-primary" onclick="seleccionarProyecto(${proyecto.id_proyecto}, decodeURIComponent('${nombreEnc}'), decodeURIComponent('${descEnc}'))">Ver tareas</button></td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">No hay proyectos disponibles.</td></tr>`;
            }
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error al cargar proyectos.</td></tr>`;
        });
}

function seleccionarProyecto(id, nombre, descripcion) {
    // Mostrar la vista de tareas y ocultar la de proyectos
    document.getElementById('vistaProyectos').style.display = 'none';
    document.getElementById('vistaTareas').style.display = 'block';
    // Mostrar info del proyecto seleccionado
    const info = document.getElementById('proyectoInfo');
    if (info) {
        info.classList.add('active');
        info.querySelector('h3').textContent = nombre;
        info.querySelector('p').textContent = descripcion;
    }
    // Aquí puedes llamar a otra función para cargar las tareas del proyecto seleccionado
    // cargarTareas(id);
}

function volverAProyectos() {
    document.getElementById('vistaTareas').style.display = 'none';
    document.getElementById('vistaProyectos').style.display = 'block';
}

// Puedes agregar aquí la función cargarTareas(id) para mostrar las tareas del proyecto seleccionado.