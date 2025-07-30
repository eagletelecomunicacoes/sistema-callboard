<?php
// Carregar configurações
require_once __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Config/database.php';

// Carregar helpers manualmente
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Helpers/Toastr.php';

// Roteamento
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remover diretório base - ✅ CORRIGIDO
$basePath = '/sistema-callboard'; // ✅ MUDANÇA AQUI
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Remover /public se existir
if (strpos($path, '/public') === 0) {
    $path = substr($path, 7);
}

// Remover query string
$path = strtok($path, '?');

// Se path vazio, definir como /
if (empty($path) || $path === '/') {
    $path = '/';
}

// Definir rotas
$routes = [
    '/' => function () {
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
        } else {
            header('Location: ' . APP_URL . '/login');
        }
        exit;
    },
    '/login' => function () {
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
    },
    '/logout' => function () {
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
    },
    '/dashboard' => function () {
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
    },
    '/users' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->index();
    },
    '/users/create' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->create();
    },
    '/users/edit' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->edit();
    },
    '/users/delete' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->delete();
    },
    '/users/change-status' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->changeStatus();
    },
    '/users/export' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->export();
    },
    '/profile' => function () {
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->profile();
    },

    // ===== ROTAS DE RELATÓRIOS (NOVAS) =====
    '/reports' => function () {
        require_once __DIR__ . '/../app/Controllers/ReportsController.php';
        $controller = new ReportsController();
        $controller->index();
    },
    '/reports/generate' => function () {
        require_once __DIR__ . '/../app/Controllers/ReportsController.php';
        $controller = new ReportsController();
        $controller->generateReport();
    },
    '/reports/export' => function () {
        require_once __DIR__ . '/../app/Controllers/ReportsController.php';
        $controller = new ReportsController();
        $controller->exportReport();
    },
    '/reports/call-details' => function () {
        require_once __DIR__ . '/../app/Controllers/ReportsController.php';
        $controller = new ReportsController();
        $controller->getCallDetails();
    },

    // ===== ROTAS DE EMAIL =====
    '/email-config' => function () {
        require_once __DIR__ . '/../app/Controllers/EmailController.php';
        $controller = new EmailController();
        $controller->index();
    },
    '/email/config' => function () {
        require_once __DIR__ . '/../app/Controllers/EmailController.php';
        $controller = new EmailController();
        $controller->index();
    },
    '/email/admin-config' => function () {
        require_once __DIR__ . '/../app/Controllers/EmailController.php';
        $controller = new EmailController();
        $controller->adminConfig();
    },
    '/email/preview' => function () {
        require_once __DIR__ . '/../app/Controllers/EmailController.php';
        $controller = new EmailController();
        $controller->preview();
    }
];

// Verificar se rota existe
if (!isset($routes[$path])) {
    // Log da rota não encontrada para debug
    error_log("Rota não encontrada: " . $path);

    if (Auth::check()) {
        header('Location: ' . APP_URL . '/dashboard');
    } else {
        header('Location: ' . APP_URL . '/login');
    }
    exit;
}

// Executar rota
try {
    $routes[$path]();
} catch (Exception $e) {
    error_log("Erro na rota {$path}: " . $e->getMessage());

    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa;'>";
    echo "<h1 style='color: #dc3545;'>🚨 Erro do Sistema</h1>";
    echo "<div style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;'>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Rota:</strong> " . htmlspecialchars($path) . "</p>";
    echo "</div>";
    echo "<p style='margin-top: 20px;'><a href='" . APP_URL . "/dashboard' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Voltar ao Dashboard</a></p>";
    echo "</div>";
}
