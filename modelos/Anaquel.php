<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

/*
 * ============================================================
 * MODELO ANAQUEL
 * ------------------------------------------------------------
 * Aquí se encuentran solamente las consultas SQL de Anaquel.
 *
 * Es parecido a lo que hacía AnaquelDAO.cs en Visual:
 * - Consultar
 * - Buscar
 * - Validar zona repetida
 * - Insertar
 * - Modificar
 * - Eliminar
 * ============================================================
 */
class Anaquel
{
    // Estados que manejamos también en el formulario de Visual.
    public const ESTADOS = ['Disponible', 'Ocupado'];

    // Columnas principales de la tabla.
    private const COLUMNAS =
        'id_ubicacion, zona, pasillo, rack, nivel, estado';


    /*
     * Trae todos los anaqueles registrados.
     */
    public static function listar(): array
    {
        $sql =
            'SELECT ' . self::COLUMNAS . '
             FROM InfoInventario.Anaquel
             ORDER BY id_ubicacion DESC';

        return conectar()->query($sql)->fetchAll();
    }


    /*
     * Busca solamente un anaquel por su ID.
     * Se utiliza principalmente cuando estamos modificando.
     */
    public static function buscarPorId(int $id): ?array
    {
        $sql =
            'SELECT ' . self::COLUMNAS . '
             FROM InfoInventario.Anaquel
             WHERE id_ubicacion = :id';

        $stmt = conectar()->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }


    /*
     * Revisa si ya existe una zona.
     *
     * $excluirId sirve al modificar:
     * no contamos el mismo anaquel que estamos editando.
     */
    public static function existeZona(
        int $zona,
        ?int $excluirId = null
    ): bool
    {
        $sql =
            'SELECT 1
             FROM InfoInventario.Anaquel
             WHERE zona = :zona';

        $params = [
            ':zona' => $zona
        ];


        if ($excluirId !== null) {

            $sql .= ' AND id_ubicacion <> :id';

            $params[':id'] = $excluirId;
        }


        $stmt = conectar()->prepare(
            $sql . ' LIMIT 1'
        );

        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }


    /*
     * Inserta un anaquel nuevo.
     *
     * Igual que en Visual, un anaquel nuevo comienza
     * automáticamente con estado Disponible.
     */
    public static function insertar(
        int $zona,
        int $pasillo,
        int $rack,
        int $nivel
    ): int
    {
        $sql =
            "INSERT INTO InfoInventario.Anaquel
             (
                 zona,
                 pasillo,
                 rack,
                 nivel,
                 estado
             )
             VALUES
             (
                 :zona,
                 :pasillo,
                 :rack,
                 :nivel,
                 'Disponible'
             )
             RETURNING id_ubicacion";

        $stmt = conectar()->prepare($sql);

        $stmt->execute([
            ':zona'    => $zona,
            ':pasillo' => $pasillo,
            ':rack'    => $rack,
            ':nivel'   => $nivel
        ]);

        return (int) $stmt->fetchColumn();
    }


    /*
     * Modifica un anaquel que ya existe.
     */
    public static function actualizar(
        int $id,
        int $zona,
        int $pasillo,
        int $rack,
        int $nivel,
        string $estado
    ): int
    {
        $sql =
            'UPDATE InfoInventario.Anaquel
             SET
                 zona = :zona,
                 pasillo = :pasillo,
                 rack = :rack,
                 nivel = :nivel,
                 estado = :estado
             WHERE id_ubicacion = :id';

        $stmt = conectar()->prepare($sql);

        $stmt->execute([
            ':zona'    => $zona,
            ':pasillo' => $pasillo,
            ':rack'    => $rack,
            ':nivel'   => $nivel,
            ':estado'  => $estado,
            ':id'      => $id
        ]);

        return $stmt->rowCount();
    }


    /*
     * Elimina un anaquel usando su ID.
     */
    public static function eliminar(int $id): int
    {
        $stmt = conectar()->prepare(
            'DELETE FROM InfoInventario.Anaquel
             WHERE id_ubicacion = :id'
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->rowCount();
    }
}