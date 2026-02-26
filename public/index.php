<?php
// public/index.php
require_once __DIR__ . '/../config/db.php';

spl_autoload_register(function($class) {
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

session_start();


// ─── ROUTER DE URLs LIMPIAS ───────────────────────────────────────────────────
$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Quitar el prefijo del subdirectorio si corre en /PrintFlow/public
// En producción esto queda vacío. En XAMPP ajusta si es necesario.
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$rutaActual = '/' . trim(str_replace($basePath, '', $requestUri), '/');
if ($rutaActual === '' || $rutaActual === '//') $rutaActual = '/';


// ─── PROTECCIÓN: redirigir a /login si no hay sesión ─────────────────────────
$rutasPublicas = ['/login'];
if (!in_array($rutaActual, $rutasPublicas) && empty($_SESSION['usuario_id'])) {
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/PrintFlow/public/login');
    exit;
}

// ─── RUTAS DE AUTENTICACIÓN ───────────────────────────────────────────────────
if ($rutaActual === '/login') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    $controller = new AuthController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $controller->login();
    } else {
        $data = $controller->showLogin();
    }

    extract($data['vars']);
    require $data['view'];
    exit;
}

if ($rutaActual === '/logout') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

// ─── ROUTER PRINCIPAL (compatible con ?controller= Y URLs limpias) ────────────
// Mapeo de rutas limpias a controller/action
$routes = [
    '/'                  => ['controller' => 'home',            'action' => 'index'],
    '/lecturas'          => ['controller' => 'lectura',         'action' => 'index'],
    '/impresoras'        => ['controller' => 'printer',         'action' => 'index'],
    '/mantenimiento'     => ['controller' => 'mantenimiento',   'action' => 'index'],
    '/cambio-toner'      => ['controller' => 'cambioToner',     'action' => 'index'],
    '/reportes'          => ['controller' => 'reports',         'action' => 'index'],
    '/repuestos'         => ['controller' => 'repuesto',        'action' => 'index'],
    '/scraper'           => ['controller' => 'scraper',         'action' => 'index'],
    '/toner-inventario'  => ['controller' => 'tonerInventario', 'action' => 'index'],
    '/umbrales'          => ['controller' => 'umbral',          'action' => 'index'],
];

if (isset($routes[$rutaActual])) {
    // URL limpia encontrada
    $controllerParam = $routes[$rutaActual]['controller'];
    $action          = $routes[$rutaActual]['action'];
} else {
    // Fallback al sistema original con ?controller=&action=
    $controllerParam = $_GET['controller'] ?? 'home';
    $action          = $_GET['action']     ?? 'index';
}

$controllerName = ucfirst($controllerParam) . 'Controller';
$controllerFile = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "Error 404: Controlador '$controllerName' no encontrado.";
    exit;
}

require_once $controllerFile;
$controllerInstance = new $controllerName();

if (!method_exists($controllerInstance, $action)) {
    http_response_code(404);
    echo "Error 404: Acción '$action' no encontrada.";
    exit;
}

$data = $controllerInstance->$action();

// ─── VALIDACIONES DE RESPUESTA ────────────────────────────────────────────────
if ($data === null) {
    http_response_code(500);
    echo "Error 500: El controlador '$controllerName::$action' devolvió null.";
    exit;
}

if (!is_array($data)) {
    http_response_code(500);
    echo "Error 500: '$controllerName::$action' debe devolver un array.";
    exit;
}

if (!isset($data['view'])) {
    http_response_code(500);
    echo "Error 500: '$controllerName::$action' debe incluir clave 'view'.";
    exit;
}

$viewFile = $data['view'];

if (isset($data['vars']) && is_array($data['vars'])) {
    foreach ($data['vars'] as $key => $value) {
        $$key = $value;
    }
} elseif (isset($data['vars']) && !is_array($data['vars'])) {
    http_response_code(500);
    echo "Error 500: 'vars' debe ser un array en '$controllerName::$action'.";
    exit;
}
$_GET['controller'] = $controllerParam ?? 'home';
$_GET['action']     = $action ?? 'index';


include __DIR__ . '/../app/Views/layout.php';