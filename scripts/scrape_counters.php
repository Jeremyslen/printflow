<?php
// scripts/scrape_counters.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Modelos
require_once __DIR__ . '/../app/Models/Umbral.php';
require_once __DIR__ . '/../app/Models/Mantenimiento.php';
require_once __DIR__ . '/../app/Models/TonerInventario.php';

$pdo     = Database::connect();
$logFile = __DIR__ . '/../logs/scrape.log';

$emailTo    = 'ti@tesa.edu.ec';
$emailFrom  = 'impresorastesa@gmail.com';
$emailSubj  = 'PrintFlow Alertas';

function alertaExistente($pdo, $impresoraId, $color, $tipo) {
    $sql  = "SELECT 1 FROM alertas_toner WHERE impresora_id=? AND color=? AND tipo_alerta=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$impresoraId, $color, $tipo]);
    return (bool)$stmt->fetchColumn();
}

function registrarAlerta($pdo, $impresoraId, $color, $tipo) {
    $sql = "INSERT IGNORE INTO alertas_toner (impresora_id, color, tipo_alerta) VALUES (?, ?, ?)";
    $pdo->prepare($sql)->execute([$impresoraId, $color, $tipo]);
}

function enviarAlertaSMTP($to, $subject, $htmlBody, $plainBody) {
    global $logFile, $emailFrom;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'impresorastesa@gmail.com';
        $mail->Password   = 'czmz vszz uool zxrq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($emailFrom, 'TESA IMPRESORAS Alertas');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody;
        $mail->CharSet = 'UTF-8';

        $mail->send();
        file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] ALERTA EMAIL enviado a {$to}: {$subject}\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] ERROR MAILER: {$mail->ErrorInfo}\n", FILE_APPEND);
        return false;
    }
}

function curl_fetch_with_headers($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Pragma: no-cache',
        'Referer: http://dummy',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_COOKIE, 'cookieOnOffChecker=on');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $html = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if (!$html) {
        throw new Exception("cURL Error ({$url}): " . ($err ?: 'Sin respuesta'));
    }
    return $html;
}

file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] INICIO DEL SCRIPT\n", FILE_APPEND);

// ========== CONSTANTES DE CONFIGURACIÓN ==========
$baseWidth      = 156;  // Ancho REAL máximo de la barra de tóner Ricoh (100%)
$umbralBajo     = 39;   // Alerta cuando tóner <= 25% (39px de 156px)
$umbralAgotado  = 0;    // Tóner completamente agotado

$umbralModel = new Umbral();
$mModel      = new Mantenimiento();
$tonerInvModel = new TonerInventario(); // 🆕 Modelo de inventario

// 0) Cargar impresoras
$printers = $pdo->query("SELECT id, nombre, ip FROM impresoras")->fetchAll();
if (empty($printers)) {
    file_put_contents($logFile, "No hay impresoras registradas\n", FILE_APPEND);
    exit;
}

