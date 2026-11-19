(function () {
    'use strict';

    const form = document.getElementById('form-transporte');
    if (!form) return;

    const campoId     = form.elements['id_transporte'];
    const campoPlaca  = form.elements['placa'];
    const campoTipo   = form.elements['tipo_unidad'];
    const campoMarca  = form.elements['marca'];
    const campoModelo = form.elements['modelo'];
    const campoEstado = form.elements['estado'];

    const titulo      = document.getElementById('titulo-formulario');
    const btnGuardar  = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const filas       = document.querySelectorAll('#tabla-transportes tbody tr[data-id]');
    const grupoEstado = document.getElementById('grupo-estado');

    // Relación entre tipo de unidad, marca y modelo
    const unidades = {
        'Tractocamión': ['Freightliner', 'Cascadia'],
        'Camión de Caja Cerrada': ['Isuzu', 'ELF 600'],
        'Van de Carga': ['Ford', 'Transit'],
        'Furgoneta Compacta': ['Peugeot', 'Partner'],
        'Camioneta Pick-Up': ['Toyota', 'Hilux']
    };


    // Asignar marca y modelo según el tipo de unidad
    function actualizarUnidad() {
        const unidad = unidades[campoTipo.value];

        if (unidad) {
            campoMarca.value = unidad[0];
            campoModelo.value = unidad[1];
        } else {
            campoMarca.value = '';
            campoModelo.value = '';
        }
    }


    // Cambiar entre registro y edición
    function actualizarModo() {
        const hayTransporte = campoId.value !== '';

        titulo.textContent = hayTransporte ? 'Editar transporte' : 'Nuevo transporte';
        btnGuardar.textContent = hayTransporte ? 'Guardar cambios' : 'Registrar';
        btnEliminar.hidden = !hayTransporte;
        
        // Estado solo visible al modificar.
        grupoEstado.hidden = !hayTransporte;
        campoEstado.disabled = !hayTransporte;

        filas.forEach(function (fila) {
            fila.classList.toggle('fila-seleccionada',
                hayTransporte && fila.dataset.id === campoId.value);
        });
    }


    // Eliminar mensajes anteriores
    function limpiarErrores() {
        form.querySelectorAll('.texto-error').forEach(function (el) {
            el.remove();
        });

        document.querySelectorAll('.panel-formulario .mensaje-error').forEach(function (el) {
            el.remove();
        });
    }


    // Cargar los datos de la fila seleccionada
    function cargarFila(fila) {
        campoId.value = fila.dataset.id;
        campoPlaca.value = fila.dataset.placa;
        campoTipo.value = fila.dataset.tipo;
        campoMarca.value = fila.dataset.marca;
        campoModelo.value = fila.dataset.modelo;
        campoEstado.value = fila.dataset.estado;

        limpiarErrores();
        actualizarModo();
        campoPlaca.focus();
    }


    // Limpiar formulario
    function limpiar() {
        campoId.value = '';
        campoPlaca.value = '';
        campoTipo.value = '';
        campoMarca.value = '';
        campoModelo.value = '';
        campoEstado.value = 'Disponible';

        limpiarErrores();
        actualizarModo();
        campoPlaca.focus();
    }


    // Seleccionar o deseleccionar una fila
    function alternarFila(fila) {
        if (campoId.value === fila.dataset.id)
            limpiar();
        else
            cargarFila(fila);
    }


    // Completar marca y modelo
    campoTipo.addEventListener('change', actualizarUnidad);


    // Convertir la placa a mayúsculas
    campoPlaca.addEventListener('input', function () {
        campoPlaca.value = campoPlaca.value.toUpperCase();
    });


    // Eventos de las filas
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


    // Confirmación al eliminar
    btnEliminar.addEventListener('click', function (evento) {
        const placa = campoPlaca.value.trim() || 'este transporte';

        if (!window.confirm('¿Eliminar el transporte con placa ' + placa + '? Esta acción no se puede deshacer.'))
            evento.preventDefault();
    });


    actualizarModo();

})();
