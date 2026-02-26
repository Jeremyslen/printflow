<?php
// scripts/rotate_logs.php

$logFile     = __DIR__ . '/../logs/scrape.log';
$archiveDir  = __DIR__ . '/../logs/archive/';
$rotateAfter = 7 * 24 * 60 * 60; 

if (!file_exists($logFile)) {
    
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LOG CREATED\n");
    exit;
}

// Verificar antigüedad del archivo
$lastModified = filemtime($logFile);
$now = time();

if (($now - $lastModified) >= $rotateAfter) {
    if (!is_dir($archiveDir)) mkdir($archiveDir, 0755, true);

    $archivedName = 'scrape-' . date('Ymd-His', $lastModified) . '.log';
    rename($logFile, $archiveDir . $archivedName);

    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] LOG ROTATED: moved to archive/{$archivedName}\n");
}
