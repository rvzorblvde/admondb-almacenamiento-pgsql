<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Transportista {
    public const ESTADOS = ['Disponible', 'En servicio'];

    private const COLUMNAS = 'id_transportista, nombre_transportista, telefono_transportista, correo_transportista, estado, fecha_contratacion';

    public static function listar(): array {
        $sql = 'SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Transportista ORDER BY id_transportista DESC';

        return conectar()->query($sql)->fetchAll();
    }

    public static function buscarPorId(int $id): ?array {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Transportista WHERE id_transportista = :id');

        $stmt->execute([':id => $id']);

        return $stmt->fetch() ?: null;
    }

    /** Verifica si el correo ya se encuentra registrado. */
    public static function existeCorreo(string $correo, ?int $excluirId = null): bool {
        if ($excluirId === null) {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Transportista WHERE LOWER(correo_transportista) = LOWER(:correo) LIMIT 1');

            $stmt->execute([':correo' => $correo]);
        } else {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Transportista WHERE LOWER(correo_transportista) = LOWER(:correo) AND id_transportista <> :id LIMIT 1');

            $stmt->execute([':correo' => $correo, ':id' => $excluirId]);
        }

        return (bool) $stmt->fetchColumn();
    }

    /** Verifica si el teléfono se encuentra registrado. */
    public static function existeTelefono(string $telefono, ?int $excluirId = null): bool {
        if ($excluirId === null) {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Transportista WHERE telefono_transportista = :telefono LIMIT 1');

            $stmt->execute([':telefono' => $telefono]);
        } else {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Transportista WHERE telefono_transportista = :telefono AND id_transportista <> :id LIMIT 1');

            $stmt->execute([':telefono' => $telefono, ':id' => $excluirId]);
        }

        return (bool) $stmt->fetchColumn();
    }

    public static function insertar(string $nombre, string $telefono, string $correo): int {
        $stmt = conectar()->prepare('INSERT INTO InfoPersona.Transportista (nombre_transportista, telefono_transportista, correo_transportista, estado, fecha_contratacion)
        VALUES (:nombre, :telefono, :correo, \'Disponible\', CURRENT_DATE) RETURNING id_transportista');

        $stmt->execute([':nombre' => $nombre, ':telefono' => $telefono, ':correo' => $correo]);

        return (int) $stmt->fetchColumn();
    }

    /** Devuelve la cantidad de filas modificadas. */
    public static function actualizar(int $id, string $nombre, string $telefono, string $correo, string $estado): int {
        $stmt = conectar()->prepare('UPDATE InfoPersona.Transportista SET nombre_transportista = :nombre, telefono_transportista = :telefono, correo_transportista = :correo, estado = :estado WHERE id_transportista = :id');

        $stmt->execute([':nombre' => $nombre, ':telefono' => $telefono, ':correo' => $correo, ':estado' => $estado, ':id' => $id]);

        return $stmt->rowCount();
    }

    /** Devuelve la cantidad de filas eliminadas. */
    public static function eliminar(int $id): int {
        $stmt = conectar()->prepare('DELETE FROM InfoPersona.Transportista WHERE id_transportista = :id');

        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}