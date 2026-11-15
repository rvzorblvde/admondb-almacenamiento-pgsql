
(function () {
    'use strict';

    const form = document.getElementById('form-transportista');
    if (!form)
        return;

    const campoId       = form.elements['id_transportista'];
    const campoNombre   = form.elements['nombre_transportista'];
    const campoTelefono = form.elements['telefono_transportista'];
    const campoCorreo   = form.elements['correo_transportista'];
    const campoEstado   = form.elements['estado'];

    const campoFecha    = document.getElementById('fecha_contratacion');
    const titulo        = document.getElementById('titulo-formulario');
    const btnGuardar    = document.getElementById('btn-guardar');
    const btnEliminar   = document.getElementById('btn-eliminar');
    const filas         = document.querySelectorAll('#tabla-transportistas tbody tr[data-id]');


    // Ajustar títulos según si hay un transportista seleccionado
    function actualizarModo() {
        const hayTransportista = campoId.value !== '';

        titulo.textContent     = hayTransportista ? 'Editar transportista' : 'Nuevo transportista';
        btnGuardar.textContent = hayTransportista ? 'Guardar cambios' : 'Registrar';
        btnEliminar.hidden     = !hayTransportista;

        // El estado solo se modifica al actualizar
        campoEstado.disabled = !hayTransportista;

        filas.forEach(function (fila) {
            fila.classList.toggle('fila-seleccionada', hayTransportista && fila.dataset.id === campoId.value);
        });
    }


    // Cargar los datos del transportista seleccionado
    function cargarFila(fila) {
        campoId.value       = fila.dataset.id;
        campoNombre.value   = fila.dataset.nombre;
        campoTelefono.value = fila.dataset.telefono;
        campoCorreo.value   = fila.dataset.correo;
        campoEstado.value   = fila.dataset.estado;
        campoFecha.value    = fila.dataset.fecha || '';

        limpiarErrores();
        actualizarModo();
        campoNombre.focus();
    }


    // Eliminar mensajes de error del formulario
    function limpiarErrores() {
        form.querySelectorAll('.texto-error').forEach(function (el) {
            el.remove();
        });

        document.querySelectorAll('.panel-formulario .mensaje-error').forEach(function (el) {
            el.remove();
        });
    }


    // Limpiar los campos para registrar otro transportista
    function limpiar() {
        campoId.value       = '';
        campoNombre.value   = '';
        campoTelefono.value = '';
        campoCorreo.value   = '';
        campoEstado.value   = 'Disponible';
        campoFecha.value    = '';

        limpiarErrores();
        actualizarModo();
        campoNombre.focus();
    }


    // Seleccionar o deseleccionar una fila
    function alternarFila(fila) {
        if (campoId.value === fila.dataset.id)
            limpiar();
        else
            cargarFila(fila);
    }


    // Eventos de las filas de la tabla
    filas.forEach(function (fila) {

        fila.addEventListener('click', function () {
            alternarFila(fila);
        });

        fila.addEventListener('keydown', function (evento) {
            if (evento.key === 'Enter' || evento.key === ' ') {
                evento.preventDefault();
                alternarFila(fila);
            }
        });

    });


    // Confirmar antes de eliminar
    btnEliminar.addEventListener('click', function (evento) {
        const nombre = campoNombre.value.trim() || 'este transportista';

        if (!window.confirm('¿Eliminar a ' + nombre + '? Esta acción no se puede deshacer.'))
            evento.preventDefault();
    });


    actualizarModo();

})();
