document.addEventListener('DOMContentLoaded', () => {
    // --- Elementos del DOM ---
    const modalOverlay = document.getElementById('modal-overlay');

    // Modales
    const createModal = document.getElementById('create-modal');
    const viewModal = document.getElementById('view-modal');
    const editModal = document.getElementById('edit-modal');
    const deleteModal = document.getElementById('delete-modal');
    const successModal = document.getElementById('success-modal');
    const errorModal = document.getElementById('error-modal');
    const reportModal = document.getElementById('report-modal');

    // Botones para abrir modales
    const addProjectBtn = document.getElementById('add-project-btn');
    const reportBtn = document.getElementById('report-btn');

    // Botones para cerrar modales
    const closeModalBtns = document.querySelectorAll('.close-modal-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete');

    // --- Funciones para controlar modales ---

    // Función genérica para abrir un modal
    const openModal = (modal) => {
        modalOverlay.classList.add('active');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    // Función genérica para cerrar todos los modales
    const closeModal = () => {
        modalOverlay.classList.remove('active');
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    };

    // --- Asignación de Eventos ---

    // Mapeo de estado a porcentaje de progreso
    const estadoProgresoMap = {
        'Pendiente': 0,
        'Investigación': 15,
        'Diseño': 30,
        'En Desarrollo': 60,
        'Implementación': 85,
        'Completado': 100,
        'Vencido': 0
    };

    // Actualizar progreso automáticamente cuando cambia el estado en el modal de edición
    const editStatusSelect = document.getElementById('edit-status');
    const editProgressInput = document.getElementById('edit-progress');
    
    if (editStatusSelect && editProgressInput) {
        editStatusSelect.addEventListener('change', function() {
            const estado = this.value;
            const progreso = estadoProgresoMap[estado] || 0;
            editProgressInput.value = progreso;
        });
    }

    // Abrir el modal de "Crear Proyecto"
    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', () => {
            console.log('✅ Click detectado en Nuevo Proyecto');
            console.log('modalOverlay:', modalOverlay);
            console.log('createModal:', createModal);
            openModal(createModal);
            console.log('Modal classes:', createModal.classList);
            console.log('Overlay classes:', modalOverlay.classList);
        });
    } else {
        console.error('❌ No se encontró el botón addProjectBtn');
    }

    // Abrir el modal de "Reporte Rápido"
    if (reportBtn) {
        reportBtn.addEventListener('click', () => {
            calcularEstadisticas();
            openModal(reportModal);
        });
    }

    // Gestionar clics en los íconos de acción de la tabla usando delegación de eventos
    const projectsTableBody = document.getElementById('projects-tbody');
    if (projectsTableBody) {
        projectsTableBody.addEventListener('click', (e) => {
            // Buscar el botón padre si se hizo clic en el icono o botón
            const button = e.target.closest('.action-icon');
            
            if (!button) return;
            
            const action = button.dataset.action;
            const proyectoId = button.dataset.id;
            
            if (!action || !proyectoId) return;
            
            switch (action) {
                case 'view':
                    cargarDetallesProyecto(proyectoId);
                    openModal(viewModal);
                    break;
                case 'edit':
                    cargarFormularioEdicion(proyectoId);
                    openModal(editModal);
                    break;
                case 'delete':
                    const confirmDeleteBtn = document.getElementById('confirm-delete');
                    if (confirmDeleteBtn) confirmDeleteBtn.dataset.id = proyectoId;
                    openModal(deleteModal);
                    break;
            }
        });
    }

    // Cerrar modales con botones personalizados
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Cerrar el modal con fondo oscuro
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }

    // Cerrar el modal de confirmación con el botón "Cancelar"
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeModal);
    }

    // Manejar envío del formulario de crear proyecto
    const createForm = document.getElementById('create-form');
    if (createForm) {
        createForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(createForm);
            
            // Enviar datos al servidor
            fetch('?c=Proyecto&a=crear', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal de crear
                    closeModal();
                    
                    // Mostrar modal de éxito
                    openModal(successModal);
                    document.querySelector('#success-modal h2').textContent = '¡Proyecto Creado!';
                    document.querySelector('#success-modal p').textContent = 'Tu proyecto ha sido creado con éxito.';
                    
                    // Recargar la página después de 2 segundos
                    setTimeout(() => {
                        closeModal();
                        location.reload();
                    }, 2000);
                } else {
                    closeModal();
                    openModal(errorModal);
                    document.getElementById('error-title').textContent = 'Error al Crear';
                    document.getElementById('error-message').textContent = data.message || 'No se pudo crear el proyecto. Intenta nuevamente.';
                    setTimeout(() => closeModal(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeModal();
                openModal(errorModal);
                document.getElementById('error-title').textContent = 'Error de Conexión';
                document.getElementById('error-message').textContent = 'No se pudo conectar con el servidor. Verifica tu conexión.';
                setTimeout(() => closeModal(), 3000);
            });
        });
    }

    // Manejar envío del formulario de editar proyecto
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const proyectoId = formData.get('id_proyecto');
            
            // Enviar datos al servidor
            fetch(`?c=Proyecto&a=actualizar&id=${proyectoId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    openModal(successModal);
                    document.querySelector('#success-modal h2').textContent = '¡Proyecto Actualizado!';
                    document.querySelector('#success-modal p').textContent = 'Tu proyecto ha sido actualizado con éxito.';
                    setTimeout(() => {
                        closeModal();
                        location.reload();
                    }, 2000);
                } else {
                    closeModal();
                    openModal(errorModal);
                    document.getElementById('error-title').textContent = 'Error al Actualizar';
                    document.getElementById('error-message').textContent = data.message || 'No se pudo actualizar el proyecto.';
                    setTimeout(() => closeModal(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeModal();
                openModal(errorModal);
                document.getElementById('error-title').textContent = 'Error de Conexión';
                document.getElementById('error-message').textContent = 'No se pudo conectar con el servidor.';
                setTimeout(() => closeModal(), 3000);
            });
        });
    }

    // Confirmar eliminación
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', () => {
            const proyectoId = confirmDeleteBtn.dataset.id;
            
            // Enviar solicitud de eliminación al servidor
            fetch(`?c=Proyecto&a=eliminar&id=${proyectoId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    openModal(successModal);
                    document.querySelector('#success-modal h2').textContent = '¡Proyecto Eliminado!';
                    document.querySelector('#success-modal p').textContent = 'El proyecto ha sido eliminado con éxito.';
                    setTimeout(() => {
                        closeModal();
                        location.reload();
                    }, 2000);
                } else {
                    closeModal();
                    openModal(errorModal);
                    document.getElementById('error-title').textContent = 'Error al Eliminar';
                    document.getElementById('error-message').textContent = data.message || 'No se pudo eliminar el proyecto.';
                    setTimeout(() => closeModal(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeModal();
                openModal(errorModal);
                document.getElementById('error-title').textContent = 'Error de Conexión';
                document.getElementById('error-message').textContent = 'No se pudo conectar con el servidor.';
                setTimeout(() => closeModal(), 3000);
            });
        });
    }

    // Función para cargar detalles del proyecto en el modal de ver
    function cargarDetallesProyecto(id) {
        fetch(`?c=Proyecto&a=obtenerDetalles&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const proyecto = data.proyecto;
                    
                    // Llenar campos básicos
                    document.getElementById('view-name').textContent = proyecto.nombre || '-';
                    document.getElementById('view-cliente').textContent = proyecto.cliente || 'No especificado';
                    document.getElementById('view-category').textContent = proyecto.categoria || 'Sin categoría';
                    document.getElementById('view-status').textContent = proyecto.estado || '-';
                    document.getElementById('view-start').textContent = proyecto.fecha_inicio || '-';
                    document.getElementById('view-end').textContent = proyecto.fecha_fin || 'Sin fecha';
                    document.getElementById('view-recursos').textContent = proyecto.recursos || 'No especificado';
                    document.getElementById('view-tecnologias').textContent = proyecto.tecnologias || 'No especificado';
                    document.getElementById('view-description').textContent = proyecto.descripcion || 'Sin descripción';
                    
                    // Actualizar barra de progreso
                    const progreso = proyecto.porcentaje_avance || 0;
                    document.getElementById('view-progress-bar').style.width = progreso + '%';
                    document.getElementById('view-progress-text').textContent = progreso + '%';
                    
                    // Generar lista de miembros del equipo (ejemplo - adaptar según tu modelo de datos)
                    const teamMembersList = document.getElementById('team-members-list');
                    teamMembersList.innerHTML = '';
                    
                    // Ejemplo: crear miembros de equipo de muestra
                    const teamMembers = [
                        { nombre: 'Juan Pérez', rol: 'Frontend Developer', progreso: 75 },
                        { nombre: 'María García', rol: 'Backend Developer', progreso: 60 },
                        { nombre: 'Carlos López', rol: 'UI/UX Designer', progreso: 90 }
                    ];
                    
                    teamMembers.forEach(member => {
                        const memberDiv = document.createElement('div');
                        memberDiv.className = 'team-member';
                        memberDiv.innerHTML = `
                            <div class="team-member-header">
                                <span class="team-member-name">${member.nombre}</span>
                                <span class="team-member-role">${member.rol}</span>
                            </div>
                            <div class="team-member-progress">
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: ${member.progreso}%;"></div>
                                    <span>${member.progreso}%</span>
                                </div>
                            </div>
                        `;
                        teamMembersList.appendChild(memberDiv);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Función para cargar datos del proyecto en el formulario de edición
    function cargarFormularioEdicion(id) {
        fetch(`?c=Proyecto&a=obtenerDetalles&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const proyecto = data.proyecto;
                    document.getElementById('edit-id').value = proyecto.id_proyecto;
                    document.getElementById('edit-name').value = proyecto.nombre;
                    document.getElementById('edit-category').value = proyecto.categoria || '';
                    document.getElementById('edit-start-date').value = proyecto.fecha_inicio;
                    document.getElementById('edit-end-date').value = proyecto.fecha_fin || '';
                    document.getElementById('edit-cliente').value = proyecto.cliente || '';
                    document.getElementById('edit-recursos').value = proyecto.recursos || '';
                    document.getElementById('edit-tecnologias').value = proyecto.tecnologias || '';
                    document.getElementById('edit-description').value = proyecto.descripcion || '';
                    document.getElementById('edit-status').value = proyecto.estado || 'Pendiente';
                    document.getElementById('edit-progress').value = proyecto.porcentaje_avance || 0;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // --- Funcionalidad de Filtros ---
    const filterCategory = document.getElementById('filter-category');
    const filterProgress = document.getElementById('filter-progress');
    const searchInput = document.getElementById('search-input');
    const tbody = document.getElementById('projects-tbody');

    function filterProjects() {
        const categoryValue = filterCategory ? filterCategory.value.toLowerCase() : '';
        const progressValue = filterProgress ? filterProgress.value.toLowerCase() : '';
        const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
        
        const rows = tbody ? tbody.querySelectorAll('tr') : [];
        
        rows.forEach(row => {
            const category = row.dataset.category ? row.dataset.category.toLowerCase() : '';
            const status = row.dataset.status ? row.dataset.status.toLowerCase() : '';
            const projectName = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
            
            const matchesCategory = !categoryValue || category.includes(categoryValue);
            const matchesProgress = !progressValue || status.includes(progressValue);
            const matchesSearch = !searchValue || projectName.includes(searchValue);
            
            if (matchesCategory && matchesProgress && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Agregar event listeners a los filtros
    if (filterCategory) {
        filterCategory.addEventListener('change', filterProjects);
    }
    
    if (filterProgress) {
        filterProgress.addEventListener('change', filterProjects);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterProjects);
    }

    // --- Función para Calcular Estadísticas del Reporte ---
    function calcularEstadisticas() {
        const tbody = document.getElementById('projects-tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');
        let total = 0;
        let softwareGestion = 0;
        let tiendaVirtual = 0;
        let landingPage = 0;
        let portalCorporativo = 0;
        let plataformaSaaS = 0;

        rows.forEach(row => {
            // Saltar la fila de "No hay proyectos"
            if (row.cells.length < 7) return;
            
            const categoria = row.dataset.category ? row.dataset.category.toLowerCase() : '';
            
            total++;
            
            if (categoria.includes('software de gestión') || categoria.includes('software de gestion')) {
                softwareGestion++;
            } else if (categoria.includes('tienda virtual') || categoria.includes('e-commerce')) {
                tiendaVirtual++;
            } else if (categoria.includes('landing page') || categoria.includes('micrositio')) {
                landingPage++;
            } else if (categoria.includes('portal corporativo') || categoria.includes('institucional')) {
                portalCorporativo++;
            } else if (categoria.includes('plataforma saas') || categoria.includes('interactiva')) {
                plataformaSaaS++;
            }
        });

        // Actualizar los números en el modal
        const statTotal = document.getElementById('stat-total');
        const statFrontend = document.getElementById('stat-frontend');
        const statBackend = document.getElementById('stat-backend');
        const statInfra = document.getElementById('stat-infraestructura');

        if (statTotal) statTotal.textContent = total;
        if (statFrontend) statFrontend.textContent = softwareGestion;
        if (statBackend) statBackend.textContent = tiendaVirtual;
        if (statInfra) statInfra.textContent = landingPage;
    }

    // --- Toggle Sidebar de Resumen (Removido) ---
    // Ya no se usa el panel lateral fijo
});