foreach ($printers as $printer) {
    $id     = $printer['id'];
    $nombre = $printer['nombre'];
    $ip     = $printer['ip'];

    file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] Consultando: {$nombre} ({$ip})\n", FILE_APPEND);

    try {
        // 1) Contador total
        $countUrl  = "http://{$ip}/web/guest/es/websys/status/getUnificationCounter.cgi";
        $htmlCount = curl_fetch_with_headers($countUrl);
        if (preg_match('/Contador\s*total<\/td>.*?<td[^>]*>\s*([\d,]+)\s*<\/td>/is', $htmlCount, $m1)) {
            $count = (int)str_replace(',', '', $m1[1]);
            file_put_contents($logFile, "Contador capturado: {$count}\n", FILE_APPEND);
        } else {
            throw new Exception("Contador no encontrado en el HTML");
        }

        // 2) Niveles de tóner: ancho real en px
        $tonerUrl  = "http://{$ip}/web/guest/es/websys/webArch/getStatus.cgi";
        $htmlToner = curl_fetch_with_headers($tonerUrl);
        $colors    = [
            'Negro'    => 'deviceStTnBarK.gif',
            'Cian'     => 'deviceStTnBarC.gif',
            'Magenta'  => 'deviceStTnBarM.gif',
            'Amarillo' => 'deviceStTnBarY.gif',
        ];
        $tonerLevelsPx = [];
        foreach ($colors as $name => $imgFile) {
            if (preg_match('/'.preg_quote($imgFile, '/').'"[^>]*width="(\d+)"/i', $htmlToner, $m2)) {
                $w = (int)$m2[1];
            } else {
                $w = 0;
            }
            $tonerLevelsPx[$name] = max(0, min($w, $baseWidth));
        }

        // 3) Alertas de tóner (basadas en píxeles y porcentajes)
        foreach ($tonerLevelsPx as $color => $px) {
            if ($px === $umbralAgotado) {
                // AGOTADO
                $html  = "<p>Estimado responsable,</p>"
                       . "<p>El tóner de color <strong>{$color}</strong> en la impresora "
                       . "<strong>{$nombre}</strong> (IP: {$ip}) está <strong>AGOTADO</strong>.</p>";
                $plain = "Estimado responsable,\n\n"
                       . "El tóner de color {$color} en la impresora {$nombre} está AGOTADO.\n";
                if (enviarAlertaSMTP($emailTo, "Tóner AGOTADO: {$nombre}", $html, $plain)) {
                    registrarAlerta($pdo, $id, $color, 'agotado');
                }

            } elseif ($px <= $umbralBajo) {
                // BAJO - Alerta al 25%
                if (!alertaExistente($pdo, $id, $color, 'bajo')) {
                    $porcentaje = round($px / $baseWidth * 100, 1);
                    $html  = "<p>Estimado responsable,</p>"
                           . "<p>El tóner de color <strong>{$color}</strong> en la impresora "
                           . "<strong>{$nombre}</strong> (IP: {$ip}) está <strong>BAJO</strong> "
                           . "({$porcentaje}% restante).</p>";
                    $plain = "Estimado responsable,\n\n"
                           . "El tóner de color {$color} en la impresora {$nombre} está BAJO ({$porcentaje}% restante).\n";
                    if (enviarAlertaSMTP($emailTo, "Tóner BAJO: {$nombre}", $html, $plain)) {
                        registrarAlerta($pdo, $id, $color, 'bajo');
                    }
                }
            } else {
                // 🆕 REPUESTO: si hubo alerta "bajo", registramos cambio, descontamos inventario y borramos alerta
                if (alertaExistente($pdo, $id, $color, 'bajo')) {
                    $porcentajeActual = round($px / $baseWidth * 100);
                    
                    // 🔍 Buscar tóner disponible en inventario del mismo color
                    $tonerInventarioId = null;
                    $tonersDisponibles = $tonerInvModel->disponibles();
                    
                    foreach ($tonersDisponibles as $toner) {
                        // Comparar color (normalizar mayúsculas/minúsculas)
                        $colorToner = strtolower(trim($toner['color']));
                        $colorCambio = strtolower(trim($color));
                        
                        // Mapeo de nombres alternativos
                        $mapeoColores = [
                            'negro' => ['negro', 'black'],
                            'cian' => ['cian', 'cyan'],
                            'magenta' => ['magenta'],
                            'amarillo' => ['amarillo', 'yellow'],
                        ];
                        
                        foreach ($mapeoColores as $colorBase => $variantes) {
                            if (in_array($colorToner, $variantes) && in_array($colorCambio, $variantes)) {
                                $tonerInventarioId = $toner['id'];
                                break 2; // Salir de ambos loops
                            }
                        }
                    }
                    
                    // Registrar cambio con o sin inventario
                    $stmt = $pdo->prepare("
                        INSERT INTO cambios_toner
                          (impresora_id, color, fecha, contador, stock_actual, toner_inventario_id)
                        VALUES (?, ?, NOW(), ?, ?, ?)
                    ");
                    $stmt->execute([$id, $color, $count, $px, $tonerInventarioId]);
                    
                    // 🔽 Descontar del inventario si encontramos el tóner
                    if ($tonerInventarioId) {
                        if ($tonerInvModel->descontar($tonerInventarioId)) {
                            file_put_contents($logFile,
                                "[".date('Y-m-d H:i:s')."] ✅ Tóner ID {$tonerInventarioId} descontado del inventario ({$color})\n",
                                FILE_APPEND
                            );
                        } else {
                            file_put_contents($logFile,
                                "[".date('Y-m-d H:i:s')."] ⚠️ No se pudo descontar tóner ID {$tonerInventarioId} (sin stock?)\n",
                                FILE_APPEND
                            );
                        }
                    } else {
                        file_put_contents($logFile,
                            "[".date('Y-m-d H:i:s')."] ⚠️ No se encontró tóner disponible en inventario para color: {$color}\n",
                            FILE_APPEND
                        );
                    }
                    
                    // Eliminar alerta
                    $pdo->prepare("
                        DELETE FROM alertas_toner
                         WHERE impresora_id=? AND color=? AND tipo_alerta='bajo'
                    ")->execute([$id, $color]);
                    
                    file_put_contents($logFile,
                        "[".date('Y-m-d H:i:s')."] Cambio de tóner registrado para {$nombre}-{$color} (nuevo nivel: {$porcentajeActual}%)\n",
                        FILE_APPEND
                    );
                }
            }
        }

        // 4) Alerta de mantenimiento
        $lastM  = $mModel->lastForPrinter($id);
        $umbral = $umbralModel->getForPrinter($id);
        if ($umbral > 0 && ($count - $lastM) >= $umbral) {
            if (!alertaExistente($pdo, $id, 'mantenimiento', 'bajo')) {
                $diff = $count - $lastM;
                $html  = "<p>📢 <strong>Mantenimiento próximo</strong></p>"
                       . "<p>Es necesario mantenimiento en <strong>{$nombre}</strong>: "
                       . "<strong>{$diff}</strong> impresiones desde el último.</p>";
                $plain = "Mantenimiento próximo en {$nombre}: {$diff} impresiones.";
                if (enviarAlertaSMTP($emailTo, "Mantenimiento próximo: {$nombre}", $html, $plain)) {
                    registrarAlerta($pdo, $id, 'mantenimiento', 'bajo');
                }
            }
        }

        // 5) Guardar lectura en BD (guardamos px tal cual):
        $stmt = $pdo->prepare("
            INSERT INTO lecturas
              (impresora_id, fecha_hora, contador_total,
               toner_black, toner_cyan, toner_magenta, toner_yellow)
            VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id, $count,
            $tonerLevelsPx['Negro'],
            $tonerLevelsPx['Cian'],
            $tonerLevelsPx['Magenta'],
            $tonerLevelsPx['Amarillo'],
        ]);
        file_put_contents($logFile,
            "[".date('Y-m-d H:i:s')."] OK: {$nombre} -> contador {$count}, tóner guardado en px\n",
            FILE_APPEND
        );

    } catch (Exception $e) {
        file_put_contents($logFile,
            "[".date('Y-m-d H:i:s')."] ERROR: {$nombre}: {$e->getMessage()}\n",
            FILE_APPEND
        );
    }
}

file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] FIN DEL SCRIPT\n\n", FILE_APPEND);