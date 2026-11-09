<?php require __DIR__ . '/parciales/header.php'; ?>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form action="cliente.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="accion" value="guardar">
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
                   value="<?= e($datos['correo_electronico']) ?>"
                   required maxlength="255" autocomplete="email">
            <?php if (!empty($errores['correo_electronico'])): ?>
                <span class="texto-error"><?= e($errores['correo_electronico']) ?></span>
            <?php endif; ?>

            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= e($estado) ?>" <?= $datos['estado'] === $estado ? 'selected' : '' ?>>
                        <?= e($estado) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errores['estado'])): ?>
                <span class="texto-error"><?= e($errores['estado']) ?></span>
            <?php endif; ?>

            <?php if ($esEdicion && !empty($datos['fecha_registro'])): ?>
                <label for="fecha_registro">Fecha de registro</label>
                <input type="text" id="fecha_registro" value="<?= e($datos['fecha_registro']) ?>" readonly disabled>
            <?php endif; ?>

            <div class="barra-acciones">
                <button type="submit" class="boton-principal"><?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?></button>
                <a class="boton-secundario" href="cliente.php">Cancelar</a>
            </div>
        </form>

<?php require __DIR__ . '/parciales/footer.php'; ?>
