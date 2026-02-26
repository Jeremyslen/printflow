<?php
// app/Models/Lectura.php

require_once __DIR__ . '/../../config/db.php';

class Lectura {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function contarConFiltros($impresoraId = null, $fecha = null) {
        $sql = "
            SELECT COUNT(*)
            FROM lecturas l
            JOIN impresoras i ON i.id = l.impresora_id
            WHERE 1=1
        ";
        $params = [];

        if ($impresoraId) {
            $sql .= " AND l.impresora_id = ?";
            $params[] = $impresoraId;
        }
        if ($fecha) {
            $sql .= " AND DATE(l.fecha_hora) = ?";
            $params[] = $fecha;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function obtenerConFiltros($impresoraId = null, $fecha = null, $limit = 10, $offset = 0) {
        $where = "WHERE 1=1";
        $params = [];

        if ($impresoraId) {
            $where .= " AND l.impresora_id = ?";
            $params[] = $impresoraId;
        }
        if ($fecha) {
            $where .= " AND DATE(l.fecha_hora) = ?";
            $params[] = $fecha;
        }

        $limit  = (int)$limit;
        $offset = (int)$offset;

        $sql = "
            SELECT 
                l.*,
                i.nombre AS nombre_impresora,
                i.ip     AS ip
            FROM lecturas l
            JOIN impresoras i ON i.id = l.impresora_id
            $where
            ORDER BY l.fecha_hora DESC
            LIMIT $limit
            OFFSET $offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLastReadings(int $impresoraId, int $limit = 30): array {
        $limit = max(1, min(1000, $limit));
        $sql = "
            SELECT fecha_hora, contador_total,
                   toner_black, toner_cyan, toner_magenta, toner_yellow
            FROM lecturas
            WHERE impresora_id = ?
            ORDER BY fecha_hora DESC
            LIMIT {$limit}
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$impresoraId]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Nuevo: datos de la semana actual
    public function getCurrentWeekData(int $impresoraId): array {
        $sql = "
            SELECT fecha_hora, contador_total,
                   toner_black, toner_cyan, toner_magenta, toner_yellow
            FROM lecturas
            WHERE impresora_id = ?
              AND YEARWEEK(fecha_hora,1) = YEARWEEK(CURDATE(),1)
            ORDER BY fecha_hora ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$impresoraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nuevo: cierres semanales (último punto de cada semana pasada)
    public function getWeeklyClosuresData(int $impresoraId): array {
    $sql = "
        SELECT l.fecha_hora,
               l.contador_total,
               l.toner_black,
               l.toner_cyan,
               l.toner_magenta,
               l.toner_yellow
        FROM lecturas l
        JOIN (
            SELECT YEARWEEK(fecha_hora,1) AS yw,
                   MAX(fecha_hora)    AS max_fecha
            FROM lecturas
            WHERE impresora_id = ?
              AND YEARWEEK(fecha_hora,1) < YEARWEEK(CURDATE(),1)
            GROUP BY yw
        ) w ON YEARWEEK(l.fecha_hora,1) = w.yw
           AND l.fecha_hora = w.max_fecha
           AND l.impresora_id = ?
        ORDER BY l.fecha_hora ASC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$impresoraId, $impresoraId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método legado, puedes mantenerlo o retirarlo si no lo usas
    public function getChartData(int $impresoraId): array {
        $sql = "
            /* Cierres semanales de semanas pasadas */
            SELECT l.fecha_hora, l.contador_total,
                   l.toner_black, l.toner_cyan, l.toner_magenta, l.toner_yellow
            FROM lecturas l
            JOIN (
                SELECT YEARWEEK(fecha_hora,1) AS yw,
                       MAX(fecha_hora)    AS max_fecha
                FROM lecturas
                WHERE impresora_id = ?
                  AND YEARWEEK(fecha_hora,1) < YEARWEEK(CURDATE(),1)
                GROUP BY yw
            ) w ON YEARWEEK(l.fecha_hora,1)=w.yw
               AND l.fecha_hora = w.max_fecha
               AND l.impresora_id = ?

            UNION ALL

            /* Semana actual */
            SELECT fecha_hora, contador_total,
                   toner_black, toner_cyan, toner_magenta, toner_yellow
            FROM lecturas
            WHERE impresora_id = ?
              AND YEARWEEK(fecha_hora,1) = YEARWEEK(CURDATE(),1)

            ORDER BY fecha_hora ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$impresoraId, $impresoraId, $impresoraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
