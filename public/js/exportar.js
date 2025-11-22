

document.addEventListener("DOMContentLoaded", () => {
    cargarTablaExportar();
});

function cargarTablaExportar() {
    const tbody = document.getElementById("tablaExportarBody");

    fetch("?c=Proyecto&a=obtenerProyectosJSON")
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="4" class="text-center py-3">No hay datos.</td></tr>
                `;
                return;
            }

            data.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>${p.herramienta || "—"}</td>
                        <td>${p.avance}%</td>
                        <td>${p.estado}</td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            console.error("Error:", err);
            tbody.innerHTML = `
                <tr><td colspan="4" class="text-center text-danger py-3">
                    Error al cargar los datos
                </td></tr>
            `;
        });
}

