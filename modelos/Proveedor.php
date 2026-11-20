<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Proveedor
{
    private const COLUMNAS = 'id_proveedor, nombre_proveedor, nombre_empresa, telefono_proveedor, correo_empresa';

    private const ACENTOS = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'];

    /**
     * Convierte el nombre de la empresa en el dominio de su correo.
     * Ej: "Lácteos del Norte S.A." -> "lacteosdelnortesa"
     * (El mismo cálculo se hace en public/js/proveedor.js)
     */
    public static function dominioEmpresa(string $empresa): string
    {
        $texto = strtr(mb_strtolower(trim($empresa)), self::ACENTOS);
        return (string) preg_replace('/[^a-z0-9]/', '', $texto);
    }

    /** Arma el correo completo: usuario@dominio.com */
    public static function armarCorreo(string $usuario, string $empresa): string
    {
        return $usuario . '@' . self::dominioEmpresa($empresa) . '.com';
    }

    /** Devuelve solo la parte antes del @ de un correo. */
    public static function usuarioDeCorreo(string $correo): string
    {
        return explode('@', $correo, 2)[0];
    }

    public static function listar(): array
    {
        $sql = 'SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Proveedor ORDER BY id_proveedor DESC';
        return conectar()->query($sql)->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoPersona.Proveedor WHERE id_proveedor = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Indica si ya existe un registro idéntico (mismo nombre, empresa, teléfono y correo). */
    public static function existeDuplicado(string $nombre, string $empresa, string $telefono, string $correo, ?int $excluirId = null): bool
    {
        $sql = 'SELECT 1 FROM InfoPersona.Proveedor
                WHERE LOWER(TRIM(nombre_proveedor)) = LOWER(TRIM(:nombre))
                  AND LOWER(TRIM(nombre_empresa)) = LOWER(TRIM(:empresa))
                  AND telefono_proveedor = :telefono
                  AND LOWER(correo_empresa) = LOWER(:correo)';
        $params = [':nombre' => $nombre, ':empresa' => $empresa, ':telefono' => $telefono, ':correo' => $correo];

        if ($excluirId !== null) {
            $sql .= ' AND id_proveedor <> :id';
            $params[':id'] = $excluirId;
        }

        $stmt = conectar()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** Indica si el correo ya está registrado. */
    public static function existeCorreo(string $correo, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Proveedor WHERE LOWER(correo_empresa) = LOWER(:correo) LIMIT 1');
            $stmt->execute([':correo' => $correo]);
        } else {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Proveedor WHERE LOWER(correo_empresa) = LOWER(:correo) AND id_proveedor <> :id LIMIT 1');
            $stmt->execute([':correo' => $correo, ':id' => $excluirId]);
        }
        return (bool) $stmt->fetchColumn();
    }

    /** Indica si el teléfono ya está registrado (por restricción UNIQUE). */
    public static function existeTelefono(string $telefono, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Proveedor WHERE telefono_proveedor = :telefono LIMIT 1');
            $stmt->execute([':telefono' => $telefono]);
        } else {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Proveedor WHERE telefono_proveedor = :telefono AND id_proveedor <> :id LIMIT 1');
            $stmt->execute([':telefono' => $telefono, ':id' => $excluirId]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public static function insertar(string $nombre_proveedor, string $nombre_empresa, string $telefono, string $correo): int
    {
        $stmt = conectar()->prepare(
            'INSERT INTO InfoPersona.Proveedor (nombre_proveedor, nombre_empresa, telefono_proveedor, correo_empresa)
             VALUES (:nombre_proveedor, :nombre_empresa, :telefono, :correo)
             RETURNING id_proveedor'
        );
        $stmt->execute([
            ':nombre_proveedor' => $nombre_proveedor,
            ':nombre_empresa'   => $nombre_empresa,
            ':telefono'         => $telefono,
            ':correo'           => $correo,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /** Devuelve la cantidad de filas modificadas. */
    public static function actualizar(int $id, string $nombre_proveedor, string $nombre_empresa, string $telefono, string $correo): int
    {
        $stmt = conectar()->prepare(
            'UPDATE InfoPersona.Proveedor
             SET nombre_proveedor = :nombre_proveedor,
                 nombre_empresa = :nombre_empresa,
                 telefono_proveedor = :telefono,
                 correo_empresa = :correo
             WHERE id_proveedor = :id'
        );
        $stmt->execute([
            ':nombre_proveedor' => $nombre_proveedor,
            ':nombre_empresa'   => $nombre_empresa,
            ':telefono'         => $telefono,
            ':correo'           => $correo,
            ':id'               => $id,
        ]);
        return $stmt->rowCount();
    }

    /** Devuelve la cantidad de filas eliminadas. */
    public static function eliminar(int $id): int
    {
        $stmt = conectar()->prepare('DELETE FROM InfoPersona.Proveedor WHERE id_proveedor = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
