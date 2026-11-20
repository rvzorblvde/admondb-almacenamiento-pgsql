(function () {
    'use strict';

    const form = document.getElementById('form-ruta');
    if (!form) return;

    const campoId = form.elements['id_ruta'];
    const campoTransporte = form.elements['id_transporte'];
    const campoTransportista = form.elements['id_transportista'];
    const campoSalida = form.elements['hora_salida'];
    const campoLlegada = form.elements['hora_estimada_llegada'];
    const campoZona = form.elements['zona_ruta'];
    const campoDistancia = form.elements['distancia_estimada'];
    const campoEstado = form.elements['estado_ruta'];
    const campoNombre = form.elements['nombre_ruta'];
    const campoFecha = form.elements['fecha_ruta'];

    const titulo = document.getElementById('titulo-formulario');
    const contenedorEstado = document.getElementById('contenedor-estado');
    const btnGuardar = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const btnCancelar = document.getElementById('btn-cancelar');

    const filas = document.querySelectorAll('#tabla-rutas tbody tr[data-id]');

    // Cambiar entre el modo de registro y modificación.
    function actualizarModo() {
        const esEdicion = campoId.value !== '';

        titulo.textContent = esEdicion ? 'Editar ruta' : 'Nueva ruta';
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
        document.querySelectorAll('.panel-formulario .texto-error').forEach(function (el) {
            el.remove();
        });

        document.querySelectorAll('.panel-formulario .mensaje-error').forEach(function (el) {
            el.remove();
        });
    }

    // Colocar en el formulario los datos de una ruta seleccionada.
    function cargarFila(fila) {
        campoId.value = fila.dataset.id;
        campoTransporte.value = fila.dataset.transporte;
        campoTransportista.value = fila.dataset.transportista;
        campoSalida.value = fila.dataset.salida;
        campoLlegada.value = fila.dataset.llegada;
        campoZona.value = fila.dataset.zona;
        campoDistancia.value = fila.dataset.distancia;
        campoEstado.value = fila.dataset.estado;
        campoNombre.value = fila.dataset.nombre;
        campoFecha.value = fila.dataset.fecha;

        limpiarErrores();
        actualizarModo();
        campoTransporte.focus();
    }

    // Regresar al formulario para registrar una ruta nueva.
    function limpiarFormulario() {
        form.reset();

        campoId.value = '';
        campoTransporte.value = '';
        campoTransportista.value = '';
        campoSalida.value = '';
        campoLlegada.value = '';
        campoZona.value = '';
        campoDistancia.value = '';
        campoEstado.value = 'Programada';
        campoNombre.value = '';

        // Conservar la fecha predeterminada del formulario.
        campoFecha.value = campoFecha.defaultValue;

        limpiarErrores();
        actualizarModo();
        campoTransporte.focus();
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

        const nombre = campoNombre.value.trim() || 'esta ruta';

        if (!window.confirm('¿Eliminar la ruta "' + nombre + '"? Esta acción no se puede deshacer.'))
            evento.preventDefault();
    });

    actualizarModo();
})();
