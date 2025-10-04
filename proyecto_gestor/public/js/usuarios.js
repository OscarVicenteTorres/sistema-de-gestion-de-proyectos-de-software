// ===== Usuarios Management System =====

// Elementos del DOM
const successModal = document.getElementById('success-modal');
const successModalOverlay = document.getElementById('success-modal-overlay');
const errorModal = document.getElementById('error-modal');
const errorModalOverlay = document.getElementById('error-modal-overlay');
const createModal = document.getElementById('modal-create');
const createModalOverlay = document.getElementById('modal-create-overlay');
const viewModal = document.getElementById('modal-view');
const viewModalOverlay = document.getElementById('modal-view-overlay');
const confirmModal = document.getElementById('modal-confirm');
const confirmModalOverlay = document.getElementById('modal-confirm-overlay');

// Variables globales
let currentUserId = null;
let currentAction = null;

// ===== Funciones de Modal =====
function openModal(modalElement, overlayElement) {
    if (modalElement && overlayElement) {
        overlayElement.classList.add('active');
        modalElement.classList.add('active');
    }
}

function closeModal(modalElement, overlayElement) {
    if (modalElement && overlayElement) {
        overlayElement.classList.remove('active');
        modalElement.classList.remove('active');
    }
}

function closeAllModals() {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
    const modalContents = document.querySelectorAll('.modal');
    modalContents.forEach(modal => {
        modal.classList.remove('active');
    });
}

// Cerrar modal al hacer clic en el overlay
[successModalOverlay, errorModalOverlay, createModalOverlay, viewModalOverlay, confirmModalOverlay].forEach(overlay => {
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeAllModals();
            }
        });
    }
});

// ===== Funciones de Modal Específicas =====
function openCreateModal() {
    openModal(createModal, createModalOverlay);
}

