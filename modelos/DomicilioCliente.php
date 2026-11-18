<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class DomicilioCliente
{
    public const LONGITUD_MAXIMA = 200;

    // Domicilios de un cliente (0 o más)
    public static function listarPorCliente(int $idCliente): array
    {
        $stmt = conectar()->prepare(
            'SELECT id_domicilio, id_cliente, domicilio
            FROM InfoPersona.Domicilio_Cliente
            WHERE id_cliente = :id_cliente
            ORDER BY id_domicilio DESC'
        );
        $stmt->execute([':id_cliente' => $idCliente]);

        return $stmt->fetchAll();
    }

    // Indica si el cliente ya tiene ese domicilio (sin importar mayúsculas).
    // Solo compara contra los domicilios del MISMO cliente; al editar se ignora el propio registro.
    public static function existeDomicilio(int $idCliente, string $domicilio, ?int $excluirId = null): bool
    {
        $sql = 'SELECT 1 FROM InfoPersona.Domicilio_Cliente
        WHERE id_cliente = :id_cliente
        AND LOWER(domicilio) = LOWER(:domicilio)';
        $params = [':id_cliente' => $idCliente, ':domicilio' => $domicilio];

        if ($excluirId !== null) {
            $sql .= ' AND id_domicilio <> :id_domicilio';
            $params[':id_domicilio'] = $excluirId;
        }

        $stmt = conectar()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function insertar(int $idCliente, string $domicilio): int
    {
        $stmt = conectar()->prepare(
            'INSERT INTO InfoPersona.Domicilio_Cliente (id_cliente, domicilio)
        VALUES (:id_cliente, :domicilio)
        RETURNING id_domicilio'
        );

        $stmt->execute([':id_cliente' => $idCliente, ':domicilio' => $domicilio]);
        return (int) $stmt->fetchColumn();
    }

    // Se evita que se puedan modificar domicilios de otro cliente cambiando el ID del formulario
    public static function actualizar(int $idDomicilio, int $idCliente, string $domicilio): int
    {
        $stmt = conectar()->prepare(
            'UPDATE InfoPersona.Domicilio_Cliente
            SET domicilio = :domicilio
            WHERE id_domicilio = :id_domicilio AND id_cliente = :id_cliente'
        );

        $stmt->execute([':id_cliente' => $idCliente, ':id_domicilio' => $idDomicilio, ':domicilio' => $domicilio]);
        return $stmt->rowCount();
    }

    // Devuelve las filas eliminadas
    public static function eliminar(int $idDomicilio, int $idCliente): int
    {
        $stmt = conectar()->prepare(
            'DELETE FROM InfoPersona.Domicilio_Cliente
            WHERE id_domicilio = :id_domicilio AND id_cliente = :id_cliente'
        );

        $stmt->execute([':id_domicilio' => $idDomicilio, ':id_cliente' => $idCliente]);
        return $stmt->rowCount();
    }
}
