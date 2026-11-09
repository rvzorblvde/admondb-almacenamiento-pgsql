<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Cliente.php';

iniciarSesion();

/** Toma los datos crudos del formulario y los normaliza. */
function datosDelFormulario(array $post): array
{
    $id = filter_var($post['id_cliente'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return [
        'id_cliente'         => $id === false ? null : $id,
        'nombre_cliente'     => trim((string) ($post['nombre_cliente'] ?? '')),
        'telefono_cliente'   => trim((string) ($post['telefono_cliente'] ?? '')),
        'correo_electronico' => mb_strtolower(trim((string) ($post['correo_electronico'] ?? ''))),
        'estado'             => trim((string) ($post['estado'] ?? '')),
    ];
}

/** Devuelve un arreglo campo => mensaje. Vacío si todo es válido. */
function validarCliente(array $d): array
{
    $errores = [];

    $nombre = $d['nombre_cliente'];
    if ($nombre === '') {
        $errores['nombre_cliente'] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 255) {
        $errores['nombre_cliente'] = 'El nombre debe tener entre 2 y 255 caracteres.';
    } elseif (!preg_match('/^\p{L}[\p{L} .\'\-]*$/u', $nombre)) {
        $errores['nombre_cliente'] = 'El nombre solo admite letras, espacios, puntos, apóstrofes y guiones.';
    }

    if (!preg_match('/^[0-9]{10}$/', $d['telefono_cliente'])) {
        $errores['telefono_cliente'] = 'El teléfono debe tener exactamente 10 dígitos.';
    }

    $correo = $d['correo_electronico'];
    if ($correo === '') {
        $errores['correo_electronico'] = 'El correo electrónico es obligatorio.';
    } elseif (mb_strlen($correo) > 255 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo_electronico'] = 'Ingresa un correo electrónico válido.';
    } elseif (Cliente::existeCorreo($correo, $d['id_cliente'])) {
        $errores['correo_electronico'] = 'Ya existe un cliente con ese correo.';
    }

    if (!in_array($d['estado'], Cliente::ESTADOS, true)) {
        $errores['estado'] = 'Selecciona un estado válido.';
    }

    return $errores;
}

/** Procesa el guardado (alta o modificación). Devuelve los errores si los hay. */
function guardarCliente(array $datos): array
{
    $errores = validarCliente($datos);
    if ($errores) {
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
        // 23505 = violación de restricción única (carrera con otro usuario)
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
        // 23503 = violación de llave foránea (hay registros que dependen del cliente)
        if ($e->getCode() === '23503') {
            mensajeFlash('error', 'No se puede eliminar: el cliente tiene registros asociados. Puedes marcarlo como Inactivo.');
        } else {
            error_log('Error al eliminar cliente: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el cliente.');
        }
    }

    redirigir('cliente.php');
}

// ---------------------------------------------------------------------
// Enrutamiento simple por acción
// ---------------------------------------------------------------------
$errores = [];
$datos = [
    'id_cliente' => null, 'nombre_cliente' => '', 'telefono_cliente' => '',
    'correo_electronico' => '', 'estado' => 'Activo', 'fecha_registro' => null,
];
$estados = Cliente::ESTADOS;

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
        $errores = guardarCliente($datos);   // si todo sale bien, redirige y no continúa

        // Con errores se vuelve a mostrar el formulario con lo que escribió la persona
        $esEdicion = $datos['id_cliente'] !== null;
        $titulo = $esEdicion ? 'Editar cliente' : 'Nuevo cliente';
        $flash = null;
        $csrf = tokenCsrf();
        require __DIR__ . '/../vistas/cliente_form.php';
        exit;
    }

    redirigir('cliente.php');
}

$accion = $_GET['accion'] ?? 'lista';
$flash = obtenerFlash();
$csrf = tokenCsrf();

if ($accion === 'nuevo') {
    $esEdicion = false;
    $titulo = 'Nuevo cliente';
    require __DIR__ . '/../vistas/cliente_form.php';
} elseif ($accion === 'editar') {
    $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $cliente = $id === false ? null : Cliente::buscarPorId($id);

    if ($cliente === null) {
        mensajeFlash('error', 'El cliente solicitado no existe.');
        redirigir('cliente.php');
    }

    $datos = $cliente;
    $esEdicion = true;
    $titulo = 'Editar cliente';
    require __DIR__ . '/../vistas/cliente_form.php';
} else {
    $clientes = Cliente::listar();
    $titulo = 'Clientes';
    require __DIR__ . '/../vistas/cliente_lista.php';
}
