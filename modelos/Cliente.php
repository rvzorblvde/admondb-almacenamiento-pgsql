<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Cliente
{
    public const ESTADOS = ['Activo', 'Inactivo'];

    private const COLUMNAS = 'id_cliente, nombre_cliente, telefono_cliente, correo_electronico, fecha_registro, estado';

    public static function listar(): array
    {
        $sql = 'SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Cliente ORDER BY id_cliente DESC';
        return conectar()->query($sql)->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Cliente WHERE id_cliente = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Indica si el correo ya está registrado (ignorando mayúsculas y, opcionalmente, un cliente). */
    public static function existeCorreo(string $correo, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = conectar()->prepare(
                'SELECT 1 FROM InfoPersona.Cliente WHERE LOWER(correo_electronico) = LOWER(:correo) LIMIT 1'
            );
            $stmt->execute([':correo' => $correo]);
        } else {
            $stmt = conectar()->prepare(
                'SELECT 1 FROM InfoPersona.Cliente
                 WHERE LOWER(correo_electronico) = LOWER(:correo) AND id_cliente <> :id LIMIT 1'
            );
            $stmt->execute([':correo' => $correo, ':id' => $excluirId]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public static function insertar(string $nombre, string $telefono, string $correo, string $estado): int
    {
        $stmt = conectar()->prepare(
            'INSERT INTO InfoPersona.Cliente (nombre_cliente, telefono_cliente, correo_electronico, estado)
             VALUES (:nombre, :telefono, :correo, :estado)
             RETURNING id_cliente'
        );
        $stmt->execute([
            ':nombre'   => $nombre,
            ':telefono' => $telefono,
            ':correo'   => $correo,
            ':estado'   => $estado,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /** Devuelve la cantidad de filas modificadas (0 si el cliente no existe). */
    public static function actualizar(int $id, string $nombre, string $telefono, string $correo, string $estado): int
    {
        $stmt = conectar()->prepare(
            'UPDATE InfoPersona.Cliente
             SET nombre_cliente = :nombre,
                 telefono_cliente = :telefono,
                 correo_electronico = :correo,
                 estado = :estado
             WHERE id_cliente = :id'
        );
        $stmt->execute([
            ':nombre'   => $nombre,
            ':telefono' => $telefono,
            ':correo'   => $correo,
            ':estado'   => $estado,
            ':id'       => $id,
        ]);
        return $stmt->rowCount();
    }

    /** Devuelve la cantidad de filas eliminadas (0 si el cliente no existe). */
    public static function eliminar(int $id): int
    {
        $stmt = conectar()->prepare('DELETE FROM InfoPersona.Cliente WHERE id_cliente = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
