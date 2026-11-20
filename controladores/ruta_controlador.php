<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Ruta.php';

iniciarSesion();

// Valores iniciales del formulario.
function datosRutaVacios(): array {
    return [
        'id_ruta' => '',
        'id_transporte' => '',
        'id_transportista' => '',
        'hora_salida' => '',
        'hora_estimada_llegada' => '',
        'zona_ruta' => '',
        'distancia_estimada' => '',
        'estado_ruta' => 'Programada',
        'nombre_ruta' => '',
        'fecha_ruta' => date('Y-m-d')
    ];
}

// Obtener los datos enviados por el formulario.
function obtenerDatosRuta(): array {
    return [
        'id_ruta' => trim((string) ($_POST['id_ruta'] ?? '')),
        'id_transporte' => trim((string) ($_POST['id_transporte'] ?? '')),
        'id_transportista' => trim((string) ($_POST['id_transportista'] ?? '')),
        'hora_salida' => trim((string) ($_POST['hora_salida'] ?? '')),
        'hora_estimada_llegada' => trim((string) ($_POST['hora_estimada_llegada'] ?? '')),
        'zona_ruta' => trim((string) ($_POST['zona_ruta'] ?? '')),
        'distancia_estimada' => trim((string) ($_POST['distancia_estimada'] ?? '')),
        'estado_ruta' => trim((string) ($_POST['estado_ruta'] ?? 'Programada')),
        'nombre_ruta' => trim((string) ($_POST['nombre_ruta'] ?? '')),
        'fecha_ruta' => trim((string) ($_POST['fecha_ruta'] ?? ''))
    ];
}

// Validar la fecha, los horarios y los datos obligatorios.
function validarDatosRuta(array $datos, bool $esEdicion): array {
    $errores = [];

    if (!ctype_digit($datos['id_transporte']) || (int) $datos['id_transporte'] <= 0)
        $errores['id_transporte'] = 'Seleccione un transporte.';

    if (!ctype_digit($datos['id_transportista']) || (int) $datos['id_transportista'] <= 0)
        $errores['id_transportista'] = 'Seleccione un transportista.';

    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $datos['fecha_ruta']);

    if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_ruta'])
        $errores['fecha_ruta'] = 'Ingrese una fecha válida.';
    elseif ($fecha < new DateTimeImmutable('today'))
        $errores['fecha_ruta'] = 'La fecha de la ruta no puede ser anterior a hoy.';

    $formatoHora = '/^([01][0-9]|2[0-3]):[0-5][0-9]$/';

    if (!preg_match($formatoHora, $datos['hora_salida']))
        $errores['hora_salida'] = 'Ingrese una hora de salida válida.';

    if (!preg_match($formatoHora, $datos['hora_estimada_llegada']))
        $errores['hora_estimada_llegada'] = 'Ingrese una hora de llegada válida.';

    if (!isset($errores['hora_salida']) && !isset($errores['hora_estimada_llegada']) &&
        $datos['hora_estimada_llegada'] <= $datos['hora_salida'])
        $errores['hora_estimada_llegada'] = 'La hora de llegada debe ser posterior a la salida.';

    if ($datos['zona_ruta'] === '')
        $errores['zona_ruta'] = 'Ingrese la zona de la ruta.';
    elseif (mb_strlen($datos['zona_ruta']) > 200)
        $errores['zona_ruta'] = 'La zona no puede superar 200 caracteres.';

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $datos['distancia_estimada']) ||
        (float) $datos['distancia_estimada'] <= 1 ||
        (float) $datos['distancia_estimada'] > 99999999.99)
        $errores['distancia_estimada'] = 'Ingrese una distancia mayor a 1 km, con máximo dos decimales.';

    if ($datos['nombre_ruta'] === '')
        $errores['nombre_ruta'] = 'Ingrese el nombre de la ruta.';
    elseif (mb_strlen($datos['nombre_ruta']) > 200)
        $errores['nombre_ruta'] = 'El nombre no puede superar 200 caracteres.';

    if ($esEdicion && !in_array($datos['estado_ruta'], Ruta::ESTADOS, true))
        $errores['estado_ruta'] = 'Seleccione un estado válido.';

    return $errores;
}

