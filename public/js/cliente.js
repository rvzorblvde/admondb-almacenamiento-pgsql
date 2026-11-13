(function () {
    'use strict';

    const form = document.getElementById('form-cliente');
    if (!form) return;

    const campoId       = form.elements['id_cliente'];
    const campoNombre   = form.elements['nombre_cliente'];
    const campoTelefono = form.elements['telefono_cliente'];
    const campoCorreo   = form.elements['correo_electronico'];
    const campoEstado   = form.elements['estado'];

    const campoFecha    = document.getElementById('fecha_registro');
    const titulo        = document.getElementById('titulo-formulario');
    const btnGuardar    = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const btnDomicilios = document.getElementById('btn-domicilios');
    const filas         = document.querySelectorAll('#tabla-clientes tbody tr[data-id]');

    // Ajustar títulos según si hay un cliente seleccionado
    function actualizarModo() {
        const hayCliente = campoId.value !== '';

        titulo.textContent      = hayCliente ? 'Editar cliente' : 'Nuevo cliente';
        btnGuardar.textContent  = hayCliente ? 'Guardar cambios' : 'Registrar';
        btnEliminar.hidden      = !hayCliente;
        campoEstado.disabled = !hayCliente;   // El estado solo se modifica al actualizar

      // Botón morado: lleva a los domicilios del cliente seleccionado
      btnDomicilios.hidden = !hayCliente;
      btnDomicilios.href   = hayCliente ? 'domicilio.php?id_cliente=' + encodeURIComponent(campoId.value) : '#';
      const filaActual     = hayCliente ? document.querySelector('#tabla-clientes tbody tr[data-id="' + campoId.value + '"]') : null;
      const totalDom       = filaActual ? filaActual.dataset.domicilios : null;
      btnDomicilios.textContent = totalDom !== null && totalDom !== undefined ? 'Domicilios (' + totalDom + ')' : 'Domicilios';

        filas.forEach(function (fila) {
            fila.classList.toggle('fila-seleccionada', hayCliente && fila.dataset.id === campoId.value);
        });
    }

    function cargarFila(fila) {
        campoId.value       = fila.dataset.id;
        campoNombre.value   = fila.dataset.nombre;
        campoTelefono.value = fila.dataset.telefono;
        campoCorreo.value   = fila.dataset.correo;
        campoEstado.value   = fila.dataset.estado;
        campoFecha.value    = fila.dataset.fecha || '';

        actualizarModo();
        campoNombre.focus();
    }

    function limpiar() {
        campoId.value       = '';
        campoNombre.value   = '';
        campoTelefono.value = '';
        campoCorreo.value   = '';
        campoEstado.value   = 'Activo';
        campoFecha.value    = '';

        form.querySelectorAll('.texto-error').forEach(function (el) { el.remove(); });
        document.querySelectorAll('.panel-formulario .mensaje-error').forEach(function (el) { el.remove(); });

        actualizarModo();
        campoNombre.focus();
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

    btnEliminar.addEventListener('click', function (evento) {
        const nombre = campoNombre.value.trim() || 'este cliente';
        if (!window.confirm('¿Eliminar a ' + nombre + '? Esta acción no se puede deshacer.')) {
            evento.preventDefault();
        }
    });

    actualizarModo();
})();
