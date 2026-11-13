<?php require __DIR__ . '/parciales/header.php'; ?>

        <!-- Reutiliza el layout de dos columnas de Cliente -->
        <link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

        <div class="barra-acciones">
            <a class="boton-secundario" href="index.html">&larr; Volver al inicio</a>
        </div>

        <!-- Selector de cliente: al cambiarlo se recarga la página con sus domicilios -->
        <form id="form-seleccion" class="selector-cliente" action="domicilio.php" method="GET">
            <label for="id_cliente_select">Cliente</label>
            <div class="selector-fila">
                <select id="id_cliente_select" name="id_cliente">
                    <option value="">-- Selecciona un cliente --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= (int) $c['id_cliente'] ?>"
                            <?= $cliente !== null && (int) $cliente['id_cliente'] === (int) $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= e($c['id_cliente']) ?> - <?= e($c['nombre_cliente']) ?><?= $c['estado'] !== 'Activo' ? ' (' . e($c['estado']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Solo se usa si JavaScript está desactivado -->
                <noscript><button type="submit" class="boton-secundario">Ver</button></noscript>
            </div>
        </form>

        <?php if ($cliente === null): ?>

            <p class="vacio">
                <?= empty($clientes)
                    ? 'No hay clientes registrados. Registra uno en la sección de Clientes.'
                    : 'Selecciona un cliente para ver y administrar sus domicilios.' ?>
            </p>

        <?php else: ?>

            <div class="panel-doble">

                <!-- Izquierda -> Formulario -->
                <section class="panel-formulario">
                    <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar domicilio' : 'Nuevo domicilio' ?></h2>

                    <?php if (!empty($errores['general'])): ?>
                        <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
                    <?php endif; ?>

                    <form id="form-domicilio" action="domicilio.php" method="POST">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                        <!-- El domicilio se guarda para el cliente elegido en el dropdown -->
                        <input type="hidden" name="id_cliente" value="<?= e($cliente['id_cliente']) ?>">
                        <input type="hidden" name="id_domicilio" value="<?= e($datos['id_domicilio']) ?>">

                        <label for="domicilio">Domicilio</label>
                        <textarea id="domicilio" name="domicilio" rows="4"
                                  required minlength="5" maxlength="<?= (int) $longitudMaxima ?>"
                                  placeholder="Calle, número, colonia, ciudad, C.P."
                                  autocomplete="street-address"><?= e($datos['domicilio']) ?></textarea>
                        <span class="contador" id="contador-domicilio"><?= mb_strlen($datos['domicilio']) ?>/<?= (int) $longitudMaxima ?></span>
                        <?php if (!empty($errores['domicilio'])): ?>
                            <span class="texto-error"><?= e($errores['domicilio']) ?></span>
                        <?php endif; ?>

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

                <!-- Derecha -> Tabla de domicilios del cliente elegido -->
                <section class="panel-lista">
                    <h2>Domicilios de <?= e($cliente['nombre_cliente']) ?> (<?= count($domicilios) ?>)</h2>
                    <p class="ayuda">Haz clic en un renglón para cargarlo en el formulario.</p>

                    <?php if (empty($domicilios)): ?>
                        <p class="vacio">Este cliente todavía no tiene domicilios registrados.</p>
                    <?php else: ?>
                        <div class="tabla-contenedor">
                            <table id="tabla-domicilios" class="tabla-seleccionable">
                                <thead>
                                    <tr>
                                        <th>ID Domicilio</th>
                                        <th>Domicilio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($domicilios as $d): ?>
                                        <tr tabindex="0"
                                            class="<?= (int) $datos['id_domicilio'] === (int) $d['id_domicilio'] ? 'fila-seleccionada' : '' ?>"
                                            data-id="<?= (int) $d['id_domicilio'] ?>"
                                            data-domicilio="<?= e($d['domicilio']) ?>">
                                            <td><?= e($d['id_domicilio']) ?></td>
                                            <td><?= e($d['domicilio']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

            </div>

        <?php endif; ?>

        <script src="js/domicilio.js?v=<?= @filemtime(__DIR__ . '/../public/js/domicilio.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
