<?php require __DIR__ . '/parciales/header.php'; ?>

        <div class="barra-acciones">
            <a class="boton-principal" href="cliente.php?accion=nuevo">+ Nuevo cliente</a>
        </div>

        <?php if (empty($clientes)): ?>
            <p class="vacio">Todavía no hay clientes registrados.</p>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo electrónico</th>
                            <th>Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
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
                                <td class="acciones">
                                    <a class="boton-secundario" href="cliente.php?accion=editar&amp;id=<?= (int) $c['id_cliente'] ?>">Editar</a>
                                    <form action="cliente.php" method="POST"
                                          onsubmit="return confirm('¿Eliminar a este cliente? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_cliente" value="<?= (int) $c['id_cliente'] ?>">
                                        <button type="submit" class="boton-peligro">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

<?php require __DIR__ . '/parciales/footer.php'; ?>
