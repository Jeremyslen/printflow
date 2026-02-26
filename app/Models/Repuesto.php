<?php
// app/Models/Repuesto.php
require_once __DIR__ . '/../../config/db.php';

class Repuesto {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::connect();
    }

    /**
     * Devuelve todos los repuestos, opcionalmente filtrados por impresora.
     *
     * @param int|null $impresoraId
     * @return array
     */
    public function all(?int $impresoraId = null): array {
        $sql = "SELECT r.*, i.nombre AS impresora
                FROM repuestos r
                JOIN impresoras i ON i.id = r.impresora_id
                WHERE 1";
        $params = [];
        if ($impresoraId !== null) {
            $sql .= " AND r.impresora_id = ?";
            $params[] = $impresoraId;
        }
        $sql .= " ORDER BY r.fecha DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar JSON de fotos
        foreach ($rows as &$row) {
            $row['fotos'] = $row['fotos'] ? json_decode($row['fotos'], true) : [];
        }

        return $rows;
    }

    public function find(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM repuestos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['fotos']) {
            $row['fotos'] = json_decode($row['fotos'], true);
        }
        return $row;
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO repuestos
            (impresora_id, fecha, descripcion, archivo_factura, fotos)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['impresora_id'],
            $data['fecha'],
            $data['descripcion'],
            $data['archivo_factura'] ?? null,
            isset($data['fotos']) ? json_encode($data['fotos']) : null,
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE repuestos SET
              impresora_id    = ?,
              fecha           = ?,
              descripcion     = ?,
              archivo_factura = ?,
              fotos           = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['impresora_id'],
            $data['fecha'],
            $data['descripcion'],
            $data['archivo_factura'] ?? null,
            isset($data['fotos']) ? json_encode($data['fotos']) : null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM repuestos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
