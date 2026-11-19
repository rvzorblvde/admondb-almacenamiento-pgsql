<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Transporte.php';

iniciarSesion();

/** Normalización de los datos */
function datosDelFormulario(array $post): array {
    $id = filter_var($post['id_transporte'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    $estado = trim((string) ($post['estado'] ?? ''));

    if ($id === false)
        $estado = 'Disponible';

    return [
        'id_transporte' => $id === false ? null : $id,
        'placa' => mb_strtoupper(trim((string) ($post['placa'] ?? ''))),
        'tipo_unidad' => trim((string) ($post['tipo_unidad'] ?? '')),
        'marca' => trim((string) ($post['marca'] ?? '')),
        'modelo' => trim((string) ($post['modelo'] ?? '')),
        'estado' => $estado
    ];
}

/** Validación de los datos */
function validarTransporte(array $d): array {
    $errores = [];

    // Valida la placa
    if ($d['placa'] === '')
        $errores['placa'] = 'La placa es obligatoria.';
    elseif (mb_strlen($d['placa']) !== 6)
        $errores['placa'] = 'La placa debe tener exactamente 6 caracteres.';
    elseif (!preg_match('/^[A-Z0-9]{6}$/', $d['placa']))
        $errores['placa'] = 'La placa solo puede contener letras y números.';
    elseif (Transporte::existePlaca($d['placa'], $d['id_transporte']))
        $errores['placa'] = 'Ya existe un transporte con esa placa.';

    // Valida el tipo de unidad
    if (!array_key_exists($d['tipo_unidad'], Transporte::UNIDADES))
        $errores['tipo_unidad'] = 'Tipo de unidad no válido.';
    else {
        // Valida que la marca y modelo correspondan al tipo de unidad
        $unidad = Transporte::UNIDADES[$d['tipo_unidad']];

        if ($d['marca'] !== $unidad['marca'])
            $errores['marca'] = 'La marca no corresponde al tipo de unidad.';

        if ($d['modelo'] !== $unidad['modelo'])
            $errores['modelo'] = 'El modelo no corresponde al tipo de unidad.';
    }

    // Valida el estado
    if (!in_array($d['estado'], Transporte::ESTADOS, true))
        $errores['estado'] = 'Estado no válido.';

    return $errores;
}

/** Guarda el transporte y devuelve errores en caso de que los haya. */
function guardarTransporte(array $datos): array {
    try {
        $errores = validarTransporte($datos);

        if (!empty($errores))
            return $errores;

        if ($datos['id_transporte'] === null) {
            Transporte::insertar($datos['placa'], $datos['tipo_unidad'], $datos['marca'], $datos['modelo']);

            mensajeFlash('exito', 'Transporte registrado correctamente.');
        } else {
            $filas = Transporte::actualizar($datos['id_transporte'], $datos['placa'], $datos['tipo_unidad'], $datos['marca'], $datos['modelo'], $datos['estado']);

            if ($filas === 0)
                mensajeFlash('error', 'El transporte que se desea modificar ya no existe.');
            else
                mensajeFlash('exito', 'Transporte actualizado correctamente.');
        }

    } catch (PDOException $e) {
        // 23505 = violación de restricción única
        if ($e->getCode() === '23505') {
            if (str_contains($e->getMessage(), 'uq_transporte_placa'))
                return ['placa' => 'Ya existe un transporte con esa placa.'];

            return ['general' => 'Ya existe un registro con esos datos.'];
        }

        // 23514 = violación de CHECK
        if ($e->getCode() === '23514')
            return ['general' => 'Algún dato no cumple con las reglas de la Base de Datos.'];

        error_log('Error al guardar transporte: ' . $e->getMessage());

        return ['general' => 'No se pudo guardar el transporte. Inténtelo de nuevo.'];
    }

    redirigir('transporte.php');
}

/** Eliminar un transporte */
function eliminarTransporte(?int $id): never {
    if ($id === null) {
        mensajeFlash('error', 'Identificador de transporte no válido.');
        redirigir('transporte.php');
    }

    try {
        $filas = Transporte::eliminar($id);

        if ($filas === 0)
            mensajeFlash('error', 'El transporte no existe o ya fue eliminado.');
        else
            mensajeFlash('exito', 'Transporte eliminado correctamente.');

    } catch (PDOException $e) {
        // 23503 = violación de llave foránea
        if ($e->getCode() === '23503')
            mensajeFlash('error', 'No se puede eliminar: el transporte tiene rutas asociadas.');
        else {
            error_log('Error al eliminar un transporte: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el transporte.');
        }
    }

    redirigir('transporte.php');
}

/** Datos iniciales del formulario */
$errores = [];

$datos = [
    'id_transporte' => null,
    'placa' => '',
    'tipo_unidad' => '',
    'marca' => '',
    'modelo' => '',
    'estado' => 'Disponible'
];

$estados = Transporte::ESTADOS;
$unidades = Transporte::UNIDADES;
$flash = null;

/** Procesar las peticiones del formulario */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('transporte.php');
    }

    $accion = $_POST['accion'] ?? '';

    // Eliminar
    if ($accion === 'eliminar') {
        $id = filter_var($_POST['id_transporte'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        eliminarTransporte($id === false ? null : $id);
    }

    // Registrar o modificar
    if ($accion === 'guardar') {
        $datos = array_merge($datos, datosDelFormulario($_POST));

        if (($_POST['id_transporte'] ?? '') !== '' && $datos['id_transporte'] === null)
            $errores['general'] = 'Identificador de transporte no válido.';
        else {
            if ($datos['id_transporte'] !== null) {
                $transporte = Transporte::buscarPorId($datos['id_transporte']);

                if ($transporte === null)
                    $errores['general'] = 'El transporte que deseas modificar ya no existe.';
            }

            if (empty($errores))
                $errores = guardarTransporte($datos);
        }

    } else
        redirigir('transporte.php');

} else
    $flash = obtenerFlash();

/** Datos necesarios para mostrar la vista */
$esEdicion = $datos['id_transporte'] !== null;
$csrf = tokenCsrf();
$transportes = Transporte::listar();
$titulo = 'Transporte';

require __DIR__ . '/../vistas/transporte_vista.php';
