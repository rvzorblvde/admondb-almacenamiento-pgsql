<?php require __DIR__ . '/parciales/header.php'; ?>

<link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

<?php
// Fecha de la BD (YYYY-MM-DD) a DD/MM/YYYY; vacío si es NULL.
function fechaVista(?string $fecha): string {
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

// Hora de la BD (HH:MM:SS) a HH:MM; vacío si es NULL.
function horaVista(?string $hora): string {
    return $hora ? substr($hora, 0, 5) : '';
}
?>

<div class="barra-acciones">
    <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
</div>

<div class="panel-doble">

    <!-- Formulario -->
    <section class="panel-formulario">
        <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar entrega' : 'Nueva entrega' ?></h2>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form id="form-entrega" action="entrega.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id_entrega" value="<?= e($datos['id_entrega']) ?>">

            <!-- Ruta -->
            <label for="id_ruta">Seleccione una ruta</label>
            <select id="id_ruta" name="id_ruta" required>
                <option value="">Seleccione una ruta</option>

                <?php foreach ($rutas as $ruta): ?>
                    <option value="<?= e($ruta['id_ruta']) ?>"
                        <?= (string) $datos['id_ruta'] === (string) $ruta['id_ruta'] ? 'selected' : '' ?>>
                        <?= e($ruta['ruta_display']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errores['id_ruta'])): ?>
                <span class="texto-error"><?= e($errores['id_ruta']) ?></span>
            <?php endif; ?>

            <!-- Domicilio -->
            <label for="id_domicilio">Seleccione un domicilio</label>
            <select id="id_domicilio" name="id_domicilio" required>
                <option value="">Seleccione un domicilio</option>

                <?php foreach ($domicilios as $domicilio): ?>
                    <option value="<?= e($domicilio['id_domicilio']) ?>"
                        <?= (string) $datos['id_domicilio'] === (string) $domicilio['id_domicilio'] ? 'selected' : '' ?>>
                        <?= e($domicilio['domicilio_display']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errores['id_domicilio'])): ?>
                <span class="texto-error"><?= e($errores['id_domicilio']) ?></span>
            <?php endif; ?>

            <!-- Hora programada (la fecha programada se toma de la fecha de la ruta) -->
            <label for="hora_programada">Hora programada</label>
            <input type="time" id="hora_programada" name="hora_programada"
                value="<?= e(substr($datos['hora_programada'], 0, 5)) ?>"
                step="60" required>

            <?php if (!empty($errores['hora_programada'])): ?>
                <span class="texto-error"><?= e($errores['hora_programada']) ?></span>
            <?php endif; ?>

            <!-- Estado: solo al modificar -->
            <div id="contenedor-estado" <?= $esEdicion ? '' : 'hidden' ?>>
                <label for="estado_entrega">Estado de la entrega</label>
                <select id="estado_entrega" name="estado_entrega" <?= $esEdicion ? '' : 'disabled' ?>>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= e($estado) ?>"
                            <?= $datos['estado_entrega'] === $estado ? 'selected' : '' ?>>
                            <?= e($estado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (!empty($errores['estado_entrega'])): ?>
                    <span class="texto-error"><?= e($errores['estado_entrega']) ?></span>
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

    <!-- Tabla de entregas -->
    <section class="panel-lista">
        <h2>Lista de entregas</h2>
        <p class="ayuda">Selecciona un registro para modificarlo o eliminarlo.</p>

        <?php if (empty($entregas)): ?>
            <p class="vacio">Todavía no hay entregas registradas.</p>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table id="tabla-entregas" class="tabla-seleccionable">
                    <thead>
                        <tr>
                            <th>ID Entrega</th>
                            <th>Ruta asignada</th>
                            <th>Cliente y domicilio</th>
                            <th>Fecha programada</th>
                            <th>Hora programada</th>
                            <th>Fecha de entrega</th>
                            <th>Hora de entrega</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($entregas as $entrega): ?>
                            <tr tabindex="0"
                                class="<?= (string) $datos['id_entrega'] === (string) $entrega['id_entrega'] ? 'fila-seleccionada' : '' ?>"
                                data-id="<?= e($entrega['id_entrega']) ?>"
                                data-ruta="<?= e($entrega['id_ruta']) ?>"
                                data-domicilio="<?= e($entrega['id_domicilio']) ?>"
                                data-hora="<?= e(horaVista($entrega['hora_programada'])) ?>"
                                data-estado="<?= e($entrega['estado_entrega']) ?>">

                                <td><?= e($entrega['id_entrega']) ?></td>
                                <td><?= e($entrega['ruta_display']) ?></td>
                                <td><?= e($entrega['domicilio_display']) ?></td>
                                <td><?= e(fechaVista($entrega['fecha_programada'])) ?></td>
                                <td><?= e(horaVista($entrega['hora_programada'])) ?></td>
                                <td><?= e(fechaVista($entrega['fecha_entrega_real'])) ?></td>
                                <td><?= e(horaVista($entrega['hora_entrega_real'])) ?></td>
                                <td><?= e($entrega['estado_entrega']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>

<script src="js/entrega.js?v=<?= @filemtime(__DIR__ . '/../public/js/entrega.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
