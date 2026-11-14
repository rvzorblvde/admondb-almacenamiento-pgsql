<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/Transportista.php';


iniciarSesion();

/** Normalización de los datos. */
function datosDelFormulario(array $post): array {
    $id = filter_var($post['id_transportista'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    $estado = trim((string) ($post['estado'] ?? ''));

    if ($id === false)
        $estado = 'Disponible';

    return ['id_transportista' => $id === false ? null : $id,
            'nombre_transportista' => trim((string) ($post['nombre_transportista'] ?? '')),
            'telefono_transportista' => trim((string) ($post['telefono_transportista'] ?? '' )),
            'correo_transportista' => mb_strtolower(trim((string) ($post['correo_transportista'] ?? ''))),
            'estado' => $estado,];
}

/** Validación de los datos. */
function validarTransportista(array $d) : array {
    $errores = [];

    // Valida el nombre
    if ($d['nombre_transportista'] === '')
        $errores['nombre_transportista'] = 'El nombre es obligatorio.';
    elseif (mb_strlen($d['nombre_transportista']) < 2)
        $errores['nombre_transportista'] = 'El nombre debe tener al menos 2 caracteres.';
    elseif (mb_strlen($d['nombre_transportista']) > 200)
        $errores['nombre_transportista'] = 'El nombre no puede ser mayor a 200 caracteres.';

    // Validación de los CHECK de la BD
    if (!preg_match('/^[0-9]{10}$/', $d['telefono_transportista']))
        $errores['telefono_transportista'] = 'El teléfono debe tener exactamente 10 dígitos.';
    elseif (Transportista::existeTelefono($d['telefono_transportista'], $d['id_transportista']))
        $errores['telefono_transportista'] = 'Ya existe un transportista con este teléfono.';

    // Validación de los UNIQUE de la BD
    if (!filter_var($d['correo_transportista'], FILTER_VALIDATE_EMAIL))
        $errores['correo_transportista'] = 'Correo no válido.';
    elseif (mb_strlen($d['correo_transportista']) > 255)
        $errores['correo_transportista'] = 'El correo no puede superar los 255 caracteres.';
    elseif (Transportista::existeCorreo($d['correo_transportista'], $d['id_transportista']))
        $errores['correo_transportista'] = 'Ya existe un transportista con ese correo.';

    // Validación del estado
    if (!in_array($d['estado'], Transportista::ESTADOS, true))
        $errores['estado'] = 'Estado no válido.';
    
    return $errores;
}

/** Guarda el transportista y devuelve errores en caso de que los haya. */
function guardarTransportista(array $datos): array {
    try {
        
        $errores = validarTransportista($datos);

        if (!empty($errores))
            return $errores;

            if ($datos['id_transportista'] === null) {
                Transportista::insertar($datos['nombre_transportista'], $datos['telefono_transportista'], $datos['correo_transportista']);

                mensajeFlash('exito', 'Transportista registrado correctamente.');
            } else {
                $filas = Transportista::actualizar($datos['id_transportista'], $datos['nombre_transportista'], $datos['telefono_transportista'], $datos['correo_transportista'], $datos['estado']);

                if ($filas === 0)
                    mensajeFlash('error', 'El transportista que se desea modificar ya no existe.');
                else
                    mensajeFlash('exito', 'Transportista actualizado correctamente,');
            } 
        } catch (PDOException $e) {
            // 23505 = violación de restricción única
            if ($e->getCode() === '23505') {
                if (str_contains($e->getMessage(), 'uq_transportista_telefono'))
                    return ['telefono_transportista' => 'Ya existe un transportista con este teléfono'];
                
                if (str_contains($e->getMessage(), 'uq_transportista_correo'))
                    return ['correo_transportista' => 'Ya existe un transportista con este correo.'];

                return ['general' => 'Ya existe un registro con esos datos.'];
            }

            // 23514 = violación de CHECK
            if ($e->getCode() === '23514')
                return ['general' => 'Algún dato no cumple con la reglas de la Base de Datos.'];
            
            error_log('Error al guardar transportista: ' . $e->getMessage());

            return ['general' => 'No se pudo guardar el transportista. Intentelo de nuevo.'];
        }
    
    redirigir('transportista.php');
}

/** Eliminar el transportista. */
function eliminarTransportista(?int $id): never {
    if ($id === null) {
        mensajeFlash('error', 'Identificador de transportista no válido.');
        redirigir('transportista.php');
    }

    try {
        $filas = Transportista::eliminar($id);

        if ($filas === 0)
            mensajeFlash('error', 'El transportista no existe o ya fue eliminado.');
        else
            mensajeFlash('exito', 'Transportista eliminado correctamente.');
    } catch (PDOException $e) {
        // 23503 = violación de la llave foránea
        if ($e->getCode() === '23503')
            mensajeFlash('error', 'No se puede eliminar: el transportista tiene rutas asociadas.');
        else {
            error_log('Error al eliminar transportista: ' . $e->getMessage());
            mensajeFlash('error', 'No se puede eliminar el transportista.');
        }
    }

    redirigir('transportista.php');
}

/** Datos iniciales del formulario. */
$errores = [];

$datos = ['id_transportista' => null, 'nombre_transportista' => '', 'telefono_transportista' => '', 'correo_transportista' => '', 'estado' => 'Disponible', 'fecha_contratacion' => null,];

$estados = Transportista::ESTADOS;
$flash = null;


/** Procesar las peticiones del formulario. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfValido((string) ($_POST['csrf'] ?? ''))) {
        mensajeFlash('error', 'La sesión expiró. Vuelve a intentarlo.');
        redirigir('transportista.php');
    }

    $accion = $_POST['accion'] ?? '';

    // Eliminar
    if ($accion === 'eliminar') {
        $id = filter_var($_POST['id_transportista'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        eliminarTransportista($id === false ? null : $id);
    }

    // Registrar o modificar
    if ($accion === 'guardar') {
        $datos = array_merge($datos, datosDelFormulario($_POST));

        if (($_POST['id_transportista'] ?? '') !== '' && $datos['id_transportista'] === null)
            $errores['general'] = 'Identificador de transportista no válido.';
        else {
            if ($datos['id_transportista'] !== null) {
                $transportista = Transportista::buscarPorId($datos['id_transportista']);

                if ($transportista !== null)
                    $datos['fecha_contratacion'] = $transportista['fecha_contratacion'];
            }

            $errores = guardarTransportista($datos);
        }

    } else
        redirigir('transportista.php');

} else
    $flash = obtenerFlash();

/** Datos necesarios para mostrar la vista. */
$esEdicion = $datos['id_transportista'] !== null;
$csrf = tokenCsrf();
$transportistas = Transportista::listar();
$titulo = 'Transportista';

require __DIR__ . '/../vistas/transportista_vista.php';