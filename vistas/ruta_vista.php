<?php require __DIR__ . '/parciales/header.php'; ?>

<link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

<div class="barra-acciones">
    <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
</div>

<div class="panel-doble">

    <!-- Formulario -->
    <section class="panel-formulario">
        <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar ruta' : 'Nueva ruta' ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form id="form-ruta" action="ruta.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id_ruta" value="<?= e($datos['id_ruta']) ?>">

            <!-- Transporte -->
            <label for="id_transporte">Seleccione un transporte</label>
            <select id="id_transporte" name="id_transporte" required>
                <option value="">Seleccione un transporte</option>

                <?php foreach ($transportes as $transporte): ?>
                    <option value="<?= e($transporte['id_transporte']) ?>"
                        <?= (string) $datos['id_transporte'] === (string) $transporte['id_transporte'] ? 'selected' : '' ?>>
                        <?= e($transporte['transporte_display']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errores['id_transporte'])): ?>
                <span class="texto-error"><?= e($errores['id_transporte']) ?></span>
            <?php endif; ?>

            <!-- Transportista -->
            <label for="id_transportista">Seleccione un transportista</label>
            <select id="id_transportista" name="id_transportista" required>
                <option value="">Seleccione un transportista</option>

                <?php foreach ($transportistas as $transportista): ?>
                    <option value="<?= e($transportista['id_transportista']) ?>"
                        <?= (string) $datos['id_transportista'] === (string) $transportista['id_transportista'] ? 'selected' : '' ?>>
                        <?= e($transportista['transportista_display']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errores['id_transportista'])): ?>
                <span class="texto-error"><?= e($errores['id_transportista']) ?></span>
            <?php endif; ?>

            
            <!-- Horarios -->
            <label for="hora_salida">Hora de salida</label>
            <input type="time" id="hora_salida" name="hora_salida"
                value="<?= e(substr($datos['hora_salida'], 0, 5)) ?>"
                step="60" required>

            <?php if (!empty($errores['hora_salida'])): ?>
                <span class="texto-error"><?= e($errores['hora_salida']) ?></span>
            <?php endif; ?>

            <label for="hora_estimada_llegada">Hora estimada de llegada</label>
            <input type="time" id="hora_estimada_llegada" name="hora_estimada_llegada"
                value="<?= e(substr($datos['hora_estimada_llegada'], 0, 5)) ?>"
                step="60" required>

            <?php if (!empty($errores['hora_estimada_llegada'])): ?>
                <span class="texto-error"><?= e($errores['hora_estimada_llegada']) ?></span>
            <?php endif; ?>

            <!-- Zona -->
            <label for="zona_ruta">Zona de la ruta</label>
            <input type="text" id="zona_ruta" name="zona_ruta"
                    value="<?= e($datos['zona_ruta']) ?>"
                    maxlength="200" required>

            <?php if (!empty($errores['zona_ruta'])): ?>
                <span class="texto-error"><?= e($errores['zona_ruta']) ?></span>
            <?php endif; ?>

            <!-- Distancia -->
            <label for="distancia_estimada">Distancia estimada (Km)</label>
            <input type="number" id="distancia_estimada" name="distancia_estimada"
                    value="<?= e($datos['distancia_estimada']) ?>"
                    min="1.01" max="99999999.99" step="0.01" required>

            <?php if (!empty($errores['distancia_estimada'])): ?>
                <span class="texto-error"><?= e($errores['distancia_estimada']) ?></span>
            <?php endif; ?>

            <!-- Nombre -->
            <label for="nombre_ruta">Nombre de la ruta</label>
            <input type="text" id="nombre_ruta" name="nombre_ruta"
                    value="<?= e($datos['nombre_ruta']) ?>"
                    maxlength="200" required>

            <?php if (!empty($errores['nombre_ruta'])): ?>
                <span class="texto-error"><?= e($errores['nombre_ruta']) ?></span>
            <?php endif; ?>

            <!-- Fecha -->
            <label for="fecha_ruta">Fecha de la ruta</label>
            <input type="date" id="fecha_ruta" name="fecha_ruta"
                    value="<?= e($datos['fecha_ruta']) ?>" required>

            <?php if (!empty($errores['fecha_ruta'])): ?>
                <span class="texto-error"><?= e($errores['fecha_ruta']) ?></span>
            <?php endif; ?>

            <!-- Estado: solo al modificar -->
            <div id="contenedor-estado" <?= $esEdicion ? '' : 'hidden' ?>>
                <label for="estado_ruta">Estado de la ruta</label>
                <select id="estado_ruta" name="estado_ruta" <?= $esEdicion ? '' : 'disabled' ?>>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= e($estado) ?>"
                            <?= $datos['estado_ruta'] === $estado ? 'selected' : '' ?>>
                            <?= e($estado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (!empty($errores['estado_ruta'])): ?>
                    <span class="texto-error"><?= e($errores['estado_ruta']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Acciones -->
            <div class="barra-acciones">
                <button type="submit" name="accion" value="guardar"
                        id="btn-guardar" class="boton-principal">
                    <?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?>
                </button>

                <button type="submit" name="accion" value="eliminar"
                        id="btn-eliminar" class="boton-peligro"
                        formnovalidate <?= $esEdicion ? '' : 'hidden' ?>>
                    Eliminar
                </button>

                <button type="button" id="btn-cancelar" class="boton-secundario"
                        <?= $esEdicion ? '' : 'hidden' ?>>
                    Cancelar
                </button>
            </div>
        </form>
    </section>

    <!-- Tabla de rutas -->
    <section class="panel-lista">
        <h2>Lista de rutas</h2>
        <p class="ayuda">Selecciona un registro para modificarlo o eliminarlo.</p>

        <?php if (empty($rutas)): ?>
            <p class="vacio">Todavía no hay rutas registradas.</p>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table id="tabla-rutas" class="tabla-seleccionable">
                    <thead>
                        <tr>
                            <th>ID Ruta</th>
                            <th>Transporte</th>
                            <th>Nombre transportista</th>
                            <th>Hora de salida</th>
                            <th>Hora estimada de llegada</th>
                            <th>Zona de la ruta</th>
                            <th>Distancia estimada</th>
                            <th>Estado de la ruta</th>
                            <th>Nombre de la ruta</th>
                            <th>Fecha de la ruta</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rutas as $ruta): ?>
                            <tr tabindex="0"
                                class="<?= (string) $datos['id_ruta'] === (string) $ruta['id_ruta'] ? 'fila-seleccionada' : '' ?>"
                                data-id="<?= e($ruta['id_ruta']) ?>"
                                data-transporte="<?= e($ruta['id_transporte']) ?>"
                                data-transportista="<?= e($ruta['id_transportista']) ?>"
                                data-salida="<?= e(substr($ruta['hora_salida'], 0, 5)) ?>"
                                data-llegada="<?= e(substr($ruta['hora_estimada_llegada'], 0, 5)) ?>"
                                data-zona="<?= e($ruta['zona_ruta']) ?>"
                                data-distancia="<?= e($ruta['distancia_estimada']) ?>"
                                data-estado="<?= e($ruta['estado_ruta']) ?>"
                                data-nombre="<?= e($ruta['nombre_ruta']) ?>"
                                data-fecha="<?= e($ruta['fecha_ruta']) ?>">

                                <td><?= e($ruta['id_ruta']) ?></td>
                                <td><?= e($ruta['transporte_display']) ?></td>
                                <td><?= e($ruta['transportista_display']) ?></td>
                                <td><?= e(substr($ruta['hora_salida'], 0, 5)) ?></td>
                                <td><?= e(substr($ruta['hora_estimada_llegada'], 0, 5)) ?></td>
                                <td><?= e($ruta['zona_ruta']) ?></td>
                                <td><?= e($ruta['distancia_estimada']) ?> Km</td>
                                <td><?= e($ruta['estado_ruta']) ?></td>
                                <td><?= e($ruta['nombre_ruta']) ?></td>
                                <td><?= e($ruta['fecha_ruta']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>

<script src="js/ruta.js?v=<?= @filemtime(__DIR__ . '/../public/js/ruta.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
