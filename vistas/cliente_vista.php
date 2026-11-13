<?php require __DIR__ . '/parciales/header.php'; ?>

        <link rel="stylesheet" href="css/cliente-layout.css?v=<?= @filemtime(__DIR__ . '/../public/css/cliente-layout.css') ?: 0 ?>">

        <div class="barra-acciones">
            <a class="boton-secundario" href="../public/index.html">&larr; Volver al inicio</a>
        </div>

        <div class="panel-doble">

            <!-- Izquierda -> Formulario -->
            <section class="panel-formulario">
                <h2 id="titulo-formulario"><?= $esEdicion ? 'Editar cliente' : 'Nuevo cliente' ?></h2>

                <?php if (!empty($errores['general'])): ?>
                    <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
                <?php endif; ?>

                <form id="form-cliente" action="cliente.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id_cliente" value="<?= e($datos['id_cliente']) ?>">

                    <label for="nombre_cliente">Nombre completo</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente"
                           value="<?= e($datos['nombre_cliente']) ?>"
                           required minlength="2" maxlength="255"
                           pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'\-]+"
                           title="Solo letras, espacios, puntos, apóstrofes y guiones (mínimo 2 caracteres)"
                           autocomplete="name">
                    <?php if (!empty($errores['nombre_cliente'])): ?>
                        <span class="texto-error"><?= e($errores['nombre_cliente']) ?></span>
                    <?php endif; ?>

                    <label for="telefono_cliente">Teléfono (10 dígitos)</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente"
                           value="<?= e($datos['telefono_cliente']) ?>"
                           required inputmode="numeric" maxlength="10" minlength="10"
                           pattern="[0-9]{10}"
                           title="Exactamente 10 dígitos, sin espacios ni guiones"
                           autocomplete="tel-national">
                    <?php if (!empty($errores['telefono_cliente'])): ?>
                        <span class="texto-error"><?= e($errores['telefono_cliente']) ?></span>
                    <?php endif; ?>

                    <label for="correo_electronico">Correo electrónico</label>
                    <input type="email" id="correo_electronico" name="correo_electronico"
                           placeholder="ejemplo@correo.com" value="<?= e($datos['correo_electronico']) ?>"
                           required maxlength="255" autocomplete="email">
                    <?php if (!empty($errores['correo_electronico'])): ?>
                        <span class="texto-error"><?= e($errores['correo_electronico']) ?></span>
                    <?php endif; ?>

                    <label for="estado">Estado</label>
                    <!-- Bloqueado al registrar, se puede cambiar al editar -->
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

                    <!-- Lo llena automáticamente JavaScript -->
                    <div id="grupo-fecha">
                        <label for="fecha_registro">Fecha de registro</label>
                        <input type="text" id="fecha_registro"
                               value="<?= e($datos['fecha_registro'] ?? '') ?>"
                               placeholder="Se asigna al registrar" readonly disabled>
                    </div>

                    <div class="barra-acciones">
                        <!-- Botón único: registra si no hay cliente seleccionado, modifica si lo hay -->
                        <button type="submit" id="btn-guardar" name="accion" value="guardar" class="boton-principal">
                            <?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?>
                        </button>

                        <!-- Solo visible cuando hay un cliente seleccionado -->
                        <button type="submit" id="btn-eliminar" name="accion" value="eliminar"
                                class="boton-peligro" formnovalidate <?= $esEdicion ? '' : 'hidden' ?>>
                            Eliminar
                        </button>
                    </div>
                </form>
            </section>

            <!-- Derecha -> Tabla de datos -->
            <section class="panel-lista">
                <h2>Lista de clientes</h2>
                <p class="ayuda">Clic en un registro para editar.</p>

                <?php if (empty($clientes)): ?>
                    <p class="vacio">Todavía no hay clientes registrados.</p>
                <?php else: ?>
                    <div class="tabla-contenedor">
                        <table id="tabla-clientes" class="tabla-seleccionable">
                            <thead>
                                <tr>
                                    <th>ID Cliente</th>
                                    <th>Nombre del Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Correo electrónico</th>
                                    <th>Registro</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $c): ?>
                                    <tr tabindex="0"
                                        class="<?= (int) $datos['id_cliente'] === (int) $c['id_cliente'] ? 'fila-seleccionada' : '' ?>"
                                        data-id="<?= (int) $c['id_cliente'] ?>"
                                        data-nombre="<?= e($c['nombre_cliente']) ?>"
                                        data-telefono="<?= e($c['telefono_cliente']) ?>"
                                        data-correo="<?= e($c['correo_electronico']) ?>"
                                        data-estado="<?= e($c['estado']) ?>"
                                        data-fecha="<?= e($c['fecha_registro']) ?>">
                                        <td><?= e($c['id_cliente']) ?></td>
                                        <td><?= e($c['nombre_cliente']) ?></td>
                                        <td><?= e($c['telefono_cliente']) ?></td>
                                        <td><?= e($c['correo_electronico']) ?></td>
                                        <td><?= e($c['fecha_registro']) ?></td>
                                        <td>
                                            <span class="etiqueta etiqueta-<?= $c['estado'] === 'Activo' ? 'activo' : 'inactivo' ?>">
                                                <?= e($c['estado']) ?>
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

        <script src="js/cliente.js?v=<?= @filemtime(__DIR__ . '/../public/js/cliente.js') ?: 0 ?>" defer></script>

<?php require __DIR__ . '/parciales/footer.php'; ?>
