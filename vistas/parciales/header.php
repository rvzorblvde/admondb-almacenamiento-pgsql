<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="contenedor">
        <h1><?= e($titulo) ?></h1>

        <?php if (!empty($flash)): ?>
            <p class="mensaje-<?= $flash['tipo'] === 'exito' ? 'exito' : 'error' ?>" role="alert">
                <?= e($flash['texto']) ?>
            </p>
        <?php endif; ?>
