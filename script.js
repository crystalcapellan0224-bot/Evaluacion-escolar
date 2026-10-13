document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEvaluacion');
    const resultadoDiv = document.getElementById('resultado');
    const resultadoContenido = document.getElementById('resultadoContenido');
    const listaHistorial = document.getElementById('listaHistorial');
    const btnActualizar = document.getElementById('btnActualizar');

    cargarHistorial();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const nota = parseFloat(formData.get('nota'));

        if (isNaN(nota) || nota < 0 || nota > 100) {
            alert('La nota debe estar entre 0 y 100.');
            return;
        }

        try {
            const response = await fetch('php/procesar.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            mostrarResultado(data);
            form.reset();
            cargarHistorial();

        } catch (error) {
            alert('No se pudo conectar con el servidor. Verifique que Apache y MySQL estén activos.');
            console.error(error);
        }
    });

    btnActualizar.addEventListener('click', cargarHistorial);

    function mostrarResultado(data) {
        resultadoDiv.className = `resultado ${data.estado}`;
        resultadoDiv.classList.remove('oculto');

        let asistenciaHtml = data.asistencia !== null 
            ? `<p><strong>Asistencia:</strong> ${data.asistencia}%</p>` 
            : '';

        resultadoContenido.innerHTML = `
            <span class="badge ${data.estado}">${data.estado}</span>
            <p><strong>Estudiante:</strong> ${data.nombre}</p>
            <p><strong>Grado:</strong> ${data.grado}°</p>
            <p><strong>Promedio:</strong> ${data.nota}</p>
            ${asistenciaHtml}
            <p style="margin-top: 12px;">${data.mensaje}</p>
        `;
    }

    async function cargarHistorial() {
        try {
            const response = await fetch('php/listar.php');
            const data = await response.json();

            if (data.error) {
                listaHistorial.innerHTML = `<p class="empty">${data.error}</p>`;
                return;
            }

            if (!data.length) {
                listaHistorial.innerHTML = '<p class="empty">No hay registros todavía.</p>';
                return;
            }

            listaHistorial.innerHTML = data.map(item => `
                <div class="historial-item">
                    <div class="info">
                        <div class="nombre">${item.nombre}</div>
                        <div class="detalle">
                            ${item.grado}° · Nota: ${item.nota} · ${item.fecha}
                        </div>
                    </div>
                    <span class="badge ${item.estado}">${item.estado}</span>
                </div>
            `).join('');

        } catch (error) {
            listaHistorial.innerHTML = '<p class="empty">No se pudo cargar el historial. Verifique la conexión.</p>';
        }
    }
});