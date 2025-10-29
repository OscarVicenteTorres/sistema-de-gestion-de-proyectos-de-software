// JS unificado para la gestión de usuarios
// Maneja modales y peticiones AJAX utilizando la estructura JSON normalizada (exito/mensaje/datos)
(function(){
    'use strict';

    // Helper: normaliza la respuesta del servidor para aceptar ambas formas
    function normalizeResponse(raw) {
        const exito = (raw && (raw.exito !== undefined ? raw.exito : raw.success !== undefined ? raw.success : false));
        const mensaje = raw && (raw.mensaje || raw.message || '') ;
        const datos = raw && (raw.datos || raw.data || null);
        const usuario = raw && (raw.usuario || (datos && datos.usuario) || null);
        const usuarios = raw && (raw.usuarios || (Array.isArray(datos) ? datos : (datos && datos.usuarios) || null));
        return { raw, exito, mensaje, datos, usuario, usuarios };
    }

    // Selector de elementos (guard-safe)
    const $ = id => document.getElementById(id);

    // Modales (basados en la vista)
    const modalOverlay = $('modal-overlay');
    function openModal(id){
        if (!modalOverlay) return;
        modalOverlay.classList.add('active');
        const m = $(id);
        if (m) m.classList.add('active');
    }
    function closeModal(){
        if (!modalOverlay) return;
        modalOverlay.classList.remove('active');
        document.querySelectorAll('.modal').forEach(m=>m.classList.remove('active'));
    }

    // Exponer función para abrir modal de creación (asegura compatibilidad con onclick en la vista)
    window.openCreateModal = function(){ openModal('create-modal'); };

    function showSuccess(title, message){
        const t = $('success-title');
        const m = $('success-message');
        if (t) t.textContent = title;
        if (m) m.textContent = message;
        openModal('success-modal');
        setTimeout(closeModal, 2000);
    }

    function showError(title, message){
        const t = $('error-title');
        const m = $('error-message');
        if (t) t.textContent = title;
        if (m) m.textContent = message;
        openModal('error-modal');
        setTimeout(closeModal, 3000);
    }

    // [CORREGIDO] Util para llamadas fetch con header de AJAX (versión robusta)
    function fetchAjax(url, opts = {}){
        opts.headers = Object.assign({'X-Requested-With': 'XMLHttpRequest'}, opts.headers || {});
        return fetch(url, opts).then(async r => {
            const text = await r.text();
            try {
                return JSON.parse(text);
            } catch (err) {
                // Mostrar modal de error específico para ayudar al debugging
                console.error('Respuesta no es JSON válido:', text);
                try { showError('Error', 'Formato JSON inválido'); } catch(e) { /* ignore */ }
                throw { message: 'Formato JSON inválido', body: text, status: r.status };
            }
        });
    }

    // Obtener detalles y abrir modal de edición
    window.editUser = function(id){
        fetchAjax(`?c=Usuario&a=obtenerDetalles&id=${id}`)
            .then(raw => {
                const res = normalizeResponse(raw);
                if (res.exito){
                    const user = res.usuario || res.datos;
                    if (!user) return showError('Error','Respuesta inválida');
                    $('edit-id').value = user.id_usuario || user.id || '';
                    $('edit-nombres').value = user.nombre || user.nombres || '';
                    $('edit-apellidos').value = user.apellido || user.apellidos || '';
                    $('edit-documento').value = user.documento || '';
                    $('edit-tipo-documento').value = user.tipo_documento || 'DNI';
                    $('edit-correo').value = user.correo || user.email || '';
                    $('edit-telefono').value = user.telefono || '';
                    $('edit-area-trabajo').value = user.area_trabajo || 'Frontend';
                    $('edit-fecha-inicio').value = user.fecha_inicio || '';
                    $('edit-tecnologias').value = user.tecnologias || '';
                    $('edit-rol').value = user.rol || user.rol_nombre || 'Desarrollador';
                    if ($('edit-contrasena')) $('edit-contrasena').value = '';
                    if ($('edit-confirmar-contrasena')) $('edit-confirmar-contrasena').value = '';
                    openModal('edit-modal');
                } else {
                    showError('Error', res.mensaje || 'No se pudieron cargar los datos');
                }
            }).catch(()=> showError('Error','Error de conexión'));
    };

    // Ver usuario
    window.viewUser = function(id){
        fetchAjax(`?c=Usuario&a=obtenerDetalles&id=${id}`)
            .then(raw => {
                const res = normalizeResponse(raw);
                if (res.exito){
                    const user = res.usuario || res.datos;
                    if (!user) return showError('Error','Respuesta inválida');
                    $('view-nombre').textContent = `${user.nombre || user.nombres || ''} ${user.apellido || user.apellidos || ''}`.trim();
                    $('view-documento').textContent = user.documento || 'No especificado';
                    $('view-correo').textContent = user.correo || user.email || 'No especificado';
                    $('view-telefono').textContent = user.telefono || 'No especificado';
                    $('view-area').textContent = user.area_trabajo || 'Frontend';
                    $('view-password').textContent = '••••••••';
                    $('view-tecnologias').textContent = user.tecnologias || 'No especificado';
                    openModal('view-modal');
                } else {
                    showError('Error', res.mensaje || 'No se pudieron cargar los datos');
                }
            }).catch(()=> showError('Error','Error de conexión'));
    };

    // [CORREGIDO] Bloquear/activar/eliminar workflow usando el modal de confirmación existente
    let currentUserId = null;
    let currentAction = null; // 'bloquear' | 'activar' | 'eliminar'

    function openConfirm(action, id){
        currentUserId = id;
        currentAction = action;
        
        // Personalizar título/mensaje según acción
        const titleMap = {
            'bloquear': '¿Realmente quieres bloquear?',
            'activar': '¿Realmente quieres activar?',
            'eliminar': '¿Realmente quieres eliminar?'
        };
        const msgMap = {
            'bloquear': 'Esta acción impedirá que el usuario inicie sesión.',
            'activar': 'El usuario podrá acceder al sistema nuevamente.',
            'eliminar': 'Esta acción no se puede deshacer. Las tareas permanecerán desvinculadas.'
        };
        const title = titleMap[action] || 'Confirmar acción';
        const message = msgMap[action] || '';
        
        // El modal existente usa id 'block-confirmation-modal'
        const modalBodyTitle = document.querySelector('#block-confirmation-modal h2');
        const modalBodyP = document.querySelector('#block-confirmation-modal .modal-body p');
        if (modalBodyTitle) modalBodyTitle.textContent = title;
        if (modalBodyP) modalBodyP.innerHTML = message;
        
        openModal('block-confirmation-modal');
    }

    // [CORREGIDO] Funciones de ventana unificadas
    window.blockUser = function(id){ openConfirm('bloquear', id); };
    window.activateUser = function(id){ openConfirm('activar', id); };
    window.deleteUser = function(id){ openConfirm('eliminar', id); };

    // [CORREGIDO] Mover todos los listeners al DOMContentLoaded
    document.addEventListener('DOMContentLoaded', ()=>{
        
        // Listener para el botón de confirmación genérico
        $('confirm-block-btn')?.addEventListener('click', ()=>{
            if (!currentUserId || !currentAction) return closeModal();
            closeModal();
            let endpoint = '';
            
            // Determinar qué endpoint usar basado en la acción guardada
            switch (currentAction){
                case 'bloquear': endpoint = `?c=Usuario&a=bloquear&id=${currentUserId}`; break;
                case 'activar': endpoint = `?c=Usuario&a=activar&id=${currentUserId}`; break;
                case 'eliminar': endpoint = `?c=Usuario&a=eliminar&id=${currentUserId}`; break;
                default: return;
            }
            
            fetchAjax(endpoint, { method: 'POST' })
                .then(raw => {
                    const res = normalizeResponse(raw);
                    if (res.exito){
                        // Mostrar modal de éxito (reutilizando los que tienes)
                        if (currentAction === 'activar') {
                            openModal('activate-success-modal');
                        } else {
                            // 'bloquear' y 'eliminar' pueden compartir el modal de éxito de bloqueo
                            openModal('block-success-modal'); 
                        }
                        setTimeout(()=>{ closeModal(); location.reload(); }, 1500);
                    } else {
                        showError('Error', res.mensaje || 'No se pudo completar la acción');
                    }
                }).catch(err => {
                    console.error('Error en confirm action:', err);
                    showError('Error','Error de conexión o respuesta inválida');
                });
        });

        // Crear usuario
        const createForm = $('create-form');
        if (createForm){
            createForm.addEventListener('submit', function(e){
                e.preventDefault();
                const fd = new FormData(this);
                if (fd.get('contrasena') !== fd.get('confirmar_contrasena')) return showError('Error','Las contraseñas no coinciden');
                
                fetchAjax('?c=Usuario&a=crear',{ method: 'POST', body: fd })
                    .then(raw=>{
                        const res = normalizeResponse(raw);
                        if (res.exito){ closeModal(); showSuccess('¡Usuario Creado!','El usuario ha sido creado exitosamente.'); setTimeout(()=>location.reload(),1500); }
                        else showError('Error', res.mensaje || 'No se pudo crear');
                    }).catch(()=> showError('Error','Error de conexión'));
            });
        }

        // Editar usuario
        const editForm = $('edit-form');
        if (editForm){
            editForm.addEventListener('submit', function(e){
                e.preventDefault();
                const fd = new FormData(this);
                const pw = fd.get('contrasena');
                if (pw && pw !== fd.get('confirmar_contrasena')) return showError('Error','Las contraseñas no coinciden');
                if (!pw){ fd.delete('contrasena'); fd.delete('confirmar_contrasena'); }
                
                fetchAjax('?c=Usuario&a=actualizar',{ method: 'POST', body: fd })
                    .then(raw=>{
                        const res = normalizeResponse(raw);
                        if (res.exito){ closeModal(); showSuccess('¡Usuario Actualizado!','Los datos del usuario han sido actualizados.'); setTimeout(()=>location.reload(),1500); }
                        else showError('Error', res.mensaje || 'No se pudo actualizar');
                    }).catch(()=> showError('Error','Error de conexión'));
            });
        }
    });


    // Cerrar modales
    document.addEventListener('click', function(e){
        if (e.target.classList.contains('close-modal-btn') || e.target.classList.contains('modal-overlay')) closeModal();
    });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });

})();