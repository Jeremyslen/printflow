<?php

class ScraperController {
    public function ejecutar() {
        $script = __DIR__ . '/../../scripts/scrape_counters.php';

        // 1) Marca el inicio de la llamada manual
        file_put_contents(__DIR__ . '/../../logs/scrape.log',
            "[".date('Y-m-d H:i:s')."] Ejecutado manualmente desde interfaz.\n",
            FILE_APPEND
        );

        // 2) Ejecuta el script y captura TODO (stdout + stderr)
        $output = shell_exec("php \"$script\" 2>&1");

        // 3) Guarda la salida en el log para depurar
        file_put_contents(__DIR__ . '/../../logs/scrape.log',
            $output . "\n",
            FILE_APPEND
        );

        // 4) Redirige como siempre
        header('Location: ?controller=lectura&action=index&actualizado=1');
        exit;
    }
}

