<?php require __DIR__ . '/parciales/header.php'; ?>

        <div class="barra-acciones">
            <a class="boton-principal" href="proveedor.php?accion=nuevo">+ Nuevo proveedor</a>
        </div>

        <?php if (empty($proveedores)): ?>
            <p class="vacio">Todavía no hay proveedores registrados.</p>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del proveedor</th>
                            <th>Empresa</th>
                            <th>Teléfono</th>
                            <th>Correo de la empresa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $p): ?>
                            <tr>
                                <td><?= e($p['id_proveedor']) ?></td>
                                <td><?= e($p['nombre_proveedor']) ?></td>
                                <td><?= e($p['nombre_empresa']) ?></td>
                                <td><?= e($p['telefono_proveedor']) ?></td>
                                <td><?= e($p['correo_empresa']) ?></td>
                                <td class="acciones">
                                    <a class="boton-secundario" href="proveedor.php?accion=editar&amp;id=<?= (int) $p['id_proveedor'] ?>">Editar</a>
                                    <form action="proveedor.php" method="POST"
                                          onsubmit="return confirm('¿Eliminar a este proveedor? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_proveedor" value="<?= (int) $p['id_proveedor'] ?>">
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
