<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - PrintFlow TESA' : 'PrintFlow TESA - Sistema de Gestión de Impresoras'; ?></title>
    
    <!-- CSS base siempre -->
    <link rel="stylesheet" href="css/base.css">
    
    <!-- CSS específicos según la sección -->
    <?php
    $controller = $_GET['controller'] ?? 'home';
    $action = $_GET['action'] ?? 'index';
    
    if ($controller === 'home') {
        echo '<link rel="stylesheet" href="css/home.css">';
    } elseif ($controller === 'printer') {
        if ($action === 'index') {
            echo '<link rel="stylesheet" href="css/impresoras.css">';
        } elseif ($action === 'add' || $action === 'edit') {
            echo '<link rel="stylesheet" href="css/nueva.css">';
        }
    } elseif ($controller === 'lectura') {
        echo '<link rel="stylesheet" href="css/base.css">';
    } elseif ($controller === 'mantenimiento') {
        echo '<link rel="stylesheet" href="css/mantenimiento.css">';
    } elseif ($controller === 'umbral') {
        echo '<link rel="stylesheet" href="css/umbral.css">';
    } elseif ($controller === 'reports') {
        echo '<link rel="stylesheet" href="css/reports.css">';
    }
    ?>
</head>
<body class="<?php echo ($controller === 'lectura') ? 'historial-page' : ($controller === 'home' ? 'home-page' : ''); ?>">
    <!-- Navbar -->
    <nav class="navbar">
        <a class="navbar-brand" href="index.php">
            <span class="logo">🖨️</span>
            PrintFlow <small>TESA</small>
        </a>
        <div class="navbar-nav">
            <a class="nav-link <?php echo ($controller === 'home') ? 'active' : ''; ?>" href="index.php">
                🏠 Inicio
            </a>
            <a class="nav-link <?php echo ($controller === 'printer') ? 'active' : ''; ?>" href="index.php?controller=printer&action=index">
                📋 Impresoras
            </a>
            
            <a class="nav-link <?php echo ($controller === 'lectura') ? 'active' : ''; ?>" href="index.php?controller=lectura&action=index">
                📊 Historial
            </a>
            <a class="nav-link <?php echo ($controller === 'mantenimiento') ? 'active' : ''; ?>" href="index.php?controller=mantenimiento&action=index">
                🛠️ Mantenimientos
            </a>
            
            <a class="nav-link" href="index.php?controller=repuesto&action=index">
            🛠️ Repuestos
            </a>
            <a class="nav-link <?php echo ($controller === 'reports') ? 'active' : ''; ?>" href="index.php?controller=reports&action=charts">
                📈 Gráficos
            </a>
            <a class="nav-link" href="/PrintFlow/public/logout">
                🚪 Salir (<?php echo $_SESSION['usuario_name'] ?? ''; ?>)
            </a>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Mensajes -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Contenido -->
        <main>
            <?php
            if (!empty($viewFile) && file_exists($viewFile)) {
                include $viewFile;
            } else {
                ?>
                <div class="card">
                    <div class="card-header">
                        <h2>🚫 Vista no encontrada</h2>
                    </div>
                    <div class="empty-state">
                        <h3>Página no encontrada</h3>
                        <p>La vista solicitada no existe.</p>
                        <a href="index.php" class="btn btn-primary">Volver al inicio</a>
                    </div>
                </div>
                <?php
            }
            ?>
        </main>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>PrintFlow TESA</h4>
                <p>Sistema de Gestión de Impresoras</p>
            </div>
            <div class="footer-section">
                <h4>Contacto</h4>
                <p>Instituto TESA</p>
                <p>Soporte Técnico</p>
            </div>
            <div class="footer-section">
                <h4>Sistema</h4>
                <p>Versión 1.0</p>
                <p>© <?php echo date('Y'); ?> TESA</p>
            </div>
        </div>
    </footer>

    <script>
        // Auto-ocultar alertas después de 4 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 4000);
            });

            // Marcar enlace activo en navegación
            const currentUrl = window.location.href;
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.href === currentUrl) {
                    link.classList.add('active');
                }
            });
        });

        // Función para confirmar acciones destructivas
        function confirmAction(message = '¿Estás seguro de realizar esta acción?') {
            return confirm(message);
        }

        // Función para mostrar notificaciones personalizadas
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `
                <span>${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'} ${message}</span>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(notification, container.firstChild);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>