<?php
// app/Models/Umbral.php
require_once __DIR__ . '/../../config/db.php';

class Umbral {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::connect();
    }

    /**
     * Devuelve el valor de contador_mantenimiento para la impresora,
     * o 0 si no hay fila.
     */
    public function getForPrinter(int $impresoraId): int {
        $stmt = $this->pdo->prepare("
            SELECT contador_mantenimiento
            FROM umbrales
            WHERE impresora_id = ?
        ");
        $stmt->execute([$impresoraId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : 0;
    }

    /**
     * Inserta o actualiza el umbral para una impresora.
     */
    public function upsert(int $impresoraId, int $contador): bool {
        // REPLACE hace INSERT o DELETE+INSERT
        $stmt = $this->pdo->prepare("
            REPLACE INTO umbrales (impresora_id, contador_mantenimiento)
            VALUES (?, ?)
        ");
        return $stmt->execute([$impresoraId, $contador]);
    }
}
