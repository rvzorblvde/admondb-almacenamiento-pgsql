<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class Ruta {
    public const ESTADOS = ['Programada', 'En curso', 'Completada', 'Cancelada'];

    private const COLUMNAS = 'r.id_ruta, r.id_transporte, r.id_transportista, r.hora_salida, r.hora_estimada_llegada, r.zona_ruta, r.distancia_estimada, 
                                r.estado_ruta, r.nombre_ruta, r.fecha_ruta';

    // Consultar las rutas y mostrar los datos concatenados de sus llaves foráneas.
    public static function listar(): array {
        $sql = "SELECT " . self::COLUMNAS . ",
                t.tipo_unidad || ' - *' || t.placa || '*' AS transporte_display,
                tr.nombre_transportista || ' <' || split_part(tr.correo_transportista, '@', 2) || '>' AS transportista_display
                FROM InfoLogistica.Ruta r
                INNER JOIN InfoLogistica.Transporte t ON r.id_transporte = t.id_transporte
                INNER JOIN InfoPersona.Transportista tr ON r.id_transportista = tr.id_transportista
                ORDER BY r.id_ruta DESC";

        return conectar()->query($sql)->fetchAll();
    }

    // Buscar una ruta por su ID.
    public static function buscarPorId(int $id): ?array {
        $stmt = conectar()->prepare('SELECT ' . self::COLUMNAS . ' FROM InfoLogistica.Ruta r WHERE r.id_ruta = :id');

        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Obtener los transportes para el selector.
    public static function listarTransportes(): array {
        $sql = "SELECT id_transporte, tipo_unidad || ' - *' || placa || '*' AS transporte_display
                FROM InfoLogistica.Transporte
                ORDER BY tipo_unidad, placa";

        return conectar()->query($sql)->fetchAll();
    }

    // Obtener los transportistas para el selector.
    public static function listarTransportistas(): array {
        $sql = "SELECT id_transportista,
                nombre_transportista || ' <' || split_part(correo_transportista, '@', 2) || '>' AS transportista_display
                FROM InfoPersona.Transportista
                ORDER BY nombre_transportista";

        return conectar()->query($sql)->fetchAll();
    }

    // Comprobar que el transporte y el transportista estén disponibles.
    public static function validarDisponibilidad(
        int $idTransporte,
        int $idTransportista,
        string $fecha,
        string $horaSalida,
        string $horaLlegada,
        int $idRutaActual = 0
    ): string {
        $pdo = conectar();

        // Verificar que el transporte exista y no esté en mantenimiento.
        $stmt = $pdo->prepare('SELECT estado FROM InfoLogistica.Transporte WHERE id_transporte = :id');
        $stmt->execute([':id' => $idTransporte]);
        $estadoTransporte = $stmt->fetchColumn();

        if ($estadoTransporte === false)
            return 'El transporte seleccionado no existe.';

        if ($estadoTransporte === 'Mantenimiento')
            return 'El transporte seleccionado se encuentra en mantenimiento.';

        // Verificar que el transportista exista.
        $stmt = $pdo->prepare('SELECT estado FROM InfoPersona.Transportista WHERE id_transportista = :id');
        $stmt->execute([':id' => $idTransportista]);

        if ($stmt->fetchColumn() === false)
            return 'El transportista seleccionado no existe.';

        // Buscar rutas activas del mismo transporte que coincidan en horario.
        $sql = "SELECT 1 FROM InfoLogistica.Ruta
                WHERE id_transporte = :id
                AND fecha_ruta = :fecha
                AND id_ruta <> :id_actual
                AND estado_ruta IN ('Programada', 'En curso')
                AND :salida < hora_estimada_llegada
                AND hora_salida < :llegada
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $idTransporte,
            ':fecha' => $fecha,
            ':id_actual' => $idRutaActual,
            ':salida' => $horaSalida,
            ':llegada' => $horaLlegada
        ]);

        if ($stmt->fetchColumn() !== false)
            return 'El transporte ya tiene una ruta que coincide con este horario.';

        // Buscar rutas activas del mismo transportista que coincidan en horario.
        $sql = "SELECT 1 FROM InfoLogistica.Ruta
                WHERE id_transportista = :id
                AND fecha_ruta = :fecha
                AND id_ruta <> :id_actual
                AND estado_ruta IN ('Programada', 'En curso')
                AND :salida < hora_estimada_llegada
                AND hora_salida < :llegada
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $idTransportista,
            ':fecha' => $fecha,
            ':id_actual' => $idRutaActual,
            ':salida' => $horaSalida,
            ':llegada' => $horaLlegada
        ]);

        if ($stmt->fetchColumn() !== false)
            return 'El transportista ya tiene una ruta que coincide con este horario.';

        return '';
    }

    // Registrar una nueva ruta con estado Programada.
    public static function insertar(
        int $idTransporte,
        int $idTransportista,
        string $horaSalida,
        string $horaLlegada,
        string $zona,
        string $distancia,
        string $nombre,
        string $fecha
    ): int {
        $sql = "INSERT INTO InfoLogistica.Ruta
                (id_transporte, id_transportista, hora_salida, hora_estimada_llegada,
                zona_ruta, distancia_estimada, estado_ruta, nombre_ruta, fecha_ruta)
                VALUES (:transporte, :transportista, :salida, :llegada,
                :zona, :distancia, 'Programada', :nombre, :fecha)
                RETURNING id_ruta";

        $stmt = conectar()->prepare($sql);
        $stmt->execute([
            ':transporte' => $idTransporte,
            ':transportista' => $idTransportista,
            ':salida' => $horaSalida,
            ':llegada' => $horaLlegada,
            ':zona' => $zona,
            ':distancia' => $distancia,
            ':nombre' => $nombre,
            ':fecha' => $fecha
        ]);

        return (int) $stmt->fetchColumn();
    }

    // Modificar una ruta existente.
    public static function actualizar(
        int $id,
        int $idTransporte,
        int $idTransportista,
        string $horaSalida,
        string $horaLlegada,
        string $zona,
        string $distancia,
        string $estado,
        string $nombre,
        string $fecha
    ): int {
        $sql = "UPDATE InfoLogistica.Ruta SET
                id_transporte = :transporte,
                id_transportista = :transportista,
                hora_salida = :salida,
                hora_estimada_llegada = :llegada,
                zona_ruta = :zona,
                distancia_estimada = :distancia,
                estado_ruta = :estado,
                nombre_ruta = :nombre,
                fecha_ruta = :fecha
                WHERE id_ruta = :id";

        $stmt = conectar()->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':transporte' => $idTransporte,
            ':transportista' => $idTransportista,
            ':salida' => $horaSalida,
            ':llegada' => $horaLlegada,
            ':zona' => $zona,
            ':distancia' => $distancia,
            ':estado' => $estado,
            ':nombre' => $nombre,
            ':fecha' => $fecha
        ]);

        return $stmt->rowCount();
    }

    // Eliminar una ruta por su ID.
    public static function eliminar(int $id): int {
        $stmt = conectar()->prepare('DELETE FROM InfoLogistica.Ruta WHERE id_ruta = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
