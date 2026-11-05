<?php
function cargarEnv(string $ruta): void {
    if (!is_readable($ruta)) {
        die("No se encontró el archivo .env | Copia .env.example a .env y completalo con sus datos correspondientes.");
    }

    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);

        if (str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $_ENV[trim($clave)] = trim($valor, " \t\"'");
    }
}