function openViewModal(userId) {
    fetch(`?c=Usuario&a=obtenerDetalles&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.usuario;
                document.getElementById('view-nombre').textContent = `${user.nombre} ${user.apellido}`;
                document.getElementById('view-documento').textContent = user.documento || '-';
                document.getElementById('view-correo').textContent = user.correo || '-';
                document.getElementById('view-telefono').textContent = user.telefono || '-';
                document.getElementById('view-area').textContent = user.area_trabajo || '-';
                document.getElementById('view-contrasena').textContent = user.contrasena_visible || '********';
                document.getElementById('view-tecnologias').textContent = user.tecnologias || 'No especificado';
                
                openModal(viewModal, viewModalOverlay);
            } else {
                showError('Error al Cargar', data.message || 'No se pudieron cargar los detalles del usuario.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error de Conexión', 'No se pudo conectar con el servidor.');
        });
}

function openConfirmModal(userId, action, title, message) {
    currentUserId = userId;
    currentAction = action;
    
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').innerHTML = message;
    
    openModal(confirmModal, confirmModalOverlay);
}

function showSuccess(title, message) {
    document.getElementById('success-title').textContent = title;
    document.getElementById('success-message').textContent = message;
    openModal(successModal, successModalOverlay);
    setTimeout(() => {
        closeModal(successModal, successModalOverlay);
        location.reload();
    }, 2000);
}

function showError(title, message) {
    document.getElementById('error-title').textContent = title;
    document.getElementById('error-message').textContent = message;
    openModal(errorModal, errorModalOverlay);
    setTimeout(() => {
        closeModal(errorModal, errorModalOverlay);
    }, 3000);
}

// ===== Cargar Usuarios =====
function cargarUsuarios() {
    fetch('?c=Usuario&a=listar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const activos = data.usuarios.filter(u => u.activo == 1);
                const inactivos = data.usuarios.filter(u => u.activo == 0);
                
                // Actualizar estadísticas
                document.getElementById('total-usuarios').textContent = data.usuarios.length;
                document.getElementById('usuarios-activos').textContent = activos.length;
                document.getElementById('usuarios-bloqueados').textContent = inactivos.length;
                
                // Renderizar listas
                renderUsuarios(activos, 'usuarios-activos-list', true);
                renderUsuarios(inactivos, 'usuarios-inactivos-list', false);
            } else {
                console.error('Error al cargar usuarios:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error de Carga', 'No se pudieron cargar los usuarios.');
        });
}

function renderUsuarios(usuarios, containerId, esActivo) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    if (usuarios.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No hay usuarios</p>';
        return;
    }
    
    usuarios.forEach(user => {
        const userCard = document.createElement('div');
        userCard.className = 'user-card';
        
        userCard.innerHTML = `
            <div class="user-info">
                <h3>${user.nombre} ${user.apellido}</h3>
                <p>${user.area_trabajo || 'Sin área'} - ${user.tecnologias || 'Sin tecnologías'}</p>
            </div>
            <div class="user-actions">
                <button class="btn-view" onclick="openViewModal(${user.id})" title="Ver detalles">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                ${esActivo ? `
                    <button class="btn-activate" onclick="openConfirmModal(${user.id}, 'activar', '¿Activar Usuario?', 'El usuario podrá acceder al sistema.')" title="Activar usuario" style="display: none;">
                        <i class="fa-solid fa-user-check"></i>
                    </button>
                    <button class="btn-block" onclick="openConfirmModal(${user.id}, 'bloquear', '¿Cuenta Bloqueada!', 'La cuenta ha sido bloqueada con éxito.')" title="Bloquear usuario">
                        <i class="fa-solid fa-ban"></i>
                    </button>
                ` : `
                    <button class="btn-activate" onclick="openConfirmModal(${user.id}, 'activar', '¡Cuenta Activada!', 'La cuenta ha sido Activada con éxito.')" title="Activar usuario">
                        <i class="fa-solid fa-user-check"></i>
                    </button>
                    <button class="btn-delete" onclick="openConfirmModal(${user.id}, 'eliminar', '¿Realmente quieres eliminar?', 'Esta acción no se puede deshacer.<br>Los datos eliminados se perderán permanentemente.')" title="Eliminar usuario">
                        <i class="fa-solid fa-user-xmark"></i>
                    </button>
                `}
            </div>
        `;
        
        container.appendChild(userCard);
    });
}

// ===== Crear Usuario =====
const formCreateUser = document.getElementById('form-create-user');
if (formCreateUser) {
    formCreateUser.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData(formCreateUser);
        
        // Validar contraseñas
        const password = formData.get('contrasena');
        const confirmPassword = formData.get('confirmar_contrasena');
        
        if (password !== confirmPassword) {
            showError('Error de Validación', 'Las contraseñas no coinciden.');
            return;
        }
        
        // Enviar datos
        fetch('?c=Usuario&a=crear', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAllModals();
                showSuccess('¡Cuenta Creada!', 'La cuenta ha sido creada con éxito.');
            } else {
                showError('Error al Crear', data.message || 'No se pudo crear la cuenta.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error de Conexión', 'No se pudo conectar con el servidor.');
        });
    });
}

// ===== Confirmar Acción =====
const btnConfirmAction = document.getElementById('btn-confirm-action');
if (btnConfirmAction) {
    btnConfirmAction.addEventListener('click', () => {
        if (!currentUserId || !currentAction) {
            closeAllModals();
            return;
        }
        
        let endpoint = '';
        let successTitle = '';
        let successMessage = '';
        
        switch (currentAction) {
            case 'bloquear':
                endpoint = `?c=Usuario&a=bloquear&id=${currentUserId}`;
                successTitle = '¡Cuenta Bloqueada!';
                successMessage = 'La cuenta ha sido bloqueada con éxito.';
                break;
            case 'activar':
                endpoint = `?c=Usuario&a=activar&id=${currentUserId}`;
                successTitle = '¡Cuenta Activada!';
                successMessage = 'La cuenta ha sido Activada con éxito.';
                break;
            case 'eliminar':
                endpoint = `?c=Usuario&a=eliminar&id=${currentUserId}`;
                successTitle = '¡Usuario Eliminado!';
                successMessage = 'El usuario ha sido eliminado permanentemente.';
                break;
        }
        
        fetch(endpoint, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAllModals();
                showSuccess(successTitle, successMessage);
            } else {
                closeAllModals();
                showError('Error en la Operación', data.message || 'No se pudo completar la operación.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeAllModals();
            showError('Error de Conexión', 'No se pudo conectar con el servidor.');
        });
    });
}

// ===== Cargar usuarios al iniciar =====
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});
