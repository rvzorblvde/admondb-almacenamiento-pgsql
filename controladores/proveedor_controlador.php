<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Proveedor.php';

iniciarSesion();

/** Normalización de los datos. Del correo solo se toma el usuario (antes del @); el dominio siempre sale de la empresa. */
function datosDelFormulario(array $post): array
{
    $id = filter_var($post['id_proveedor'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    $empresa = trim((string) ($post['nombre_empresa'] ?? ''));

    // Si se escribe un correo completo, solo se toma lo que va antes del @
    $usuario = Proveedor::usuarioDeCorreo(mb_strtolower(trim((string) ($post['correo_empresa'] ?? ''))));

    return [
        'id_proveedor'       => $id === false ? null : $id,
        'nombre_proveedor'   => trim((string) ($post['nombre_proveedor'] ?? '')),
        'nombre_empresa'     => $empresa,
        'telefono_proveedor' => trim((string) ($post['telefono_proveedor'] ?? '')),
        'usuario_correo'     => $usuario,
        'correo_empresa'     => Proveedor::armarCorreo($usuario, $empresa),
    ];
}

/** Validación de los datos. */
function validarProveedor(array $d): array
{
    $errores = [];

    // Valida el nombre del proveedor
    if ($d['nombre_proveedor'] === '')
        $errores['nombre_proveedor'] = 'El nombre del proveedor es obligatorio.';
    elseif (mb_strlen($d['nombre_proveedor']) < 2)
        $errores['nombre_proveedor'] = 'El nombre debe tener al menos 2 caracteres.';
    elseif (mb_strlen($d['nombre_proveedor']) > 200)
        $errores['nombre_proveedor'] = 'El nombre no puede ser mayor a 200 caracteres.';
    elseif (!preg_match("/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'\-]+$/u", $d['nombre_proveedor']))
        $errores['nombre_proveedor'] = 'El nombre solo puede contener letras, espacios, puntos, apóstrofes y guiones.';

    // Valida el nombre de la empresa (de él sale el dominio del correo)
    $dominio = Proveedor::dominioEmpresa($d['nombre_empresa']);
    if ($d['nombre_empresa'] === '')
        $errores['nombre_empresa'] = 'El nombre de la empresa es obligatorio.';
    elseif (mb_strlen($d['nombre_empresa']) < 2)
        $errores['nombre_empresa'] = 'El nombre de la empresa debe tener al menos 2 caracteres.';
    elseif (mb_strlen($d['nombre_empresa']) > 200)
        $errores['nombre_empresa'] = 'El nombre de la empresa no puede ser mayor a 200 caracteres.';
    elseif ($dominio === '')
        $errores['nombre_empresa'] = 'El nombre de la empresa debe contener letras o números.';

    // Validar restricciones de la BD (CHECK)
    if (!preg_match('/^[0-9]{10}$/', $d['telefono_proveedor']))
        $errores['telefono_proveedor'] = 'El teléfono debe tener exactamente 10 dígitos.';

    // Valida el usuario del correo y el correo completo
    if ($d['usuario_correo'] === '')
        $errores['correo_empresa'] = 'El correo de la empresa es obligatorio.';
    elseif (!preg_match('/^[a-z0-9]+([._-][a-z0-9]+)*$/', $d['usuario_correo']))
        $errores['correo_empresa'] = 'Antes del @ solo se permiten letras, números, puntos, guiones y guiones bajos.';
    elseif (mb_strlen($d['correo_empresa']) > 255 || !filter_var($d['correo_empresa'], FILTER_VALIDATE_EMAIL))
        $errores['correo_empresa'] = 'Correo no válido.';

    // Si el formato ya falló, no tiene caso consultar la BD
    if (!empty($errores))
        return $errores;

    // Registro idéntico a uno existente (mismo nombre, empresa, teléfono y correo)
    if (Proveedor::existeDuplicado($d['nombre_proveedor'], $d['nombre_empresa'], $d['telefono_proveedor'], $d['correo_empresa'], $d['id_proveedor'])) {
        $errores['general'] = 'Ya existe este registro.';
        return $errores;
    }

    // Validar restricciones únicas de la BD (UNIQUE)
    if (Proveedor::existeTelefono($d['telefono_proveedor'], $d['id_proveedor']))
        $errores['telefono_proveedor'] = 'Ya existe un proveedor con este teléfono.';

    if (Proveedor::existeCorreo($d['correo_empresa'], $d['id_proveedor']))
        $errores['correo_empresa'] = 'Ya existe un proveedor con este correo.';

    return $errores;
}

/** Guarda el proveedor y devuelve errores en caso de que los haya. */
function guardarProveedor(array $datos): array
{
    $errores = validarProveedor($datos);
    if (!empty($errores))
        return $errores;

    try {
        if ($datos['id_proveedor'] === null) {
            Proveedor::insertar($datos['nombre_proveedor'], $datos['nombre_empresa'], $datos['telefono_proveedor'], $datos['correo_empresa']);

            mensajeFlash('exito', 'Proveedor registrado correctamente.');
        } else {
            $filas = Proveedor::actualizar($datos['id_proveedor'], $datos['nombre_proveedor'], $datos['nombre_empresa'], $datos['telefono_proveedor'], $datos['correo_empresa']);

            if ($filas === 0)
                mensajeFlash('error', 'El proveedor que se desea modificar ya no existe.');
            else
                mensajeFlash('exito', 'Proveedor actualizado correctamente.');
        }
    } catch (PDOException $e) {
        // 23505 = violación de restricción única
        if ($e->getCode() === '23505') {
            if (str_contains($e->getMessage(), 'uq_proveedor_telefono'))
                return ['telefono_proveedor' => 'Ya existe un proveedor con este teléfono.'];

            if (str_contains($e->getMessage(), 'uq_proveedor_correo'))
                return ['correo_empresa' => 'Ya existe un proveedor con este correo.'];

            return ['general' => 'Ya existe un registro con esos datos.'];
        }

        // 23514 = violación de CHECK
        if ($e->getCode() === '23514') {
            if (str_contains($e->getMessage(), 'chk_proveedor_correo'))
                return ['correo_empresa' => 'El correo debe ser de la empresa: ...@' . Proveedor::dominioEmpresa($datos['nombre_empresa']) . '.com'];

            if (str_contains($e->getMessage(), 'chk_proveedor_telefono'))
                return ['telefono_proveedor' => 'El teléfono debe tener exactamente 10 dígitos.'];

            if (str_contains($e->getMessage(), 'chk_proveedor_nombre'))
                return ['nombre_proveedor' => 'El nombre debe tener al menos 2 caracteres.'];

            if (str_contains($e->getMessage(), 'chk_proveedor_empresa'))
                return ['nombre_empresa' => 'El nombre de la empresa debe tener al menos 2 caracteres.'];

            return ['general' => 'Algún dato no cumple con las reglas de la Base de Datos.'];
        }

        error_log('Error al guardar proveedor: ' . $e->getMessage());

        return ['general' => 'No se pudo guardar el proveedor. Inténtelo de nuevo.'];
    }

    redirigir('proveedor.php');
}

/** Eliminar el proveedor. */
function eliminarProveedor(?int $id): never
{
    if ($id === null) {
        mensajeFlash('error', 'Identificador de proveedor no válido.');
        redirigir('proveedor.php');
    }

    try {
        $filas = Proveedor::eliminar($id);

        if ($filas === 0)
            mensajeFlash('error', 'El proveedor no existe o ya fue eliminado.');
        else
            mensajeFlash('exito', 'Proveedor eliminado correctamente.');
    } catch (PDOException $e) {
        // 23503 = violación de la llave foránea
        if ($e->getCode() === '23503')
            mensajeFlash('error', 'No se puede eliminar: el proveedor tiene productos asociados.');
        else {
            error_log('Error al eliminar proveedor: ' . $e->getMessage());
            mensajeFlash('error', 'No se pudo eliminar el proveedor.');
        }
    }

    redirigir('proveedor.php');
}

/** Datos iniciales del formulario. */
$errores = [];

$datos = ['id_proveedor' => null, 'nombre_proveedor' => '', 'nombre_empresa' => '', 'telefono_proveedor' => '', 'usuario_correo' => '', 'correo_empresa' => ''];

$flash = null;

/** Procesar las peticiones del formulario. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('proveedor.php');
    }

    $accion = $_POST['accion'] ?? '';

    // Eliminar
    if ($accion === 'eliminar') {
        $id = filter_var($_POST['id_proveedor'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        eliminarProveedor($id === false ? null : $id);
    }

    // Registrar o modificar
    if ($accion === 'guardar') {
        $datos = array_merge($datos, datosDelFormulario($_POST));

        if (($_POST['id_proveedor'] ?? '') !== '' && $datos['id_proveedor'] === null)
            $errores['general'] = 'Identificador de proveedor no válido.';
        else
            $errores = guardarProveedor($datos);
    } else
        redirigir('proveedor.php');

} else
    $flash = obtenerFlash();

/** Datos necesarios para mostrar la vista. */
$esEdicion = $datos['id_proveedor'] !== null;
$dominio = Proveedor::dominioEmpresa($datos['nombre_empresa']);
$csrf = tokenCsrf();
$proveedores = Proveedor::listar();
$titulo = 'Proveedor';

require __DIR__ . '/../vistas/proveedor_vista.php';
