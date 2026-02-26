<?php
require_once __DIR__ . '/../../config/db.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}