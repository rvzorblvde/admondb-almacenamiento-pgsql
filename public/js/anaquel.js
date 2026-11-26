(function () {

    'use strict';


    /*
     * ============================================================
     * anaquel.js
     * ------------------------------------------------------------
     * Aquí manejamos:
     * - Seleccionar una fila.
     * - Insertar.
     * - Modificar.
     * - Eliminar.
     *
     * El estado se mantiene internamente,
     * pero no se muestra como campo editable.
     * ============================================================
     */


    const form =
        document.getElementById(
            'form-anaquel'
        );


    if (!form)
    {
        return;
    }



    // ============================================================
    // CAMPOS
    // ============================================================

    const campoId =
        document.getElementById(
            'id_ubicacion'
        );


    const campoZona =
        document.getElementById(
            'zona'
        );


    const campoPasillo =
        document.getElementById(
            'pasillo'
        );


    const campoRack =
        document.getElementById(
            'rack'
        );


    const campoNivel =
        document.getElementById(
            'nivel'
        );


    /*
     * El estado existe en un input hidden.
     * No lo ve ni lo modifica directamente el usuario.
     */
    const campoEstado =
        document.getElementById(
            'estado'
        );



    // ============================================================
    // BOTONES
    // ============================================================

    const btnInsertar =
        document.getElementById(
            'btn-insertar'
        );


    const btnModificar =
        document.getElementById(
            'btn-modificar'
        );


    const btnEliminar =
        document.getElementById(
            'btn-eliminar'
        );



    // ============================================================
    // FILAS DE LA TABLA
    // ============================================================

    const filas =
        document.querySelectorAll(
            '#tabla-anaqueles tbody tr[data-id]'
        );



    /*
     * ============================================================
     * MARCAR FILA SELECCIONADA
     * ============================================================
     */

    function actualizarSeleccion()
    {
        const haySeleccion =
            campoId.value !== '';


        filas.forEach(
            function (fila)
            {
                fila.classList.toggle(
                    'fila-seleccionada',

                    haySeleccion &&
                    fila.dataset.id
                    ===
                    campoId.value
                );
            }
        );
    }



    /*
     * ============================================================
     * CARGAR UNA FILA
     * ============================================================
     */

    function cargarFila(fila)
    {
        // Guardamos el ID.
        campoId.value =
            fila.dataset.id;


        // Pasamos los datos al formulario.
        campoZona.value =
            fila.dataset.zona;


        campoPasillo.value =
            fila.dataset.pasillo;


        campoRack.value =
            fila.dataset.rack;


        campoNivel.value =
            fila.dataset.nivel;


        /*
         * El estado también se carga,
         * pero queda oculto.
         */
        campoEstado.value =
            fila.dataset.estado;


        actualizarSeleccion();
    }



    /*
     * ============================================================
     * SELECCIONAR FILAS
     * ============================================================
     */

    filas.forEach(
        function (fila)
        {

            fila.addEventListener(
                'click',

                function ()
                {
                    cargarFila(
                        fila
                    );
                }
            );


            fila.addEventListener(
                'keydown',

                function (evento)
                {
                    if (
                        evento.key ===
                        'Enter'
                    )
                    {
                        cargarFila(
                            fila
                        );
                    }
                }
            );

        }
    );



    /*
     * ============================================================
     * INSERTAR
     * ============================================================
     */

    btnInsertar.addEventListener(
        'click',

        function ()
        {
            /*
             * Quitamos el ID porque queremos
             * crear un registro nuevo.
             */
            campoId.value = '';


            /*
             * Todo anaquel nuevo comienza Disponible.
             */
            campoEstado.value =
                'Disponible';
        }
    );



    /*
     * ============================================================
     * MODIFICAR
     * ============================================================
     */

    btnModificar.addEventListener(
        'click',

        function (evento)
        {

            /*
             * Para modificar primero debe haber
             * una fila seleccionada.
             */
            if (
                campoId.value === ''
            )
            {
                evento.preventDefault();


                alert(
                    'Seleccione un anaquel para modificar.'
                );


                return;
            }

        }
    );



    /*
     * ============================================================
     * ELIMINAR
     * ============================================================
     */

    btnEliminar.addEventListener(
        'click',

        function (evento)
        {

            /*
             * Para eliminar también debe haber
             * una fila seleccionada.
             */
            if (
                campoId.value === ''
            )
            {
                evento.preventDefault();


                alert(
                    'Seleccione un anaquel para eliminar.'
                );


                return;
            }


            const confirmar =
                window.confirm(
                    '¿Seguro que desea eliminar este anaquel?'
                );


            if (!confirmar)
            {
                evento.preventDefault();
            }
        }
    );



    actualizarSeleccion();

})();