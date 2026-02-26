<?php
// app/models/Printer.php

class Printer {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM impresoras ORDER BY id");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM impresoras WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($nombre, $ip) {
        $stmt = $this->pdo->prepare("INSERT INTO impresoras (nombre, ip) VALUES (?, ?)");
        return $stmt->execute([$nombre, $ip]);
    }

    public function update($id, $nombre, $ip) {
        $stmt = $this->pdo->prepare("UPDATE impresoras SET nombre = ?, ip = ? WHERE id = ?");
        return $stmt->execute([$nombre, $ip, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM impresoras WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
