<?php
require_once __DIR__ . '/env.php';
cargarEnv(__DIR__ . '/../.env');

function conectar(): PDO {
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']
    );

    try {
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos." . $e->getMessage());
    }
}