// Registrar o modificar una ruta.
function guardarRuta(array $datos): array {
    $errores = [];
    $idRuta = 0;

    if ($datos['id_ruta'] !== '') {
        if (!ctype_digit($datos['id_ruta']) || (int) $datos['id_ruta'] <= 0)
            return ['general' => 'El ID de la ruta no es válido.'];

        $idRuta = (int) $datos['id_ruta'];

        if (!Ruta::buscarPorId($idRuta))
            return ['general' => 'La ruta que intenta modificar no existe.'];
    }

    $esEdicion = $idRuta > 0;

    if (!$esEdicion)
        $datos['estado_ruta'] = 'Programada';

    $errores = validarDatosRuta($datos, $esEdicion);

    if ($errores)
        return $errores;

    try {
        // Las rutas completadas o canceladas no reservan transporte ni transportista.
        if (in_array($datos['estado_ruta'], ['Programada', 'En curso'], true)) {
            $errorDisponibilidad = Ruta::validarDisponibilidad(
                (int) $datos['id_transporte'],
                (int) $datos['id_transportista'],
                $datos['fecha_ruta'],
                $datos['hora_salida'],
                $datos['hora_estimada_llegada'],
                $idRuta
            );

            if ($errorDisponibilidad !== '')
                return ['general' => $errorDisponibilidad];
        }

        if ($esEdicion) {
            $filas = Ruta::actualizar(
                $idRuta,
                (int) $datos['id_transporte'],
                (int) $datos['id_transportista'],
                $datos['hora_salida'],
                $datos['hora_estimada_llegada'],
                $datos['zona_ruta'],
                $datos['distancia_estimada'],
                $datos['estado_ruta'],
                $datos['nombre_ruta'],
                $datos['fecha_ruta']
            );

            if ($filas === 0)
                return ['general' => 'No se encontró la ruta para actualizar.'];

            mensajeFlash('exito', 'Ruta actualizada correctamente.');
        } else {
            Ruta::insertar(
                (int) $datos['id_transporte'],
                (int) $datos['id_transportista'],
                $datos['hora_salida'],
                $datos['hora_estimada_llegada'],
                $datos['zona_ruta'],
                $datos['distancia_estimada'],
                $datos['nombre_ruta'],
                $datos['fecha_ruta']
            );

            mensajeFlash('exito', 'Ruta registrada correctamente.');
        }

        redirigir('ruta.php');
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());

        if ($e->getCode() === '23503')
            return ['general' => 'El transporte o transportista no existe, o la ruta tiene registros asociados.'];

        if ($e->getCode() === '23514')
            return ['general' => 'Los datos no cumplen las restricciones de la base de datos.'];

        return ['general' => 'No se pudo guardar la ruta. Revise los datos e inténtelo nuevamente.'];
    }
}

// Eliminar una ruta.
function eliminarRuta(int $id): array {
    if ($id <= 0)
        return ['general' => 'Seleccione una ruta válida para eliminar.'];

    try {
        $filas = Ruta::eliminar($id);

        if ($filas === 0)
            return ['general' => 'La ruta que intenta eliminar no existe.'];

        mensajeFlash('exito', 'Ruta eliminada correctamente.');
        redirigir('ruta.php');
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());

        if ($e->getCode() === '23503')
            return ['general' => 'No se puede eliminar la ruta porque tiene registros asociados.'];

        return ['general' => 'Ocurrió un error al eliminar la ruta.'];
    }
}


// Preparar la información del formulario.
$datos = datosRutaVacios();
$errores = [];
$estados = Ruta::ESTADOS;
$flash = obtenerFlash();


// Procesar las acciones del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = obtenerDatosRuta();

    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        $errores['general'] = 'La solicitud no es válida. Actualice la página.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');

        if ($accion === 'guardar') {
            $errores = guardarRuta($datos);
        } elseif ($accion === 'eliminar') {
            $id = filter_var($datos['id_ruta'], FILTER_VALIDATE_INT);
            $errores = eliminarRuta($id === false ? 0 : $id);
        } else {
            $errores['general'] = 'Acción no válida.';
        }
    }
}


// Consultar los datos necesarios para la vista.
$rutas = Ruta::listar();
$transportes = Ruta::listarTransportes();
$transportistas = Ruta::listarTransportistas();

$esEdicion = $datos['id_ruta'] !== '';
$csrf = tokenCsrf();
$titulo = 'Administración de rutas';

require __DIR__ . '/../vistas/ruta_vista.php';