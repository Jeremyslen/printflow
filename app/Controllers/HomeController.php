<?php
// app/Controllers/HomeController.php

class HomeController {
    private $db;
    
    public function __construct() {
        // Usar el método connect() de tu clase Database
        try {
            $this->db = Database::connect();
        } catch (Exception $e) {
            error_log("Error conectando a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos");
        }
    }
    
    public function index() {
        // Obtener estadísticas generales
        $stats = $this->getStatistics();
        
        // Obtener impresoras con alertas
        $alertas = $this->getAlertas();
        
        // Obtener mantenimientos próximos
        $mantenimientosProximos = $this->getMantenimientosProximos();
        
        // Obtener actividad reciente
        $actividadReciente = $this->getActividadReciente();
        
        return [
            'view' => __DIR__ . '/../Views/home/index.php',
            'vars' => [
                'pageTitle' => 'Inicio',
                'stats' => $stats,
                'alertas' => $alertas,
                'mantenimientosProximos' => $mantenimientosProximos,
                'actividadReciente' => $actividadReciente
            ]
        ];
    }
    
    private function getStatistics() {
        $stats = [];
        
        try {
            // Total de impresoras
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM impresoras WHERE activa = 1");
            $stats['totalImpresoras'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Impresoras activas hoy
            $stmt = $this->db->query("
                SELECT COUNT(DISTINCT impresora_id) as activas 
                FROM lecturas 
                WHERE DATE(fecha_lectura) = CURDATE()
            ");
            $stats['impresorasActivas'] = $stmt->fetch(PDO::FETCH_ASSOC)['activas'];
            
            // Total de páginas impresas hoy
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(paginas_impresas), 0) as total 
                FROM lecturas 
                WHERE DATE(fecha_lectura) = CURDATE()
            ");
            $stats['paginasHoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Páginas impresas este mes
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(paginas_impresas), 0) as total 
                FROM lecturas 
                WHERE YEAR(fecha_lectura) = YEAR(CURDATE()) 
                AND MONTH(fecha_lectura) = MONTH(CURDATE())
            ");
            $stats['paginasMes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Impresoras con alertas
            $stmt = $this->db->query("
                SELECT COUNT(*) as alertas 
                FROM impresoras i
                LEFT JOIN lecturas l ON i.id = l.impresora_id
                LEFT JOIN umbrales u ON i.id = u.impresora_id
                WHERE i.activa = 1 
                AND (
                    (l.nivel_toner_negro IS NOT NULL AND l.nivel_toner_negro <= COALESCE(u.umbral_toner_negro, 10))
                    OR (l.nivel_toner_cyan IS NOT NULL AND l.nivel_toner_cyan <= COALESCE(u.umbral_toner_cyan, 10))
                    OR (l.nivel_toner_magenta IS NOT NULL AND l.nivel_toner_magenta <= COALESCE(u.umbral_toner_magenta, 10))
                    OR (l.nivel_toner_amarillo IS NOT NULL AND l.nivel_toner_amarillo <= COALESCE(u.umbral_toner_amarillo, 10))
                )
                AND l.fecha_lectura = (
                    SELECT MAX(fecha_lectura) 
                    FROM lecturas 
                    WHERE impresora_id = i.id
                )
            ");
            $stats['alertas'] = $stmt->fetch(PDO::FETCH_ASSOC)['alertas'];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            $stats = [
                'totalImpresoras' => 0,
                'impresorasActivas' => 0,
                'paginasHoy' => 0,
                'paginasMes' => 0,
                'alertas' => 0
            ];
        }
        
        return $stats;
    }
    
    private function getAlertas() {
        $alertas = [];
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    i.nombre,
                    i.ubicacion,
                    l.nivel_toner_negro,
                    l.nivel_toner_cyan,
                    l.nivel_toner_magenta,
                    l.nivel_toner_amarillo,
                    l.fecha_lectura,
                    u.umbral_toner_negro,
                    u.umbral_toner_cyan,
                    u.umbral_toner_magenta,
                    u.umbral_toner_amarillo
                FROM impresoras i
                LEFT JOIN lecturas l ON i.id = l.impresora_id
                LEFT JOIN umbrales u ON i.id = u.impresora_id
                WHERE i.activa = 1 
                AND l.fecha_lectura = (
                    SELECT MAX(fecha_lectura) 
                    FROM lecturas 
                    WHERE impresora_id = i.id
                )
                AND (
                    (l.nivel_toner_negro IS NOT NULL AND l.nivel_toner_negro <= COALESCE(u.umbral_toner_negro, 10))
                    OR (l.nivel_toner_cyan IS NOT NULL AND l.nivel_toner_cyan <= COALESCE(u.umbral_toner_cyan, 10))
                    OR (l.nivel_toner_magenta IS NOT NULL AND l.nivel_toner_magenta <= COALESCE(u.umbral_toner_magenta, 10))
                    OR (l.nivel_toner_amarillo IS NOT NULL AND l.nivel_toner_amarillo <= COALESCE(u.umbral_toner_amarillo, 10))
                )
                ORDER BY l.fecha_lectura DESC
                LIMIT 5
            ");
            
            $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo alertas: " . $e->getMessage());
        }
        
        return $alertas;
    }
    
    private function getMantenimientosProximos() {
        $mantenimientos = [];
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    i.nombre,
                    i.ubicacion,
                    m.fecha_programada,
                    m.tipo_mantenimiento,
                    m.descripcion,
                    DATEDIFF(m.fecha_programada, CURDATE()) as dias_restantes
                FROM mantenimientos m
                JOIN impresoras i ON m.impresora_id = i.id
                WHERE m.fecha_programada >= CURDATE()
                AND m.fecha_programada <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND m.completado = 0
                ORDER BY m.fecha_programada ASC
                LIMIT 5
            ");
            
            $mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo mantenimientos próximos: " . $e->getMessage());
        }
        
        return $mantenimientos;
    }
    
    private function getActividadReciente() {
        $actividad = [];
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    i.nombre,
                    i.ubicacion,
                    l.paginas_impresas,
                    l.fecha_lectura,
                    'lectura' as tipo
                FROM lecturas l
                JOIN impresoras i ON l.impresora_id = i.id
                WHERE l.fecha_lectura >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY l.fecha_lectura DESC
                LIMIT 10
            ");
            
            $actividad = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo actividad reciente: " . $e->getMessage());
        }
        
        return $actividad;
    }
}