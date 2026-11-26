(function () {

    'use strict';


    /*
     * ============================================================
     * producto.js
     * ------------------------------------------------------------
     * Sirve para seleccionar los productos de la tabla
     * y pasar sus datos al formulario.
     * ============================================================
     */


    const form =
        document.getElementById(
            'form-producto'
        );


    if (!form)
    {
        return;
    }



    const id =
        document.getElementById(
            'id_producto'
        );


    const nombre =
        document.getElementById(
            'nombre_producto'
        );


    const peso =
        document.getElementById(
            'peso'
        );


    const precio =
        document.getElementById(
            'precio'
        );


    const ancho =
        document.getElementById(
            'ancho'
        );


    const alto =
        document.getElementById(
            'alto'
        );


    const largo =
        document.getElementById(
            'largo'
        );


    const stock =
        document.getElementById(
            'stock'
        );


    const proveedor =
        document.getElementById(
            'id_proveedor'
        );


    const ubicacion =
        document.getElementById(
            'id_ubicacion'
        );


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


    const filas =
        document.querySelectorAll(
            '#tabla-productos tbody tr'
        );



    /*
     * ============================================================
     * CARGAR DATOS DE UNA FILA
     * ============================================================
     */

    function cargarFila(fila)
    {

        id.value =
            fila.dataset.id;


        nombre.value =
            fila.dataset.nombre;


        peso.value =
            fila.dataset.peso;


        precio.value =
            fila.dataset.precio;


        ancho.value =
            fila.dataset.ancho;


        alto.value =
            fila.dataset.alto;


        largo.value =
            fila.dataset.largo;


        stock.value =
            fila.dataset.stock;


        proveedor.value =
            fila.dataset.proveedor;


        ubicacion.value =
            fila.dataset.ubicacion;



        /*
         * Marcamos la fila seleccionada.
         */
        filas.forEach(
            function (otraFila)
            {
                otraFila.classList.remove(
                    'fila-seleccionada'
                );
            }
        );


        fila.classList.add(
            'fila-seleccionada'
        );
    }



    /*
     * ============================================================
     * SELECCIONAR FILA
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
                        evento.key === 'Enter'
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
             * Quitamos el ID porque insertar
             * significa crear un registro nuevo.
             */
            id.value = '';
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

            if (
                id.value === ''
            )
            {
                evento.preventDefault();


                alert(
                    'Seleccione un producto para modificar.'
                );
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

            if (
                id.value === ''
            )
            {
                evento.preventDefault();


                alert(
                    'Seleccione un producto para eliminar.'
                );


                return;
            }


            if (
                !window.confirm(
                    '¿Seguro que desea eliminar este producto?'
                )
            )
            {
                evento.preventDefault();
            }

        }
    );

})();