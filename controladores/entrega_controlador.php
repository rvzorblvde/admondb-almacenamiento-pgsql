<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Entrega.php';

iniciarSesion();

// Valores iniciales del formulario.
function datosEntregaVacios(): array {
    return [
        'id_entrega' => '',
        'id_ruta' => '',
        'id_domicilio' => '',
        'hora_programada' => '',
        'estado_entrega' => 'Pendiente'
    ];
}

// Obtener los datos enviados por el formulario.
function obtenerDatosEntrega(): array {
    return [
        'id_entrega' => trim((string) ($_POST['id_entrega'] ?? '')),
        'id_ruta' => trim((string) ($_POST['id_ruta'] ?? '')),
        'id_domicilio' => trim((string) ($_POST['id_domicilio'] ?? '')),
        'hora_programada' => trim((string) ($_POST['hora_programada'] ?? '')),
        'estado_entrega' => trim((string) ($_POST['estado_entrega'] ?? 'Pendiente'))
    ];
}

// Validar los datos obligatorios y su formato.
function validarDatosEntrega(array $datos, bool $esEdicion): array {
    $errores = [];

    if (!ctype_digit($datos['id_ruta']) || (int) $datos['id_ruta'] <= 0)
        $errores['id_ruta'] = 'Seleccione una ruta.';

    if (!ctype_digit($datos['id_domicilio']) || (int) $datos['id_domicilio'] <= 0)
        $errores['id_domicilio'] = 'Seleccione un domicilio.';

    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $datos['hora_programada']))
        $errores['hora_programada'] = 'Ingrese una hora programada válida.';

    if ($esEdicion && !in_array($datos['estado_entrega'], Entrega::ESTADOS, true))
        $errores['estado_entrega'] = 'Seleccione un estado válido.';

    return $errores;
}

// Validar la entrega contra la ruta y el domicilio de la BD.
// Devuelve [errores, fecha_programada]; la fecha programada se toma de la fecha de la ruta.
function validarRelacionesEntrega(array $datos, ?array $entregaActual): array {
    $idRuta = (int) $datos['id_ruta'];
    $idDomicilio = (int) $datos['id_domicilio'];
    $ruta = Entrega::buscarRuta($idRuta);

    if ($ruta === null)
        return [['id_ruta' => 'La ruta seleccionada no existe.'], ''];

    // Solo se exige ruta activa al registrar o al cambiar de ruta.
    $cambioRuta = $entregaActual === null || (int) $entregaActual['id_ruta'] !== $idRuta;

    if ($cambioRuta && !in_array($ruta['estado_ruta'], Entrega::ESTADOS_RUTA_ACTIVOS, true))
        return [['id_ruta' => 'La ruta seleccionada está ' . mb_strtolower($ruta['estado_ruta']) . ' y no admite entregas.'], ''];

    $salida = substr((string) $ruta['hora_salida'], 0, 5);
    $llegada = substr((string) $ruta['hora_estimada_llegada'], 0, 5);

    if ($datos['hora_programada'] < $salida || $datos['hora_programada'] > $llegada)
        return [['hora_programada' => "La hora debe estar dentro del horario de la ruta ($salida a $llegada)."], ''];

    if (!Entrega::existeDomicilio($idDomicilio))
        return [['id_domicilio' => 'El domicilio seleccionado no existe.'], ''];

    $idActual = $entregaActual === null ? 0 : (int) $entregaActual['id_entrega'];

    if (Entrega::existeEntregaEnRuta($idRuta, $idDomicilio, $idActual))
        return [['general' => 'Ese domicilio ya tiene una entrega en la ruta seleccionada.'], ''];

    return [[], (string) $ruta['fecha_ruta']];
}

// Registrar o modificar una entrega.
function guardarEntrega(array $datos): array {
    $entregaActual = null;

    if ($datos['id_entrega'] !== '') {
        if (!ctype_digit($datos['id_entrega']) || (int) $datos['id_entrega'] <= 0)
            return ['general' => 'El ID de la entrega no es válido.'];

        $entregaActual = Entrega::buscarPorId((int) $datos['id_entrega']);

        if ($entregaActual === null)
            return ['general' => 'La entrega que intenta modificar no existe.'];
    }

    $esEdicion = $entregaActual !== null;

    if (!$esEdicion)
        $datos['estado_entrega'] = 'Pendiente';

    $errores = validarDatosEntrega($datos, $esEdicion);

    if ($errores)
        return $errores;

    try {
        [$errores, $fechaProgramada] = validarRelacionesEntrega($datos, $entregaActual);

        if ($errores)
            return $errores;

        if ($esEdicion) {
            $filas = Entrega::actualizar(
                (int) $entregaActual['id_entrega'],
                (int) $datos['id_ruta'],
                (int) $datos['id_domicilio'],
                $fechaProgramada,
                $datos['hora_programada'],
                $datos['estado_entrega']
            );

            if ($filas === 0)
                return ['general' => 'No se encontró la entrega para actualizar.'];

            mensajeFlash('exito', 'Entrega actualizada correctamente.');
        } else {
            Entrega::insertar(
                (int) $datos['id_ruta'],
                (int) $datos['id_domicilio'],
                $fechaProgramada,
                $datos['hora_programada']
            );

            mensajeFlash('exito', 'Entrega registrada correctamente.');
        }

        redirigir('entrega.php');

    } catch (PDOException $e) {
        error_log($e->getMessage());

        if ($e->getCode() === '23503')
            return ['general' => 'La ruta o el domicilio ya no existen.'];

        if ($e->getCode() === '23514')
            return ['general' => 'Los datos no cumplen las restricciones de la base de datos.'];

        return ['general' => 'No se pudo guardar la entrega. Revise los datos e inténtelo nuevamente.'];
    }
}

// Eliminar una entrega.
function eliminarEntrega(int $id): array {
    if ($id <= 0)
        return ['general' => 'Seleccione una entrega válida para eliminar.'];

    try {
        if (Entrega::eliminar($id) === 0)
            return ['general' => 'La entrega que intenta eliminar no existe.'];

        mensajeFlash('exito', 'Entrega eliminada correctamente.');
        redirigir('entrega.php');

    } catch (PDOException $e) {
        error_log($e->getMessage());

        // 23503 = la entrega tiene productos en Detalle_Entrega
        if ($e->getCode() === '23503')
            return ['general' => 'No se puede eliminar la entrega porque tiene productos asociados.'];

        return ['general' => 'Ocurrió un error al eliminar la entrega.'];
    }
}


// Preparar la información del formulario.
$datos = datosEntregaVacios();
$errores = [];
$estados = Entrega::ESTADOS;
$flash = obtenerFlash();


// Procesar las acciones del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = obtenerDatosEntrega();

    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        $errores['general'] = 'La solicitud no es válida. Actualice la página.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');

        if ($accion === 'guardar') {
            $errores = guardarEntrega($datos);
        } elseif ($accion === 'eliminar') {
            $id = filter_var($datos['id_entrega'], FILTER_VALIDATE_INT);
            $errores = eliminarEntrega($id === false ? 0 : $id);
        } else {
            $errores['general'] = 'Acción no válida.';
        }
    }
}


// Consultar los datos necesarios para la vista.
$entregas = Entrega::listar();
$rutas = Entrega::listarRutas();
$domicilios = Entrega::listarDomicilios();

$esEdicion = $datos['id_entrega'] !== '';
$csrf = tokenCsrf();
$titulo = 'Entrega';

require __DIR__ . '/../vistas/entrega_vista.php';
