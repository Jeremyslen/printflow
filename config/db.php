<?php
// config/db.php

class Database {
    private static $host = '127.0.0.1';
    private static $db   = 'printer_monitor';
    private static $user = 'root';
    private static $pass = '';
    private static $opt  = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    public static function connect() {
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4";
        return new PDO($dsn, self::$user, self::$pass, self::$opt);
    }
}
