<?php
// app/Models/CambioToner.php
require_once __DIR__ . '/../../config/db.php';

class CambioToner {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    /**
     * Devuelve todos los cambios de tóner, opcionalmente filtrados por impresora.
     *
     * @param int|null $impresoraId
     * @return array
     */
    public function all(int $impresoraId = null): array {
        $sql = "
            SELECT 
              c.id,
              c.impresora_id,
              c.color,
              c.fecha,
              c.contador,
              c.stock_actual,
              c.toner_inventario_id,
              i.nombre AS impresora,
              t.modelo AS toner_modelo
            FROM cambios_toner c
            JOIN impresoras i ON i.id = c.impresora_id
            LEFT JOIN toner_inventario t ON t.id = c.toner_inventario_id
            WHERE 1=1
        ";
        $params = [];
        if ($impresoraId) {
            $sql .= " AND c.impresora_id = ?";
            $params[] = $impresoraId;
        }
        $sql .= " ORDER BY c.fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar un nuevo cambio de tóner
     *
     * @param int $impresoraId
     * @param string $color
     * @param int $contador
     * @param int|null $tonerInventarioId
     * @return bool
     */
    public function registrar(int $impresoraId, string $color, int $contador, ?int $tonerInventarioId = null): bool {
        $sql = "INSERT INTO cambios_toner 
                (impresora_id, color, contador, toner_inventario_id, fecha, stock_actual) 
                VALUES (?, ?, ?, ?, NOW(), 156)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$impresoraId, $color, $contador, $tonerInventarioId]);
    }
}