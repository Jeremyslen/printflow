<?php
// app/Models/Mantenimiento.php
require_once __DIR__ . '/../../config/db.php';

class Mantenimiento {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::connect();
    }

    /**
     * Devuelve todos los mantenimientos, opcionalmente filtrados por impresora.
     *
     * @param int|null $impresoraId
     * @return array
     */
    public function all(?int $impresoraId = null): array {
        $sql = "SELECT m.*, i.nombre AS impresora
                FROM mantenimientos m
                JOIN impresoras i ON i.id = m.impresora_id
                WHERE 1";
        $params = [];
        if ($impresoraId !== null) {
            $sql .= " AND m.impresora_id = ?";
            $params[] = $impresoraId;
        }
        $sql .= " ORDER BY m.fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM mantenimientos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO mantenimientos
            (impresora_id, tipo, empresa, ruc, fecha, contador, observaciones, archivo_informe)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['impresora_id'],
            $data['tipo'],
            $data['empresa'],
            $data['ruc'],
            $data['fecha'],
            $data['contador'],
            $data['observaciones'] ?? null,
            $data['archivo_informe'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE mantenimientos SET
              impresora_id    = ?,
              tipo            = ?,
              empresa         = ?,
              ruc             = ?,
              fecha           = ?,
              contador        = ?,
              observaciones   = ?,
              archivo_informe = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['impresora_id'],
            $data['tipo'],
            $data['empresa'],
            $data['ruc'],
            $data['fecha'],
            $data['contador'],
            $data['observaciones'] ?? null,
            $data['archivo_informe'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM mantenimientos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function lastForPrinter(int $impresoraId): int {
        $stmt = $this->pdo->prepare("
            SELECT contador
            FROM mantenimientos
            WHERE impresora_id = ?
            ORDER BY fecha DESC
            LIMIT 1
        ");
        $stmt->execute([$impresoraId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : 0;
    }
}
