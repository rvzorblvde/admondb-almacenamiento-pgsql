<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Anaquel.php';


// Iniciamos sesión porque el proyecto usa mensajes flash
// y protección CSRF.
iniciarSesion();


/*
 * ============================================================
 * RECIBIR Y ORDENAR LOS DATOS DEL FORMULARIO
 * ============================================================
 */
function datosAnaquelFormulario(array $post): array
{
    // Si hay ID significa que estamos modificando.
    $id = filter_var(
        $post['id_ubicacion'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );


    // En un registro nuevo el estado siempre será Disponible.
    $estado = trim(
        (string) ($post['estado'] ?? '')
    );


    if ($id === false) {

        $estado = 'Disponible';

    } elseif ($estado === '') {

        // Si estamos modificando y no llegó el estado,
        // recuperamos el estado que ya tenía.
        $estado =
            Anaquel::buscarPorId((int) $id)['estado']
            ?? 'Disponible';
    }


    return [
        'id_ubicacion' =>
            $id === false ? null : (int) $id,

        'zona' =>
            trim((string) ($post['zona'] ?? '')),

        'pasillo' =>
            trim((string) ($post['pasillo'] ?? '')),

        'rack' =>
            trim((string) ($post['rack'] ?? '')),

        'nivel' =>
            trim((string) ($post['nivel'] ?? '')),

        'estado' =>
            $estado
    ];
}


/*
 * ============================================================
 * VALIDACIONES DEL ANAQUEL
 * ============================================================
 */
function validarAnaquel(array $d): array
{
    $errores = [];


    /*
     * En Visual los NumericUpDown se regresaban a 1.
     * Aquí hacemos algo parecido: deben ser enteros
     * mayores o iguales a 1.
     */

    if (
        filter_var(
            $d['zona'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        ) === false
    ) {
        $errores['zona'] =
            'La zona debe ser un número mayor o igual a 1.';
    }


    if (
        filter_var(
            $d['pasillo'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        ) === false
    ) {
        $errores['pasillo'] =
            'El pasillo debe ser un número mayor o igual a 1.';
    }


    if (
        filter_var(
            $d['rack'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        ) === false
    ) {
        $errores['rack'] =
            'El rack debe ser un número mayor o igual a 1.';
    }


    if (
        filter_var(
            $d['nivel'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        ) === false
    ) {
        $errores['nivel'] =
            'El nivel debe ser un número mayor o igual a 1.';
    }


    // Los únicos estados permitidos son los mismos
    // que teníamos en Visual.
    if (
        !in_array(
            $d['estado'],
            Anaquel::ESTADOS,
            true
        )
    ) {
        $errores['estado'] =
            'Estado no válido.';
    }


    // Si ya encontramos errores de formato,
    // no seguimos consultando PostgreSQL.
    if (!empty($errores)) {

        return $errores;
    }


    /*
     * Misma validación que ExisteZona() y
     * ExisteZonaEnOtroAnaquel() de Visual.
     */
    if (
        Anaquel::existeZona(
            (int) $d['zona'],
            $d['id_ubicacion']
        )
    ) {
        $errores['zona'] =
            $d['id_ubicacion'] === null
            ? 'Esta zona ya se encuentra registrada en el sistema.'
            : 'Otra ubicación ya utiliza esta zona.';
    }


    return $errores;
}


/*
 * ============================================================
 * INSERTAR O MODIFICAR
 * ============================================================
 */
function guardarAnaquel(array $datos): array
{
    $errores =
        validarAnaquel($datos);


    if (!empty($errores)) {

        return $errores;
    }


    try {

        /*
         * Si no existe ID, es un anaquel nuevo.
         */
        if ($datos['id_ubicacion'] === null) {

            Anaquel::insertar(
                (int) $datos['zona'],
                (int) $datos['pasillo'],
                (int) $datos['rack'],
                (int) $datos['nivel']
            );


            mensajeFlash(
                'exito',
                'Anaquel registrado correctamente.'
            );

        } else {

            /*
             * Si existe ID, estamos modificando.
             */
            $filas =
                Anaquel::actualizar(
                    (int) $datos['id_ubicacion'],
                    (int) $datos['zona'],
                    (int) $datos['pasillo'],
                    (int) $datos['rack'],
                    (int) $datos['nivel'],
                    $datos['estado']
                );


            if ($filas === 0) {

                mensajeFlash(
                    'error',
                    'El anaquel que intentas modificar ya no existe o no tuvo cambios.'
                );

            } else {

                mensajeFlash(
                    'exito',
                    'Anaquel actualizado correctamente.'
                );
            }
        }

    } catch (PDOException $e) {

        // 23505 = se intentó repetir un valor UNIQUE.
        if ($e->getCode() === '23505') {

            return [
                'zona' =>
                    'Esta zona ya se encuentra registrada.'
            ];
        }


        // 23514 = no pasó una restricción CHECK.
        if ($e->getCode() === '23514') {

            return [
                'general' =>
                    'Algún dato del anaquel no cumple las restricciones.'
            ];
        }


        error_log(
            'Error al guardar anaquel: ' .
            $e->getMessage()
        );


        return [
            'general' =>
                'No se pudo guardar el anaquel.'
        ];
    }


    redirigir('anaquel.php');
}


/*
 * ============================================================
 * ELIMINAR
 * ============================================================
 */
function eliminarAnaquel(?int $id): never
{
    if ($id === null) {

        mensajeFlash(
            'error',
            'Identificador de anaquel no válido.'
        );

        redirigir('anaquel.php');
    }


    try {

        $filas =
            Anaquel::eliminar($id);


        if ($filas === 0) {

            mensajeFlash(
                'error',
                'El anaquel no existe o ya fue eliminado.'
            );

        } else {

            mensajeFlash(
                'exito',
                'Anaquel eliminado correctamente.'
            );
        }

    } catch (PDOException $e) {

        /*
         * 23503 = llave foránea.
         * No podemos borrar un anaquel si un producto
         * todavía lo está utilizando.
         */
        if ($e->getCode() === '23503') {

            mensajeFlash(
                'error',
                'No se puede eliminar: hay productos guardados en este anaquel.'
            );

        } else {

            error_log(
                'Error al eliminar anaquel: ' .
                $e->getMessage()
            );

            mensajeFlash(
                'error',
                'No se pudo eliminar el anaquel.'
            );
        }
    }


    redirigir('anaquel.php');
}


/*
 * ============================================================
 * VALORES INICIALES DEL FORMULARIO
 * ============================================================
 */

$errores = [];

$datos = [
    'id_ubicacion' => null,
    'zona'         => '1',
    'pasillo'      => '1',
    'rack'         => '1',
    'nivel'        => '1',
    'estado'       => 'Disponible'
];

$estados =
    Anaquel::ESTADOS;

$flash = null;


/*
 * ============================================================
 * PROCESAR POST
 * ============================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Comprobamos el token del formulario.
    if (
        !csrfValido(
            (string) ($_POST['csrf'] ?? '')
        )
    ) {

        mensajeFlash(
            'error',
            'La sesión expiró. Vuelve a intentarlo.'
        );

        redirigir('anaquel.php');
    }


    $accion =
        $_POST['accion'] ?? '';


    /*
     * Botón Eliminar.
     */
    if ($accion === 'eliminar') {

        $id =
            filter_var(
                $_POST['id_ubicacion'] ?? '',
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );


        eliminarAnaquel(
            $id === false
            ? null
            : (int) $id
        );
    }


    /*
     * Botón Registrar / Guardar cambios.
     */
    if ($accion === 'guardar') {

        $datos =
            array_merge(
                $datos,
                datosAnaquelFormulario($_POST)
            );


        $errores =
            guardarAnaquel($datos);

    } else {

        redirigir('anaquel.php');
    }

} else {

    $flash =
        obtenerFlash();
}


// Si hay ID estamos modificando.
$esEdicion =
    $datos['id_ubicacion'] !== null;


// Generamos token CSRF.
$csrf =
    tokenCsrf();


// Traemos todos los anaqueles.
$anaqueles =
    Anaquel::listar();


$titulo =
    'Anaquel';


// Cargamos la vista.
require __DIR__ .
    '/../vistas/anaquel_vista.php';