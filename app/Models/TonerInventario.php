
<?php
// app/Models/TonerInventario.php
require_once __DIR__ . '/../../config/db.php';

class TonerInventario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    /**
     * Obtener todos los tóners del inventario
     */
    public function all(): array {
        $sql = "SELECT * FROM toner_inventario ORDER BY modelo, color";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener tóners disponibles (cantidad > 0)
     */
    public function disponibles(): array {
        $sql = "SELECT * FROM toner_inventario WHERE cantidad_disponible > 0 ORDER BY modelo, color";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un tóner por ID
     */
    public function find(int $id): ?array {
        $sql = "SELECT * FROM toner_inventario WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear nuevo tóner en inventario
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO toner_inventario (modelo, color, cantidad_disponible, compatible_impresoras) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['modelo'],
            $data['color'],
            $data['cantidad_disponible'],
            $data['compatible_impresoras'] ?? null
        ]);
    }

    /**
     * Actualizar tóner existente
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE toner_inventario 
                SET modelo = ?, color = ?, cantidad_disponible = ?, compatible_impresoras = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['modelo'],
            $data['color'],
            $data['cantidad_disponible'],
            $data['compatible_impresoras'] ?? null,
            $id
        ]);
    }

    /**
     * Eliminar tóner
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM toner_inventario WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Descontar una unidad del inventario
     */
    public function descontar(int $id): bool {
        $sql = "UPDATE toner_inventario 
                SET cantidad_disponible = cantidad_disponible - 1 
                WHERE id = ? AND cantidad_disponible > 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Agregar unidades al inventario
     */
    public function agregar(int $id, int $cantidad): bool {
        $sql = "UPDATE toner_inventario 
                SET cantidad_disponible = cantidad_disponible + ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$cantidad, $id]);
    }
}