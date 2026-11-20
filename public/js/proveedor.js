(function () {
    'use strict';

    const form = document.getElementById('form-proveedor');
    if (!form)
        return;

    const campoId       = form.elements['id_proveedor'];
    const campoNombre   = form.elements['nombre_proveedor'];
    const campoEmpresa  = form.elements['nombre_empresa'];
    const campoTelefono = form.elements['telefono_proveedor'];
    const campoCorreo   = form.elements['correo_empresa'];

    const titulo        = document.getElementById('titulo-formulario');
    const btnGuardar    = document.getElementById('btn-guardar');
    const btnEliminar   = document.getElementById('btn-eliminar');
    const filas         = document.querySelectorAll('#tabla-proveedores tbody tr[data-id]');


    // Mismo cálculo que Proveedor::dominioEmpresa() en PHP
    const ACENTOS = { 'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u', 'ü': 'u', 'ñ': 'n' };

    function dominioEmpresa(empresa) {
        return empresa.trim().toLowerCase()
            .replace(/[áéíóúüñ]/g, function (letra) { return ACENTOS[letra]; })
            .replace(/[^a-z0-9]/g, '');
    }


    // Sufijo actual del correo: @dominio.com
    function sufijoCorreo() {
        return '@' + (dominioEmpresa(campoEmpresa.value) || 'empresa') + '.com';
    }


    // Obtiene solo el usuario (lo que va antes del @) del valor del campo
    function usuarioDelCampo(valor, sufijoAnterior) {
        valor = valor.toLowerCase().replace(/\s+/g, '');

        const arroba = valor.indexOf('@');
        if (arroba !== -1)
            return valor.slice(0, arroba);

        // Si se borró el @, se quita lo que quedó del dominio anterior
        const restoDominio = sufijoAnterior.slice(1);
        if (restoDominio && valor.endsWith(restoDominio))
            return valor.slice(0, -restoDominio.length);

        return valor;
    }


    // Escribe usuario + @empresa.com en el campo y deja el cursor en el usuario
    let sufijoAnterior = sufijoCorreo();

    function armarCorreo(posicionCursor) {
        const usuario = usuarioDelCampo(campoCorreo.value, sufijoAnterior);
        const sufijo  = sufijoCorreo();

        campoCorreo.value       = usuario === '' ? '' : usuario + sufijo;
        campoCorreo.placeholder = 'contacto' + sufijo;
        sufijoAnterior          = sufijo;

        if (posicionCursor !== undefined && document.activeElement === campoCorreo) {
            const pos = Math.min(posicionCursor, usuario.length);
            campoCorreo.setSelectionRange(pos, pos);
        }
    }


    // Ajustar títulos según si hay un proveedor seleccionado
    function actualizarModo() {
        const hayProveedor = campoId.value !== '';

        titulo.textContent     = hayProveedor ? 'Editar proveedor' : 'Nuevo proveedor';
        btnGuardar.textContent = hayProveedor ? 'Guardar cambios' : 'Registrar';
        btnEliminar.hidden     = !hayProveedor;

        filas.forEach(function (fila) {
            fila.classList.toggle('fila-seleccionada', hayProveedor && fila.dataset.id === campoId.value);
        });
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


    // Cargar los datos del proveedor seleccionado
    function cargarFila(fila) {
        campoId.value       = fila.dataset.id;
        campoNombre.value   = fila.dataset.nombre;
        campoEmpresa.value  = fila.dataset.empresa;
        campoTelefono.value = fila.dataset.telefono;
        campoCorreo.value   = fila.dataset.usuario;

        limpiarErrores();
        armarCorreo();
        actualizarModo();
        campoNombre.focus();
    }


    // Limpiar los campos para registrar otro proveedor
    function limpiar() {
        campoId.value       = '';
        campoNombre.value   = '';
        campoEmpresa.value  = '';
        campoTelefono.value = '';
        campoCorreo.value   = '';

        limpiarErrores();
        armarCorreo();
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


    // Al escribir el correo se completa el @empresa.com
    campoCorreo.addEventListener('input', function () {
        armarCorreo(campoCorreo.selectionStart);
    });

    // Si se cambia la empresa, se actualiza el dominio del correo
    campoEmpresa.addEventListener('input', function () {
        armarCorreo();
    });


    // Confirmar antes de eliminar
    btnEliminar.addEventListener('click', function (evento) {
        const nombre = campoNombre.value.trim() || 'este proveedor';

        if (!window.confirm('¿Eliminar a ' + nombre + '? Esta acción no se puede deshacer.'))
            evento.preventDefault();
    });


    armarCorreo();
    actualizarModo();

})();
