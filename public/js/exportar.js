document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalExportar');
    const btn = document.getElementById('btnExportar');
    const cerrar = document.getElementById('cerrarModal');

    btn.onclick = () => modal.style.display = 'flex';
    cerrar.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
});
