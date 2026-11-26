<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Producto.php';

/*
 * ============================================================
 * producto_controlador.php
 * ------------------------------------------------------------
 * Aquí está la lógica de Producto.
 *
 * Recibimos los datos del formulario, hacemos las
 * validaciones y después mandamos la información
 * al modelo Producto.php.
 * ============================================================
 */

iniciarSesion();


/*
 * ============================================================
 * VALORES INICIALES
 * ============================================================
 */

$errores = [];

$flash =
    obtenerFlash();


$datos =
[
    'id_producto' =>
        '',

    'nombre_producto' =>
        '',

    'peso' =>
        '',

    'precio' =>
        '',

    'ancho' =>
        '',

    'alto' =>
        '',

    'largo' =>
        '',

    'stock' =>
        '0',

    'id_proveedor' =>
        '',

    'id_ubicacion' =>
        ''
];


/*
 * ============================================================
 * SI RECIBIMOS EL FORMULARIO
 * ============================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
)
{

    /*
     * Primero revisamos el token de seguridad.
     */
    if (
        !csrfValido(
            (string) (
                $_POST['csrf']
                ?? ''
            )
        )
    )
    {
        mensajeFlash(
            'error',
            'La sesión expiró. Intente nuevamente.'
        );

        redirigir(
            'producto.php'
        );
    }


    /*
     * Sabemos qué botón presionó el usuario.
     *
     * insertar
     * modificar
     * eliminar
     */
    $accion =
        $_POST['accion']
        ?? '';


    /*
     * Guardamos el ID si existe.
     */
    $idProducto =
        filter_var(
            $_POST['id_producto'] ?? '',
            FILTER_VALIDATE_INT,
            [
                'options' =>
                [
                    'min_range' => 1
                ]
            ]
        );


    /*
     * ========================================================
     * ELIMINAR
     * ========================================================
     */

    if (
        $accion === 'eliminar'
    )
    {

        if (
            $idProducto === false
        )
        {
            mensajeFlash(
                'error',
                'Seleccione un producto para eliminar.'
            );

            redirigir(
                'producto.php'
            );
        }


        try
        {
            $filas =
                Producto::eliminar(
                    (int) $idProducto
                );


            if (
                $filas > 0
            )
            {
                mensajeFlash(
                    'exito',
                    'Producto eliminado correctamente.'
                );
            }
            else
            {
                mensajeFlash(
                    'error',
                    'El producto ya no existe.'
                );
            }
        }

        catch (PDOException $e)
        {
            /*
             * Si el producto ya se encuentra relacionado
             * con una entrega PostgreSQL no permite borrarlo.
             */
            if (
                $e->getCode() === '23503'
            )
            {
                mensajeFlash(
                    'error',
                    'No se puede eliminar porque el producto tiene información relacionada.'
                );
            }
            else
            {
                mensajeFlash(
                    'error',
                    'No se pudo eliminar el producto.'
                );
            }
        }


        redirigir(
            'producto.php'
        );
    }



    /*
     * ========================================================
     * DATOS DE INSERTAR / MODIFICAR
     * ========================================================
     */

    $datos['id_producto'] =
        $idProducto === false
            ? ''
            : (string) $idProducto;


    $datos['nombre_producto'] =
        trim(
            (string) (
                $_POST['nombre_producto']
                ?? ''
            )
        );


    $datos['peso'] =
        str_replace(
            ',',
            '.',
            trim(
                (string) (
                    $_POST['peso']
                    ?? ''
                )
            )
        );


    $datos['precio'] =
        str_replace(
            ',',
            '.',
            trim(
                (string) (
                    $_POST['precio']
                    ?? ''
                )
            )
        );


    $datos['ancho'] =
        trim(
            (string) (
                $_POST['ancho']
                ?? ''
            )
        );


    $datos['alto'] =
        trim(
            (string) (
                $_POST['alto']
                ?? ''
            )
        );


    $datos['largo'] =
        trim(
            (string) (
                $_POST['largo']
                ?? ''
            )
        );


    $datos['stock'] =
        trim(
            (string) (
                $_POST['stock']
                ?? ''
            )
        );


    $datos['id_proveedor'] =
        trim(
            (string) (
                $_POST['id_proveedor']
                ?? ''
            )
        );


    $datos['id_ubicacion'] =
        trim(
            (string) (
                $_POST['id_ubicacion']
                ?? ''
            )
        );



    /*
     * ========================================================
     * VALIDACIONES
     * ========================================================
     */


    // Nombre obligatorio.
    if (
        $datos['nombre_producto'] === ''
    )
    {
        $errores['nombre_producto'] =
            'Ingrese el nombre del producto.';
    }


    // Peso obligatorio y numérico.
    if (
        $datos['peso'] === ''
        ||
        !is_numeric(
            $datos['peso']
        )
        ||
        (float) $datos['peso'] < 0
    )
    {
        $errores['peso'] =
            'Ingrese un peso válido.';
    }


    // Precio obligatorio.
    if (
        $datos['precio'] === ''
        ||
        !is_numeric(
            $datos['precio']
        )
        ||
        (float) $datos['precio'] < 0
    )
    {
        $errores['precio'] =
            'Ingrese un precio válido.';
    }


    /*
     * Las dimensiones están separadas visualmente,
     * pero PostgreSQL las guarda juntas.
     */
    if (
        $datos['ancho'] === ''
        ||
        !is_numeric(
            $datos['ancho']
        )
        ||
        (float) $datos['ancho'] <= 0
    )
    {
        $errores['ancho'] =
            'Ingrese un ancho válido.';
    }


    if (
        $datos['alto'] === ''
        ||
        !is_numeric(
            $datos['alto']
        )
        ||
        (float) $datos['alto'] <= 0
    )
    {
        $errores['alto'] =
            'Ingrese un alto válido.';
    }


    if (
        $datos['largo'] === ''
        ||
        !is_numeric(
            $datos['largo']
        )
        ||
        (float) $datos['largo'] <= 0
    )
    {
        $errores['largo'] =
            'Ingrese un largo válido.';
    }


    // Stock.
    if (
        filter_var(
            $datos['stock'],
            FILTER_VALIDATE_INT,
            [
                'options' =>
                [
                    'min_range' => 0
                ]
            ]
        ) === false
    )
    {
        $errores['stock'] =
            'Ingrese un stock válido.';
    }


    // Proveedor.
    $idProveedor =
        filter_var(
            $datos['id_proveedor'],
            FILTER_VALIDATE_INT,
            [
                'options' =>
                [
                    'min_range' => 1
                ]
            ]
        );


    if (
        $idProveedor === false
    )
    {
        $errores['id_proveedor'] =
            'Seleccione un proveedor.';
    }


    // Ubicación.
    $idUbicacion =
        filter_var(
            $datos['id_ubicacion'],
            FILTER_VALIDATE_INT,
            [
                'options' =>
                [
                    'min_range' => 1
                ]
            ]
        );


    if (
        $idUbicacion === false
    )
    {
        $errores['id_ubicacion'] =
            'Seleccione una ubicación.';
    }



    /*
     * Si los ID tienen formato correcto,
     * revisamos que sí existan.
     */
    if (
        $idProveedor !== false
        &&
        !Producto::existeProveedor(
            (int) $idProveedor
        )
    )
    {
        $errores['id_proveedor'] =
            'El proveedor seleccionado no existe.';
    }


    if (
        $idUbicacion !== false
        &&
        !Producto::existeAnaquel(
            (int) $idUbicacion
        )
    )
    {
        $errores['id_ubicacion'] =
            'El anaquel seleccionado no existe.';
    }



    /*
     * ========================================================
     * SI TODO ESTÁ BIEN
     * ========================================================
     */

    if (
        empty($errores)
    )
    {

        /*
         * Unimos:
         *
         * Ancho X Alto X Largo
         *
         * Ejemplo:
         * 10X10X10
         */
        $dimensiones =
            $datos['ancho']
            .
            'X'
            .
            $datos['alto']
            .
            'X'
            .
            $datos['largo'];


        try
        {

            /*
             * ================================================
             * INSERTAR
             * ================================================
             */
            
        if ($accion === 'insertar')
        {
            $idGuardado = Producto::insertar(
                (int) $idProveedor,
                (int) $idUbicacion,
                $datos['nombre_producto'],
                (float) $datos['precio'],
                (float) $datos['peso'],
                $dimensiones,
                (int) $datos['stock']
            );

            if ($idGuardado === 0)
            {
                $errores['id_ubicacion'] =
                    'El anaquel seleccionado ya contiene otro producto.';
            }
            else
            {
                mensajeFlash(
                    'exito',
                    'Producto registrado o stock actualizado correctamente.'
                );

                redirigir('producto.php');
            }
        }


            /*
             * ================================================
             * MODIFICAR
             * ================================================
             */
            if (
                $accion === 'modificar'
            )
            {

                if (
                    $idProducto === false
                )
                {
                    $errores['general'] =
                        'Seleccione un producto para modificar.';
                }
                else
                {
                    Producto::modificar(

                        (int) $idProducto,

                        (int) $idProveedor,

                        (int) $idUbicacion,

                        $datos['nombre_producto'],

                        (float) $datos['precio'],

                        (float) $datos['peso'],

                        $dimensiones,

                        (int) $datos['stock']
                    );


                    mensajeFlash(
                        'exito',
                        'Producto modificado correctamente.'
                    );


                    redirigir(
                        'producto.php'
                    );
                }
            }

        }

        catch (PDOException $e)
        {

            /*
             * Llave foránea.
             */
            if (
                $e->getCode() === '23503'
            )
            {
                $errores['general'] =
                    'El proveedor o anaquel seleccionado no existe.';
            }

            /*
             * Restricción CHECK.
             */
            elseif (
                $e->getCode() === '23514'
            )
            {
                $errores['general'] =
                    'Algún dato no cumple con las restricciones de la base de datos.';
            }
            elseif ($e->getCode() === '23505')
            {
                $errores['id_ubicacion'] =
                    'El anaquel seleccionado ya está ocupado por otro producto.';
            }
            else
            {
                $errores['general'] =
                    'Error al guardar el producto: '
                    .
                    $e->getMessage();
            }
        }
    }
}



/*
 * ============================================================
 * DATOS QUE NECESITA LA VISTA
 * ============================================================
 */

$csrf =
    tokenCsrf();


$proveedores =
    Producto::listarProveedores();


$anaqueles =
    Producto::listarAnaqueles();


$productos =
    Producto::listar();


$titulo =
    'Producto';


/*
 * Cargamos la pantalla de Producto.
 */
require __DIR__
    .
    '/../vistas/producto_vista.php';