(function () {
    'use strict';

    const form = document.getElementById('form-entrega');
    if (!form) return;

    const campoId = form.elements['id_entrega'];
    const campoRuta = form.elements['id_ruta'];
    const campoDomicilio = form.elements['id_domicilio'];
    const campoHora = form.elements['hora_programada'];
    const campoEstado = form.elements['estado_entrega'];

    const titulo = document.getElementById('titulo-formulario');
    const contenedorEstado = document.getElementById('contenedor-estado');
    const btnGuardar = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const btnCancelar = document.getElementById('btn-cancelar');

    const filas = document.querySelectorAll('#tabla-entregas tbody tr[data-id]');

    // Cambiar entre el modo de registro y modificación.
    function actualizarModo() {
        const esEdicion = campoId.value !== '';

        titulo.textContent = esEdicion ? 'Editar entrega' : 'Nueva entrega';
        btnGuardar.textContent = esEdicion ? 'Guardar cambios' : 'Registrar';

        btnEliminar.hidden = !esEdicion;
        btnCancelar.hidden = !esEdicion;

        contenedorEstado.hidden = !esEdicion;
        campoEstado.disabled = !esEdicion;

        filas.forEach(function (fila) {
            fila.classList.toggle(
                'fila-seleccionada',
                esEdicion && fila.dataset.id === campoId.value
            );
        });
    }

    // Eliminar mensajes de validación anteriores.
    function limpiarErrores() {
        document.querySelectorAll('.panel-formulario .texto-error, .panel-formulario .mensaje-error')
            .forEach(function (el) { el.remove(); });
    }

    // Colocar en el formulario los datos de una entrega seleccionada.
    function cargarFila(fila) {
        campoId.value = fila.dataset.id;
        campoRuta.value = fila.dataset.ruta;
        campoDomicilio.value = fila.dataset.domicilio;
        campoHora.value = fila.dataset.hora;
        campoEstado.value = fila.dataset.estado;

        limpiarErrores();
        actualizarModo();
        campoRuta.focus();
    }

    // Regresar al formulario para registrar una entrega nueva.
    function limpiarFormulario() {
        form.reset();

        campoId.value = '';
        campoRuta.value = '';
        campoDomicilio.value = '';
        campoHora.value = '';
        campoEstado.value = 'Pendiente';

        limpiarErrores();
        actualizarModo();
        campoRuta.focus();
    }

    // Seleccionar o deseleccionar una fila.
    function seleccionarFila(fila) {
        if (campoId.value === fila.dataset.id)
            limpiarFormulario();
        else
            cargarFila(fila);
    }

    // Activar selección con clic o teclado.
    filas.forEach(function (fila) {
        fila.addEventListener('click', function () {
            seleccionarFila(fila);
        });

        fila.addEventListener('keydown', function (evento) {
            if (evento.key === 'Enter' || evento.key === ' ') {
                evento.preventDefault();
                seleccionarFila(fila);
            }
        });
    });

    // Cancelar la edición sin guardar.
    btnCancelar.addEventListener('click', limpiarFormulario);

    // Solicitar confirmación antes de eliminar.
    btnEliminar.addEventListener('click', function (evento) {
        if (campoId.value === '') {
            evento.preventDefault();
            return;
        }

        if (!window.confirm('¿Eliminar la entrega #' + campoId.value + '? Esta acción no se puede deshacer.'))
            evento.preventDefault();
    });

    actualizarModo();
})();
