<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Transporte {
    public const ESTADOS = ['Disponible', 'En tránsito', 'Mantenimiento'];

    public const UNIDADES = [
        'Tractocamión' => ['marca' => 'Freightliner', 'modelo' => 'Cascadia'],
        'Camión de Caja Cerrada' => ['marca' => 'Isuzu', 'modelo' => 'ELF 600'],
        'Van de Carga' => ['marca' => 'Ford', 'modelo' => 'Transit'],
        'Furgoneta Compacta' => ['marca' => 'Peugeot', 'modelo' => 'Partner'],
        'Camioneta Pick-Up' => ['marca' => 'Toyota', 'modelo' => 'Hilux']
    ];

    private const COLUMNAS = 'id_transporte, placa, tipo_unidad, marca, modelo, estado';

    /** Consulta todos los transportes registrados. */
    public static function listar(): array {
        $sql = 'SELECT ' . self::COLUMNAS . ' FROM InfoLogistica.Transporte ORDER BY id_transporte DESC';

        return conectar()->query($sql)->fetchAll();
    }

    /** Busca un transporte por su identificador. */
    public static function buscarPorId(int $id): ?array {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoLogistica.Transporte WHERE id_transporte = :id');

        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Verifica si la placa ya se encuentra registrada. */
    public static function existePlaca(string $placa, ?int $excluirId = null): bool {
        if ($excluirId === null) {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoLogistica.Transporte WHERE UPPER(placa) = UPPER(:placa) LIMIT 1');

            $stmt->execute([':placa' => $placa]);
        } else {
            $stmt = conectar()->prepare('SELECT 1 FROM InfoLogistica.Transporte WHERE UPPER(placa) = UPPER(:placa) AND id_transporte <> :id LIMIT 1');

            $stmt->execute([':placa' => $placa, ':id' => $excluirId]);
        }

        return (bool) $stmt->fetchColumn();
    }

    /** Registra un nuevo transporte. */
    public static function insertar(string $placa, string $tipoUnidad, string $marca, string $modelo): int {
        $stmt = conectar()->prepare('INSERT INTO InfoLogistica.Transporte (placa, tipo_unidad, marca, modelo, estado)
        VALUES (:placa, :tipo_unidad, :marca, :modelo, \'Disponible\') RETURNING id_transporte');

        $stmt->execute([
            ':placa' => $placa,
            ':tipo_unidad' => $tipoUnidad,
            ':marca' => $marca,
            ':modelo' => $modelo
        ]);

        return (int) $stmt->fetchColumn();
    }

    /** Devuelve la cantidad de filas modificadas. */
    public static function actualizar(int $id, string $placa, string $tipoUnidad, string $marca, string $modelo, string $estado): int {
        $stmt = conectar()->prepare('UPDATE InfoLogistica.Transporte SET placa = :placa, tipo_unidad = :tipo_unidad, marca = :marca, modelo = :modelo, estado = :estado WHERE id_transporte = :id');

        $stmt->execute([
            ':placa' => $placa,
            ':tipo_unidad' => $tipoUnidad,
            ':marca' => $marca,
            ':modelo' => $modelo,
            ':estado' => $estado,
            ':id' => $id
        ]);

        return $stmt->rowCount();
    }

    /** Devuelve la cantidad de filas eliminadas. */
    public static function eliminar(int $id): int {
        $stmt = conectar()->prepare('DELETE FROM InfoLogistica.Transporte WHERE id_transporte = :id');

        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
