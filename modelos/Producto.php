<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

/*
 * ============================================================
 * Producto.php
 * ------------------------------------------------------------
 * Este es el modelo de Producto.
 *
 * Aquí se hacen solamente las consultas SQL relacionadas
 * con los productos.
 *
 * Se puede:
 * - Consultar productos.
 * - Consultar proveedores.
 * - Consultar anaqueles.
 * - Insertar.
 * - Modificar.
 * - Eliminar.
 * ============================================================
 */

class Producto
{

    /*
     * ========================================================
     * CONSULTAR TODOS LOS PRODUCTOS
     * ========================================================
     *
     * También traemos el nombre del proveedor y los datos
     * del anaquel para que la tabla se vea más clara.
     */
    public static function listar(): array
    {
        $sql = "
            SELECT
                p.id_producto,
                p.id_proveedor,
                p.id_ubicacion,
                p.nombre_producto,
                p.precio,
                p.peso,
                p.dimensiones,
                p.stock,

                pr.nombre_proveedor,
                pr.nombre_empresa,

                a.zona,
                a.pasillo,
                a.rack,
                a.nivel,
                a.estado AS estado_anaquel

            FROM InfoInventario.Producto p

            INNER JOIN InfoPersona.Proveedor pr
                ON pr.id_proveedor = p.id_proveedor

            INNER JOIN InfoInventario.Anaquel a
                ON a.id_ubicacion = p.id_ubicacion

            ORDER BY p.id_producto
        ";

        return conectar()
            ->query($sql)
            ->fetchAll();
    }


    /*
     * ========================================================
     * CONSULTAR PROVEEDORES
     * ========================================================
     *
     * Esto se utiliza para llenar la lista de Proveedor.
     */
    public static function listarProveedores(): array
    {
        $sql = "
            SELECT
                id_proveedor,
                nombre_proveedor,
                nombre_empresa

            FROM InfoPersona.Proveedor

            ORDER BY nombre_proveedor
        ";

        return conectar()
            ->query($sql)
            ->fetchAll();
    }


    /*
     * ========================================================
     * CONSULTAR ANAQUELES
     * ========================================================
     *
     * Esto llena la lista de Ubicación.
     */
    public static function listarAnaqueles(): array
    {
        $sql = "
            SELECT
                id_ubicacion,
                zona,
                pasillo,
                rack,
                nivel,
                estado

            FROM InfoInventario.Anaquel

            ORDER BY id_ubicacion
        ";

        return conectar()
            ->query($sql)
            ->fetchAll();
    }


    /*
     * Revisa que el proveedor seleccionado
     * realmente exista.
     */
    public static function existeProveedor(
        int $idProveedor
    ): bool
    {
        $sql = "
            SELECT 1

            FROM InfoPersona.Proveedor

            WHERE id_proveedor = :id

            LIMIT 1
        ";

        $stmt =
            conectar()->prepare($sql);

        $stmt->execute([
            ':id' => $idProveedor
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /*
     * Revisa que el anaquel seleccionado exista.
     */
    public static function existeAnaquel(
        int $idUbicacion
    ): bool
    {
        $sql = "
            SELECT 1

            FROM InfoInventario.Anaquel

            WHERE id_ubicacion = :id

            LIMIT 1
        ";

        $stmt =
            conectar()->prepare($sql);

        $stmt->execute([
            ':id' => $idUbicacion
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /*
     * ========================================================
     * INSERTAR PRODUCTO
     * ========================================================
     */
    public static function insertar(
        int $idProveedor,
        int $idUbicacion,
        string $nombreProducto,
        float $precio,
        float $peso,
        string $dimensiones,
        int $stock
    ): int
    {
        $sql = "
            INSERT INTO InfoInventario.Producto
            (
                id_proveedor,
                id_ubicacion,
                nombre_producto,
                precio,
                peso,
                dimensiones,
                stock
            )

            VALUES
            (
                :id_proveedor,
                :id_ubicacion,
                :nombre_producto,
                :precio,
                :peso,
                :dimensiones,
                :stock
            )

            
            ON CONFLICT (id_ubicacion)
            DO UPDATE SET
                stock = InfoInventario.Producto.stock + EXCLUDED.stock
            WHERE
                InfoInventario.Producto.id_proveedor = EXCLUDED.id_proveedor
                AND LOWER(BTRIM(InfoInventario.Producto.nombre_producto))
                    = LOWER(BTRIM(EXCLUDED.nombre_producto))
            RETURNING id_producto

        ";

        $stmt =
            conectar()->prepare($sql);

        $stmt->execute([

            ':id_proveedor' =>
                $idProveedor,

            ':id_ubicacion' =>
                $idUbicacion,

            ':nombre_producto' =>
                $nombreProducto,

            ':precio' =>
                $precio,

            ':peso' =>
                $peso,

            ':dimensiones' =>
                $dimensiones,

            ':stock' =>
                $stock
        ]);

        $resultado = $stmt->fetchColumn();

        return $resultado === false ? 0 : (int) $resultado;return (int) $stmt->fetchColumn();
    }


    /*
     * ========================================================
     * MODIFICAR PRODUCTO
     * ========================================================
     */
    public static function modificar(
        int $idProducto,
        int $idProveedor,
        int $idUbicacion,
        string $nombreProducto,
        float $precio,
        float $peso,
        string $dimensiones,
        int $stock
    ): int
    {
        $sql = "
            UPDATE InfoInventario.Producto

            SET
                id_proveedor = :id_proveedor,
                id_ubicacion = :id_ubicacion,
                nombre_producto = :nombre_producto,
                precio = :precio,
                peso = :peso,
                dimensiones = :dimensiones,
                stock = :stock

            WHERE id_producto = :id_producto
        ";

        $stmt =
            conectar()->prepare($sql);

        $stmt->execute([

            ':id_producto' =>
                $idProducto,

            ':id_proveedor' =>
                $idProveedor,

            ':id_ubicacion' =>
                $idUbicacion,

            ':nombre_producto' =>
                $nombreProducto,

            ':precio' =>
                $precio,

            ':peso' =>
                $peso,

            ':dimensiones' =>
                $dimensiones,

            ':stock' =>
                $stock
        ]);

        return $stmt->rowCount();
    }


    /*
     * ========================================================
     * ELIMINAR PRODUCTO
     * ========================================================
     */
    public static function eliminar(
        int $idProducto
    ): int
    {
        $sql = "
            DELETE FROM InfoInventario.Producto

            WHERE id_producto = :id
        ";

        $stmt =
            conectar()->prepare($sql);

        $stmt->execute([
            ':id' => $idProducto
        ]);

        return $stmt->rowCount();
    }
}