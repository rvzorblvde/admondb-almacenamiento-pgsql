<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Entrega {
    // estado_entrega no tiene CHECK en la BD; estos valores son los que usan los disparadores 2 y 6.
    public const ESTADOS = ['Pendiente', 'Completado', 'Cancelado'];

    // Estados de ruta a los que todavía se les pueden asignar entregas.
    public const ESTADOS_RUTA_ACTIVOS = ['Programada', 'En curso'];

    private const COLUMNAS = 'e.id_entrega, e.id_ruta, e.id_domicilio, e.fecha_programada, e.hora_programada,
                                e.fecha_entrega_real, e.hora_entrega_real, e.estado_entrega';

    // Ruta: "HoraSalida [dd/mm] - NombreRuta"  ->  11:45 [20/10] - Almacen-SLP
    private const RUTA_DISPLAY = "TO_CHAR(r.hora_salida, 'HH24:MI') || ' [' || TO_CHAR(r.fecha_ruta, 'DD/MM') || '] - ' || r.nombre_ruta";

    // Domicilio: "NombreCliente Domicilio (n/total)"  ->  Brenda Niño Artillero (1/1)
    // n = posición del domicilio entre los del mismo cliente; total = domicilios del cliente.
    private const DOMICILIOS_NUMERADOS = "SELECT d.id_domicilio,
                c.nombre_cliente || ' ' || d.domicilio
                || ' (' || ROW_NUMBER() OVER (PARTITION BY d.id_cliente ORDER BY d.id_domicilio)
                || '/'  || COUNT(*)     OVER (PARTITION BY d.id_cliente) || ')' AS domicilio_display,
                c.nombre_cliente
                FROM InfoPersona.Domicilio_Cliente d
                INNER JOIN InfoPersona.Cliente c ON d.id_cliente = c.id_cliente";

    // Consultar las entregas y mostrar los datos concatenados de sus llaves foráneas.
    public static function listar(): array {
        $sql = "WITH dom AS (" . self::DOMICILIOS_NUMERADOS . ")
                SELECT " . self::COLUMNAS . ",
                " . self::RUTA_DISPLAY . " AS ruta_display,
                dom.domicilio_display
                FROM InfoLogistica.Entrega e
                INNER JOIN InfoLogistica.Ruta r ON e.id_ruta = r.id_ruta
                INNER JOIN dom ON e.id_domicilio = dom.id_domicilio
                ORDER BY e.id_entrega DESC";

        return conectar()->query($sql)->fetchAll();
    }

    // Buscar una entrega por su ID.
    public static function buscarPorId(int $id): ?array {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoLogistica.Entrega e WHERE e.id_entrega = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Obtener las rutas para el selector.
    public static function listarRutas(): array {
        $sql = "SELECT r.id_ruta, " . self::RUTA_DISPLAY . " AS ruta_display
                FROM InfoLogistica.Ruta r
                ORDER BY r.fecha_ruta DESC, r.hora_salida";

        return conectar()->query($sql)->fetchAll();
    }

    // Obtener los domicilios (con su cliente) para el selector.
    public static function listarDomicilios(): array {
        $sql = "WITH dom AS (" . self::DOMICILIOS_NUMERADOS . ")
                SELECT id_domicilio, domicilio_display
                FROM dom
                ORDER BY nombre_cliente, domicilio_display";

        return conectar()->query($sql)->fetchAll();
    }

    // Datos de la ruta necesarios para validar la entrega.
    public static function buscarRuta(int $idRuta): ?array {
        $stmt = conectar()->prepare(
            'SELECT id_ruta, fecha_ruta, hora_salida, hora_estimada_llegada, estado_ruta
            FROM InfoLogistica.Ruta
            WHERE id_ruta = :id'
        );
        $stmt->execute([':id' => $idRuta]);

        return $stmt->fetch() ?: null;
    }

    public static function existeDomicilio(int $idDomicilio): bool {
        $stmt = conectar()->prepare('SELECT 1 FROM InfoPersona.Domicilio_Cliente WHERE id_domicilio = :id');
        $stmt->execute([':id' => $idDomicilio]);

        return (bool) $stmt->fetchColumn();
    }

    // Indica si el domicilio ya tiene una entrega en esa ruta (al editar se ignora el propio registro).
    public static function existeEntregaEnRuta(int $idRuta, int $idDomicilio, int $excluirId = 0): bool {
        $stmt = conectar()->prepare(
            'SELECT 1 FROM InfoLogistica.Entrega
            WHERE id_ruta = :ruta
            AND id_domicilio = :domicilio
            AND id_entrega <> :excluir
            LIMIT 1'
        );
        $stmt->execute([':ruta' => $idRuta, ':domicilio' => $idDomicilio, ':excluir' => $excluirId]);

        return (bool) $stmt->fetchColumn();
    }

    // Registrar una nueva entrega con estado Pendiente.
    public static function insertar(int $idRuta, int $idDomicilio, string $fechaProgramada, string $horaProgramada): int {
        $sql = "INSERT INTO InfoLogistica.Entrega
                (id_ruta, id_domicilio, fecha_programada, hora_programada, estado_entrega)
                VALUES (:ruta, :domicilio, :fecha, :hora, 'Pendiente')
                RETURNING id_entrega";

        $stmt = conectar()->prepare($sql);
        $stmt->execute([
            ':ruta' => $idRuta,
            ':domicilio' => $idDomicilio,
            ':fecha' => $fechaProgramada,
            ':hora' => $horaProgramada
        ]);

        return (int) $stmt->fetchColumn();
    }

    // Modificar una entrega existente.
    // Al pasar a 'Completado', el disparador trg_completar_entrega llena fecha y hora real.
    // Si deja de estar 'Completado', se limpian la fecha y hora real.
    public static function actualizar(
        int $id,
        int $idRuta,
        int $idDomicilio,
        string $fechaProgramada,
        string $horaProgramada,
        string $estado
    ): int {
        $sql = "UPDATE InfoLogistica.Entrega SET
                id_ruta = :ruta,
                id_domicilio = :domicilio,
                fecha_programada = :fecha,
                hora_programada = :hora,
                estado_entrega = :estado,
                fecha_entrega_real = CASE WHEN :estado_c = 'Completado' THEN fecha_entrega_real ELSE NULL END,
                hora_entrega_real  = CASE WHEN :estado_h = 'Completado' THEN hora_entrega_real  ELSE NULL END
                WHERE id_entrega = :id";

        $stmt = conectar()->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':ruta' => $idRuta,
            ':domicilio' => $idDomicilio,
            ':fecha' => $fechaProgramada,
            ':hora' => $horaProgramada,
            ':estado' => $estado,
            ':estado_c' => $estado,
            ':estado_h' => $estado
        ]);

        return $stmt->rowCount();
    }

    // Eliminar una entrega por su ID.
    public static function eliminar(int $id): int {
        $stmt = conectar()->prepare('DELETE FROM InfoLogistica.Entrega WHERE id_entrega = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
