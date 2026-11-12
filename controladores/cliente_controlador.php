<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Cliente.php';

iniciarSesion();

/* Normalización de los datos */
function datosDelFormulario(array $post): array
{
    $id = filter_var($post['id_cliente'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    $estado = trim((string) ($post['estado'] ?? ''));
    if ($id === false) {
        // El estado de un cliente nuevo siempre es 'Activo'
        $estado = 'Activo';
    } elseif ($estado === '') {
        $estado = Cliente::buscarPorId($id)['estado'] ?? 'Activo';
    }

    return [
        'id_cliente'         => $id === false ? null : $id,
        'nombre_cliente'     => trim((string) ($post['nombre_cliente'] ?? '')),
        'telefono_cliente'   => trim((string) ($post['telefono_cliente'] ?? '')),
        'correo_electronico' => mb_strtolower(trim((string) ($post['correo_electronico'] ?? ''))),
        'estado'             => $estado,
    ];
}

function validarCliente(array $d): array
{
    $errores = [];

    // Validar el nombre y el estado
    if ($d['nombre_cliente'] === '') {
        $errores['nombre_cliente'] = 'El nombre es obligatorio.';
    }

    if (!in_array($d['estado'], Cliente::ESTADOS, true)) {
        $errores['estado'] = 'Estado no válido.';
    }

    // Validar restricciones de la BD (CHECK)
    if (!preg_match('/^[0-9]{10}$/', $d['telefono_cliente'])) {
        $errores['telefono_cliente'] = 'El teléfono debe tener exactamente 10 dígitos.';
    }

    // Validar restricciones únicas de la BD (UNIQUE)
    if (!filter_var($d['correo_electronico'], FILTER_VALIDATE_EMAIL)) {
        $errores['correo_electronico'] = 'Correo no válido.';
    } elseif (Cliente::existeCorreo($d['correo_electronico'], $d['id_cliente'])) {
        $errores['correo_electronico'] = 'Ya existe un cliente con este correo.';
    }

    return $errores;
}

/* Guardar el cliente y devolver errores en caso de que los haya */
function guardarCliente(array $datos): array
{
    // Atrapar el error con PHP y así tenemos un ID secuencial
    $errores = validarCliente($datos);
    if (!empty($errores)) {
        return $errores;
    }

    try {
        if ($datos['id_cliente'] === null) {
            Cliente::insertar(
                $datos['nombre_cliente'],
                $datos['telefono_cliente'],
                $datos['correo_electronico'],
                $datos['estado']
            );
            mensajeFlash('exito', 'Cliente registrado correctamente.');
        } else {
            $filas = Cliente::actualizar(
                $datos['id_cliente'],
                $datos['nombre_cliente'],
                $datos['telefono_cliente'],
                $datos['correo_electronico'],
                $datos['estado']
            );
            if ($filas === 0) {
                mensajeFlash('error', 'El cliente que intentas modificar ya no existe.');
            } else {
                mensajeFlash('exito', 'Cliente actualizado correctamente.');
            }
        }
    } catch (PDOException $e) {
        // 23505 = violación de restricción única
        if ($e->getCode() === '23505') {
            return ['correo_electronico' => 'Ya existe un cliente con ese correo.'];
        }
        // 23514 = violación de CHECK
        if ($e->getCode() === '23514') {
            return ['general' => 'Algún dato no cumple las reglas de la base de datos.'];
        }
        error_log('Error al guardar cliente: ' . $e->getMessage());
        return ['general' => 'No se pudo guardar el cliente. Inténtalo de nuevo.'];
    }

    redirigir('cliente.php');
}

function eliminarCliente(?int $id): never
{
    if ($id === null) {
        mensajeFlash('error', 'Identificador de cliente no válido.');
        redirigir('cliente.php');
    }

    try {
        $filas = Cliente::eliminar($id);
        if ($filas === 0) {
            mensajeFlash('error', 'El cliente no existe o ya fue eliminado.');
        } else {
            mensajeFlash('exito', 'Cliente eliminado correctamente.');
        }
    } catch (PDOException $e) {
        // 23503 = violación de llave foránea
        if ($e->getCode() === '23503') {
            mensajeFlash('error', 'No se puede eliminar: el cliente tiene registros asociados. Puedes marcarlo como Inactivo.');
        } else {
            error_log('Error al eliminar cliente: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el cliente.');
        }
    }

    redirigir('cliente.php');
}

$errores = [];
$datos = [
    'id_cliente' => null, 'nombre_cliente' => '', 'telefono_cliente' => '',
    'correo_electronico' => '', 'estado' => 'Activo', 'fecha_registro' => null,
];
$estados = Cliente::ESTADOS;
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('cliente.php');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $id = filter_var($_POST['id_cliente'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        eliminarCliente($id === false ? null : $id);
    }

    if ($accion === 'guardar') {
        $datos = array_merge($datos, datosDelFormulario($_POST));
        $errores = guardarCliente($datos);
    } else {
        redirigir('cliente.php');
    }
} else {
    $flash = obtenerFlash();
}

$esEdicion = $datos['id_cliente'] !== null;
$csrf = tokenCsrf();
$clientes = Cliente::listar();
$titulo = 'Clientes';

require __DIR__ . '/../vistas/cliente_vista.php';
