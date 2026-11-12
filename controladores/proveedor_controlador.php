<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Proveedor.php';

iniciarSesion();

function datosDelFormulario(array $post): array
{
    $id = filter_var($post['id_proveedor'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return [
        'id_proveedor'       => $id === false ? null : $id,
        'nombre_proveedor'   => trim((string) ($post['nombre_proveedor'] ?? '')),
        'nombre_empresa'     => trim((string) ($post['nombre_empresa'] ?? '')),
        'telefono_proveedor' => trim((string) ($post['telefono_proveedor'] ?? '')),
        'correo_empresa'     => mb_strtolower(trim((string) ($post['correo_empresa'] ?? ''))),
    ];
}

function validarProveedor(array $d): array
{
    $errores = [];

    $nombre = $d['nombre_proveedor'];
    if ($nombre === '') {
        $errores['nombre_proveedor'] = 'El nombre del proveedor es obligatorio.';
    } elseif (mb_strlen($nombre) > 200) {
        $errores['nombre_proveedor'] = 'El nombre no puede exceder los 200 caracteres.';
    }

    $empresa = $d['nombre_empresa'];
    if ($empresa === '') {
        $errores['nombre_empresa'] = 'El nombre de la empresa es obligatorio.';
    } elseif (mb_strlen($empresa) > 200) {
        $errores['nombre_empresa'] = 'El nombre de la empresa no puede exceder los 200 caracteres.';
    }

    $telefono = $d['telefono_proveedor'];
    if (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores['telefono_proveedor'] = 'El teléfono debe tener exactamente 10 dígitos.';
    } elseif (Proveedor::existeTelefono($telefono, $d['id_proveedor'])) {
        $errores['telefono_proveedor'] = 'Ya existe un proveedor registrado con este teléfono.';
    }

    $correo = $d['correo_empresa'];
    if ($correo === '') {
        $errores['correo_empresa'] = 'El correo de la empresa es obligatorio.';
    } elseif (mb_strlen($correo) > 255 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo_empresa'] = 'Ingresa un correo electrónico válido.';
    } elseif (Proveedor::existeCorreo($correo, $d['id_proveedor'])) {
        $errores['correo_empresa'] = 'Ya existe un proveedor registrado con este correo.';
    }

    return $errores;
}

function guardarProveedor(array $datos): array
{
    $errores = validarProveedor($datos);
    if ($errores) {
        return $errores;
    }

    try {
        if ($datos['id_proveedor'] === null) {
            Proveedor::insertar(
                $datos['nombre_proveedor'],
                $datos['nombre_empresa'],
                $datos['telefono_proveedor'],
                $datos['correo_empresa']
            );
            mensajeFlash('exito', 'Proveedor registrado correctamente.');
        } else {
            $filas = Proveedor::actualizar(
                $datos['id_proveedor'],
                $datos['nombre_proveedor'],
                $datos['nombre_empresa'],
                $datos['telefono_proveedor'],
                $datos['correo_empresa']
            );
            if ($filas === 0) {
                mensajeFlash('error', 'El proveedor que intentas modificar ya no existe.');
            } else {
                mensajeFlash('exito', 'Proveedor actualizado correctamente.');
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            return ['general' => 'El teléfono o correo ya están registrados en la base de datos.'];
        }
        if ($e->getCode() === '23514') {
            return ['general' => 'Algún dato no cumple las reglas de la base de datos (Ej: Teléfono).'];
        }
        error_log('Error al guardar proveedor: ' . $e->getMessage());
        return ['general' => 'No se pudo guardar el proveedor. Inténtalo de nuevo.'];
    }

    redirigir('proveedor.php');
}

function eliminarProveedor(?int $id): never
{
    if ($id === null) {
        mensajeFlash('error', 'Identificador de proveedor no válido.');
        redirigir('proveedor.php');
    }

    try {
        $filas = Proveedor::eliminar($id);
        if ($filas === 0) {
            mensajeFlash('error', 'El proveedor no existe o ya fue eliminado.');
        } else {
            mensajeFlash('exito', 'Proveedor eliminado correctamente.');
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23503') {
            mensajeFlash('error', 'No se puede eliminar: hay productos u otros registros asociados a este proveedor.');
        } else {
            error_log('Error al eliminar proveedor: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el proveedor.');
        }
    }

    redirigir('proveedor.php');
}

// ---------------------------------------------------------------------
// Enrutamiento simple por acción
// ---------------------------------------------------------------------
$errores = [];
$datos = [
    'id_proveedor' => null, 'nombre_proveedor' => '', 'nombre_empresa' => '',
    'telefono_proveedor' => '', 'correo_empresa' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('proveedor.php');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $id = filter_var($_POST['id_proveedor'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        eliminarProveedor($id === false ? null : $id);
    }

    if ($accion === 'guardar') {
        $datos = array_merge($datos, datosDelFormulario($_POST));
        $errores = guardarProveedor($datos);

        $esEdicion = $datos['id_proveedor'] !== null;
        $titulo = $esEdicion ? 'Editar Proveedor' : 'Nuevo Proveedor';
        $flash = null;
        $csrf = tokenCsrf();
        require __DIR__ . '/../vistas/proveedor_form.php';
        exit;
    }

    redirigir('proveedor.php');
}

$accion = $_GET['accion'] ?? 'lista';
$flash = obtenerFlash();
$csrf = tokenCsrf();

if ($accion === 'nuevo') {
    $esEdicion = false;
    $titulo = 'Nuevo Proveedor';
    require __DIR__ . '/../vistas/proveedor_form.php';
} elseif ($accion === 'editar') {
    $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $proveedor = $id === false ? null : Proveedor::buscarPorId($id);

    if ($proveedor === null) {
        mensajeFlash('error', 'El proveedor solicitado no existe.');
        redirigir('proveedor.php');
    }

    $datos = $proveedor;
    $esEdicion = true;
    $titulo = 'Editar Proveedor';
    require __DIR__ . '/../vistas/proveedor_form.php';
} else {
    $proveedores = Proveedor::listar();
    $titulo = 'Proveedores';
    require __DIR__ . '/../vistas/proveedor_lista.php';
}
