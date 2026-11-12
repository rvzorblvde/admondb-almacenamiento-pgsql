<?php require __DIR__ . '/parciales/header.php'; ?>

        <?php if (!empty($errores['general'])): ?>
            <p class="mensaje-error" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <form action="proveedor.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id_proveedor" value="<?= e($datos['id_proveedor']) ?>">

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

            <label for="nombre_empresa">Nombre de la empresa</label>
            <input type="text" id="nombre_empresa" name="nombre_empresa"
                   value="<?= e($datos['nombre_empresa']) ?>"
                   required minlength="2" maxlength="200"
                   autocomplete="organization">
            <?php if (!empty($errores['nombre_empresa'])): ?>
                <span class="texto-error"><?= e($errores['nombre_empresa']) ?></span>
            <?php endif; ?>

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

            <label for="correo_empresa">Correo de la empresa</label>
            <input type="email" id="correo_empresa" name="correo_empresa"
                   value="<?= e($datos['correo_empresa']) ?>"
                   required maxlength="255" autocomplete="email">
            <?php if (!empty($errores['correo_empresa'])): ?>
                <span class="texto-error"><?= e($errores['correo_empresa']) ?></span>
            <?php endif; ?>

            <div class="barra-acciones">
                <button type="submit" class="boton-principal"><?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?></button>
                <a class="boton-secundario" href="proveedor.php">Cancelar</a>
            </div>
        </form>

<?php require __DIR__ . '/parciales/footer.php'; ?>
