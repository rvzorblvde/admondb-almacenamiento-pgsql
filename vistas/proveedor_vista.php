<?php require __DIR__ . '/parciales/header.php'; ?>

<link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

<div class="barra-acciones">
    <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
</div>

<div class="panel-doble">
    <!-- Izquierda -> Formulario -->
    <section class="panel-formulario">
        <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar proveedor' : 'Nuevo proveedor' ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form id="form-proveedor" action="proveedor.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id_proveedor" value="<?= e($datos['id_proveedor']) ?>">

            <!-- Nombre del proveedor -->
            <label for="nombre_proveedor">Nombre del proveedor</label>
            <input type="text" id="nombre_proveedor" name="nombre_proveedor"
                value="<?= e($datos['nombre_proveedor']) ?>"
                required minlength="2" maxlength="200"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'\-]+"
                title="Solo letras, espacios, puntos, apóstrofes y guiones (mínimo 2 caracteres)"
                autocomplete="name">

            <?php if (!empty($errores['nombre_proveedor'])): ?>
                <span class="texto-error"><?= e($errores['nombre_proveedor']) ?></span>
            <?php endif; ?>

            <!-- Nombre de la empresa -->
            <label for="nombre_empresa">Nombre de la empresa</label>
            <input type="text" id="nombre_empresa" name="nombre_empresa"
                value="<?= e($datos['nombre_empresa']) ?>"
                required minlength="2" maxlength="200"
                autocomplete="organization">

            <?php if (!empty($errores['nombre_empresa'])): ?>
                <span class="texto-error"><?= e($errores['nombre_empresa']) ?></span>
            <?php endif; ?>

            <!-- Teléfono del proveedor -->
            <label for="telefono_proveedor">Teléfono (10 dígitos)</label>
            <input type="tel" id="telefono_proveedor" name="telefono_proveedor"
                value="<?= e($datos['telefono_proveedor']) ?>"
                required inputmode="numeric" maxlength="10" minlength="10"
                pattern="[0-9]{10}"
                title="Exactamente 10 dígitos, sin espacios ni guiones"
                autocomplete="tel-national">

            <?php if (!empty($errores['telefono_proveedor'])): ?>
                <span class="texto-error"><?= e($errores['telefono_proveedor']) ?></span>
            <?php endif; ?>

            <!-- Correo: se escribe el usuario y JavaScript agrega solo el @empresa.com -->
            <label for="correo_empresa">Correo de la empresa</label>
            <input type="text" id="correo_empresa" name="correo_empresa"
                value="<?= e($datos['usuario_correo'] !== '' && $dominio !== '' ? $datos['correo_empresa'] : $datos['usuario_correo']) ?>"
                placeholder="contacto@<?= e($dominio !== '' ? $dominio : 'empresa') ?>.com"
                required maxlength="255" inputmode="email"
                autocomplete="off" spellcheck="false" autocapitalize="off">
            <small class="nota-campo">Escribe solo el usuario; el @empresa.com se agrega solo.</small>

            <?php if (!empty($errores['correo_empresa'])): ?>
                <span class="texto-error"><?= e($errores['correo_empresa']) ?></span>
            <?php endif; ?>

            <!-- Botones del formulario -->
            <div class="barra-acciones">
                <!-- Lo registra si no hay un registro seleccionado, modifica si lo hay -->
                <button type="submit" id="btn-guardar" name="accion" value="guardar" class="boton-principal">
                    <?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?>
                </button>

                <!-- Solo es visible cuando hay un registro seleccionado -->
                <button type="submit" id="btn-eliminar" name="accion" value="eliminar"
                    class="boton-peligro" formnovalidate <?= $esEdicion ? '' : 'hidden' ?>>Eliminar</button>
            </div>
        </form>
    </section>

    <!-- Derecha -> Tabla de Datos -->
    <section class="panel-lista">
        <h2>Lista de proveedores</h2>
        <p class="ayuda">Clic en un registro para editar</p>

        <?php if (empty($proveedores)): ?>
            <p class="vacio">Todavía no hay proveedores registrados.</p>
        <?php else: ?>

            <div class="tabla-contenedor">
                <table id="tabla-proveedores" class="tabla-seleccionable">
                    <thead>
                        <tr>
                            <th>ID Proveedor</th>
                            <th>Nombre del Proveedor</th>
                            <th>Empresa</th>
                            <th>Teléfono</th>
                            <th>Correo de la empresa</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($proveedores as $p): ?>
                            <tr tabindex="0"
                                class="<?= (int) $datos['id_proveedor'] === (int) $p['id_proveedor'] ? 'fila-seleccionada' : '' ?>"
                                data-id="<?= (int) $p['id_proveedor'] ?>"
                                data-nombre="<?= e($p['nombre_proveedor']) ?>"
                                data-empresa="<?= e($p['nombre_empresa']) ?>"
                                data-telefono="<?= e($p['telefono_proveedor']) ?>"
                                data-usuario="<?= e(Proveedor::usuarioDeCorreo($p['correo_empresa'])) ?>">

                                <td><?= e($p['id_proveedor']) ?></td>
                                <td><?= e($p['nombre_proveedor']) ?></td>
                                <td><?= e($p['nombre_empresa']) ?></td>
                                <td><?= e($p['telefono_proveedor']) ?></td>
                                <td><?= e($p['correo_empresa']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="js/proveedor.js?v=<?= @filemtime(__DIR__ . '/../public/js/proveedor.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
