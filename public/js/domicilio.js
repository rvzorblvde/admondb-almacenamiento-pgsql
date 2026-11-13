(function () {
    'use strict';

    // Dropdown de clientes: al elegir uno se recarga la página con sus domicilios
    const formSeleccion = document.getElementById('form-seleccion');
    if (formSeleccion) {
        formSeleccion.elements['id_cliente'].addEventListener('change', function () {
            formSeleccion.submit();
        });
    }

    // El formulario y la tabla solo existen si hay un cliente seleccionado
    const form = document.getElementById('form-domicilio');
    if (!form) return;

    const campoId        = form.elements['id_domicilio'];
    const campoDomicilio = form.elements['domicilio'];

    const contador    = document.getElementById('contador-domicilio');
    const titulo      = document.getElementById('titulo-formulario');
    const btnGuardar  = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const filas       = document.querySelectorAll('#tabla-domicilios tbody tr[data-id]');
    const maximo      = campoDomicilio.maxLength;

    function actualizarContador() {
        contador.textContent = campoDomicilio.value.length + '/' + maximo;
    }

    function actualizarModo() {
        const hayDomicilio = campoId.value !== '';

        titulo.textContent     = hayDomicilio ? 'Editar domicilio' : 'Nuevo domicilio';
        btnGuardar.textContent = hayDomicilio ? 'Guardar cambios' : 'Registrar';
        btnEliminar.hidden     = !hayDomicilio;

        filas.forEach(function (fila) {
            fila.classList.toggle('fila-seleccionada', hayDomicilio && fila.dataset.id === campoId.value);
        });
        actualizarContador();
    }

    function cargarFila(fila) {
        campoId.value        = fila.dataset.id;
        campoDomicilio.value = fila.dataset.domicilio;
        actualizarModo();
        campoDomicilio.focus();
    }

    function limpiar() {
        campoId.value        = '';
        campoDomicilio.value = '';

        form.querySelectorAll('.texto-error').forEach(function (el) { el.remove(); });
        document.querySelectorAll('.panel-formulario .mensaje-error').forEach(function (el) { el.remove(); });

        actualizarModo();
        campoDomicilio.focus();
    }

    function alternarFila(fila) {
        if (campoId.value === fila.dataset.id) {
            limpiar();            // Deseleccionar
        } else {
            cargarFila(fila);
        }
    }

    filas.forEach(function (fila) {
        fila.addEventListener('click', function () { alternarFila(fila); });
        fila.addEventListener('keydown', function (evento) {
            if (evento.key === 'Enter' || evento.key === ' ') {
                evento.preventDefault();
                alternarFila(fila);
            }
        });
    });

    campoDomicilio.addEventListener('input', actualizarContador);

    btnEliminar.addEventListener('click', function (evento) {
        if (!window.confirm('¿Eliminar este domicilio? Esta acción no se puede deshacer.')) {
            evento.preventDefault();
        }
    });

    actualizarModo();
})();
