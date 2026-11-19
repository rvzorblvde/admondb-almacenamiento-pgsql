<?php require __DIR__ . '/parciales/header.php'; ?>

<link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

<div class="barra-acciones">
    <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
</div>

<div class="panel-doble">

    <!-- Izquierda -> Formulario -->
    <section class="panel-formulario">
        <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar transporte' : 'Nuevo transporte' ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form id="form-transporte" action="transporte.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id_transporte" value="<?= e($datos['id_transporte']) ?>">

            <!-- Placa -->
            <label for="placa">Placa</label>
            <input type="text" id="placa" name="placa"
                    value="<?= e($datos['placa']) ?>"
                    required minlength="6" maxlength="6"
                    pattern="[A-Za-z0-9]{6}"
                    title="Exactamente 6 letras o números"
                    style="text-transform: uppercase;">

            <?php if (!empty($errores['placa'])): ?>
                <span class="texto-error"><?= e($errores['placa']) ?></span>
            <?php endif; ?>


            <!-- Tipo de unidad -->
            <label for="tipo_unidad">Tipo de unidad</label>
            <select id="tipo_unidad" name="tipo_unidad" required>
                <option value="">Seleccione un tipo de unidad</option>

                <?php foreach ($unidades as $tipo => $detalles): ?>
                    <option value="<?= e($tipo) ?>" <?= $datos['tipo_unidad'] === $tipo ? 'selected' : '' ?>>
                        <?= e($tipo) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errores['tipo_unidad'])): ?>
                <span class="texto-error"><?= e($errores['tipo_unidad']) ?></span>
            <?php endif; ?>


            <!-- Marca -->
            <label for="marca">Marca</label>
            <input type="text" id="marca" name="marca"
                    value="<?= e($datos['marca']) ?>"
                    placeholder="Se asigna automáticamente"
                    readonly required>

            <?php if (!empty($errores['marca'])): ?>
                <span class="texto-error"><?= e($errores['marca']) ?></span>
            <?php endif; ?>


            <!-- Modelo -->
            <label for="modelo">Modelo</label>
            <input type="text" id="modelo" name="modelo"
                    value="<?= e($datos['modelo']) ?>"
                    placeholder="Se asigna automáticamente"
                    readonly required>

            <?php if (!empty($errores['modelo'])): ?>
                <span class="texto-error"><?= e($errores['modelo']) ?></span>
            <?php endif; ?>


            <!-- Estado -->
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


            <!-- Botones -->
            <div class="barra-acciones">
                <button type="submit" id="btn-guardar" name="accion" value="guardar" class="boton-principal">
                    <?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?>
                </button>

                <button type="submit" id="btn-eliminar" name="accion" value="eliminar"
                        class="boton-peligro" formnovalidate <?= $esEdicion ? '' : 'hidden' ?>>
                    Eliminar
                </button>
            </div>
        </form>
    </section>


    <!-- Derecha -> Tabla -->
    <section class="panel-lista">
        <h2>Lista de transportes</h2>
        <p class="ayuda">Clic en un registro para editar.</p>

        <?php if (empty($transportes)): ?>
            <p class="vacio">Todavía no hay transportes registrados.</p>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table id="tabla-transportes" class="tabla-seleccionable">
                    <thead>
                        <tr>
                            <th>ID Transporte</th>
                            <th>Placa</th>
                            <th>Tipo de unidad</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($transportes as $t): ?>
                            <tr tabindex="0"
                                class="<?= (int) $datos['id_transporte'] === (int) $t['id_transporte'] ? 'fila-seleccionada' : '' ?>"
                                data-id="<?= (int) $t['id_transporte'] ?>"
                                data-placa="<?= e($t['placa']) ?>"
                                data-tipo="<?= e($t['tipo_unidad']) ?>"
                                data-marca="<?= e($t['marca']) ?>"
                                data-modelo="<?= e($t['modelo']) ?>"
                                data-estado="<?= e($t['estado']) ?>">

                                <td><?= e($t['id_transporte']) ?></td>
                                <td><?= e($t['placa']) ?></td>
                                <td><?= e($t['tipo_unidad']) ?></td>
                                <td><?= e($t['marca']) ?></td>
                                <td><?= e($t['modelo']) ?></td>
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

<script src="js/transporte.js?v=<?= @filemtime(__DIR__ . '/../public/js/transporte.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
