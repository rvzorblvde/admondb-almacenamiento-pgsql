<?php require __DIR__ . '/parciales/header.php'; ?>

<div class="barra-acciones">
    <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
</div>

<div class="panel-doble">
    <!-- Izquierda -> Formulario -->
    <section class="panel-formulario">
        <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar transportista' : 'Nuevo transportista' ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form id="form-transportista" action="transportista.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id_transportista" value="<?= e($datos['id_transportista']) ?>">

            <!-- Nombre del transportista -->
            <label for="nombre_transportista">Nombre completo</label>
            <input type="text" id="nombre_transportista" name="nombre_transportista"
                value="<?= e($datos['nombre_transportista']) ?>"
                required minlength="2" maxlength="200"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'\-]+"
                title="Solo letras, espacios, puntos, apóstrofes y guiones (mínimo 2 caracteres)"
                autocomplete="name">

            <?php if (!empty($errores['nombre_transportista'])): ?>
                <span class="texto-error"><?= e($errores['nombre_transportista']) ?></span>
            <?php endif; ?>

            <!-- Teléfono del transportista -->
            <label for="telefono_transportista">Teléfono (10 dígitos)</label>
            <input type="tel" id="telefono_transportista" name="telefono_transportista"
                value="<?= e($datos['telefono_transportista']) ?>"
                required inputmode="numeric" maxlength="10" minlength="10"
                pattern="[0-9]{10}"
                title="Exactamente 10 dígitos, sin espacios ni guiones"
                autocomplete="tel-national">

            <?php if (!empty($errores['telefono_transportista'])): ?>
                <span class="texto-error"><?= e($errores['telefono_transportista']) ?></span>
            <?php endif; ?>

            <!-- Correo electrónico -->
            <label for="correo_transportista">Correo electrónico</label>
            <input type="email" id="correo_transportista" name="correo_transportista"
                placeholder="ejemplo@correo.com"
                value="<?= e($datos['correo_transportista']) ?>"
                required maxlength="255" autocomplete="email">

            <?php if (!empty($errores['correo_transportista'])): ?>
                <span class="texto-error"><?= e($errores['correo_transportista']) ?></span>
            <?php endif; ?>

            
            <!-- Estado: solo visible cuando se modifica un transportista -->
            <div id="grupo-estado" <?= $esEdicion ? '' : 'hidden' ?>>
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required <?= $esEdicion ? '' : 'disabled' ?>>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= $datos['estado'] === $estado ? 'selected' : '' ?>>
                            <?= e($estado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (!empty($errores['estado'])): ?>
                    <span class="texto-error"><?= e($errores['estado']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Botones del formulario -->
            <div class="barra-acciones">
                <!-- Lo registra si no hay un registro seleccionado, modifica si lo hay -->
                <button type="submit" id="btn-guardar" name="accion" value="guardar" class="boton-principal">
                    <?= $esEdicion? 'Guardar cambios' : 'Registrar' ?>
                </button>

                <!-- Solo es visible cuando hay un registro seleccionado -->
                <button type="submit" id="btn-eliminar" name="accion" value="eliminar"
                    class="boton-peligro" formnovalidate <?= $esEdicion ? '' : 'hidden' ?>>Eliminar</button>
            </div>
        </form>
    </section>

    <!-- Derecha -> Tabla de Datos -->
    <section class="panel-lista">
        <h2>Lista de transportistas</h2>
        <p class="ayuda">Clic en un registro para editar</p>

        <?php if (empty($transportistas)): ?>
            <p class="vacio">Todavia no hay transportistas registrados</p>
        <?php else: ?>

            <div class="tabla-contenedor">
                <table id="tabla-transportistas" class="tabla-seleccionable">
                    <thead>
                        <tr>
                            <th>ID Transportista</th>
                            <th>Nombre Transportista</th>
                            <th>Teléfono</th>
                            <th>Correo Electrónico</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($transportistas as $t): ?>

                            <tr tabindex="0"
                                class="<?= (int) $datos['id_transportista'] === (int) $t['id_transportista'] ? 'fila-seleccionada' : '' ?>"
                                data-id="<?= (int) $t['id_transportista'] ?>"
                                data-nombre="<?= e($t['nombre_transportista']) ?>"
                                data-telefono="<?= e($t['telefono_transportista']) ?>"
                                data-correo="<?= e($t['correo_transportista']) ?>"
                                data-estado="<?= e($t['estado']) ?>">

                                <td><?= e($t['id_transportista']) ?></td>
                                <td><?= e($t['nombre_transportista']) ?></td>
                                <td><?= e($t['telefono_transportista']) ?></td>
                                <td><?= e($t['correo_transportista']) ?></td>

                                <td>
                                    <span class="etiqueta etiqueta-<?= $t['estado'] === 'Disponible' ? 'activo' : 'inactivo' ?>">
                                        <?= e($t['estado']) ?>
                                    </span>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="js/transportista.js?v=<?= @filemtime(__DIR__ . '/../public/js/transportista.js') ?:0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>