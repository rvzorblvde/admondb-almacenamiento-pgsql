<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../modelos/DomicilioCliente.php';

iniciarSesion();

function idValido(mixed $valor): ?int
{
    $id = filter_var($valor ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function validarDomicilio(string $domicilio): array
{
    $errores = [];

    if ($domicilio === '') {
        $errores['domicilio'] = 'El domicilio es obligatorio.';
    } elseif (mb_strlen($domicilio) < 5) {
        $errores['domicilio'] = 'El domicilio debe tener al menos 5 caracteres.';
    } elseif (mb_strlen($domicilio) > DomicilioCliente::LONGITUD_MAXIMA) {
        // VARCHAR(200) en la BD
        $errores['domicilio'] = 'El domicilio no puede exceder ' . DomicilioCliente::LONGITUD_MAXIMA . ' caracteres.';
    }

    return $errores;
}

/* Guardar el domicilio y devolver errores en caso de que los haya */
function guardarDomicilio(int $idCliente, ?int $idDomicilio, string $domicilio): array
{
    $errores = validarDomicilio($domicilio);
    if (!empty($errores)) {
        return $errores;
    }

    try {
        if ($idDomicilio === null) {
            DomicilioCliente::insertar($idCliente, $domicilio);
            mensajeFlash('exito', 'Domicilio registrado correctamente.');
        } elseif (DomicilioCliente::actualizar($idDomicilio, $idCliente, $domicilio) === 0) {
            mensajeFlash('error', 'El domicilio que intentas modificar ya no existe.');
        } else {
            mensajeFlash('exito', 'Domicilio actualizado correctamente.');
        }
    } catch (PDOException $e) {
        // 23503 = violación de llave foránea (el cliente fue eliminado mientras tanto)
        if ($e->getCode() === '23503') {
            mensajeFlash('error', 'El cliente ya no existe.');
            redirigir('domicilio.php');
        }
        error_log('Error al guardar domicilio: ' . $e->getMessage());
        return ['general' => 'No se pudo guardar el domicilio. Inténtalo de nuevo.'];
    }

    redirigir('domicilio.php?id_cliente=' . $idCliente);
}

function eliminarDomicilio(int $idCliente, ?int $idDomicilio): never
{
    if ($idDomicilio === null) {
        mensajeFlash('error', 'Identificador de domicilio no válido.');
    } else {
        try {
            if (DomicilioCliente::eliminar($idDomicilio, $idCliente) === 0) {
                mensajeFlash('error', 'El domicilio no existe o ya fue eliminado.');
            } else {
                mensajeFlash('exito', 'Domicilio eliminado correctamente.');
            }
        } catch (PDOException $e) {
            error_log('Error al eliminar domicilio: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el domicilio.');
        }
    }

    redirigir('domicilio.php?id_cliente=' . $idCliente);
}

/* ===== Cliente seleccionado en el dropdown: llega por GET (ver) o por POST (guardar/eliminar) ===== */
$esPost    = $_SERVER['REQUEST_METHOD'] === 'POST';
$valorId   = $esPost ? ($_POST['id_cliente'] ?? null) : ($_GET['id_cliente'] ?? null);
$idCliente = idValido($valorId);
$cliente   = $idCliente === null ? null : Cliente::buscarPorId($idCliente);

// Se mandó un cliente pero no existe (o al guardar no se eligió ninguno)
if ($cliente === null && ($esPost || ($valorId !== null && $valorId !== ''))) {
    mensajeFlash('error', 'Selecciona un cliente válido.');
    redirigir('domicilio.php');
}

$errores = [];
$datos = ['id_domicilio' => null, 'domicilio' => ''];
$flash = null;

if ($esPost) {
    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('domicilio.php?id_cliente=' . $idCliente);
    }

    $accion = $_POST['accion'] ?? '';
    $idDomicilio = idValido($_POST['id_domicilio'] ?? null);

    if ($accion === 'eliminar') {
        eliminarDomicilio($idCliente, $idDomicilio);
    }

    if ($accion === 'guardar') {
        // Normalizar: quitar espacios sobrantes y saltos de línea
        $domicilio = trim(preg_replace('/\s+/u', ' ', (string) ($_POST['domicilio'] ?? '')));
        $datos = ['id_domicilio' => $idDomicilio, 'domicilio' => $domicilio];
        $errores = guardarDomicilio($idCliente, $idDomicilio, $domicilio);
    } else {
        redirigir('domicilio.php?id_cliente=' . $idCliente);
    }
} else {
    $flash = obtenerFlash();
}

$esEdicion = $datos['id_domicilio'] !== null;
$csrf = tokenCsrf();
$clientes = Cliente::listar();
$domicilios = $cliente === null ? [] : DomicilioCliente::listarPorCliente($idCliente);
$longitudMaxima = DomicilioCliente::LONGITUD_MAXIMA;
$titulo = 'Domicilios de clientes';

require __DIR__ . '/../vistas/domicilio_vista.php';
