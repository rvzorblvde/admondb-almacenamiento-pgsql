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

    // Cantidad de domicilios por cliente
    public static function contarPorCliente(): array
    {
        $sql = 'SELECT id_cliente, count(*) AS Total
                FROM InfoPersona.Domicilio_Cliente
                GROUP BY id_cliente';

        return conectar()->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public static function insertar(int $idCliente, string $domicilio)
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
    public static function actualizar(int $idDomicilio, int $idCliente, string $domicilio)
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
    public static function elminar(int $domicilio, int $idCliente): int
    {
        $stmt = conectar()->prepare(
            'DELETE FROM InfoPersona.Domicilio_Cliente
            WHERE id_domicilio = :id_domicilio AND id_cliente = :id_cliente'
        );

        $stmt->execute([':id_cliente' => $idCliente, ':id_cliente' => $idCliente]);
        return $stmt->rowCount();
    }
}
