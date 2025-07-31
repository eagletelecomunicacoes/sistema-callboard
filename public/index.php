<?php

/**
 * ========================================
 * ARQUIVO PRINCIPAL DE ROTEAMENTO
 * ========================================
 * 
 * Este arquivo é o ponto de entrada da aplicação
 * USA APENAS as configurações centralizadas
 */

// ✅ CARREGAR CONFIGURAÇÕES CENTRALIZADAS
require_once __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Config/database.php';

// ✅ CARREGAR HELPERS
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Helpers/Toastr.php';

/**
 * ========================================
 * PROCESSAMENTO DE ROTA
 * ========================================
 */
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// ✅ DETECÇÃO AUTOMÁTICA DE AMBIENTE PARA ROTEAMENTO
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
    // ✅ AMBIENTE LOCAL - remover diretório base
    $basePath = '/sistema-callboard';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
} elseif (strpos($httpHost, '10.0.1.55') !== false) {
    // ✅ AMBIENTE SERVIDOR DEBIAN - remover diretório base
    $basePath = '/sistema-callboard';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
} else {
    // ✅ AMBIENTE PRODUÇÃO (LOCAWEB) - sem diretório base
    // Não remover nenhum diretório
}

// ✅ REMOVER /public SE EXISTIR
if (strpos($path, '/public') === 0) {
    $path = substr($path, 7);
}

// ✅ REMOVER QUERY STRING
$path = strtok($path, '?');

// ✅ SE PATH VAZIO, DEFINIR COMO /
if (empty($path) || $path === '/') {
    $path = '/';
}

// ✅ LOG PARA DEBUG (remover após funcionar)
error_log("�� DEBUG ROTEAMENTO - HTTP_HOST: {$httpHost} | PATH: {$path} | REQUEST_URI: {$request}");

/**
 * ========================================
 * DEFINIÇÃO DE ROTAS
 * ========================================
 */
$routes = [
    // ===== ROTAS PRINCIPAIS =====
    '/' => function () {
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
        } else {
            header('Location: ' . APP_URL . '/login');
        }
        exit;
    },

    // ===== AUTENTICAÇÃO =====
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
    '/auth/status' => function () {
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->checkStatus();
    },
    '/auth/renew' => function () {
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->renewSession();
    },

    // ===== DASHBOARD =====
    '/dashboard' => function () {
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
    },

    // ===== USUÁRIOS =====
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

    // ===== RELATÓRIOS =====
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

    // ===== EMAIL =====
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
    },

    // ===== DEBUG (remover em produção) =====
    '/debug' => function () {
        echo "<h1>🔍 Debug do Sistema</h1>";
        echo "<h2>📊 Informações do Ambiente:</h2>";
        $envInfo = get_environment_info();
        echo "<pre>" . print_r($envInfo, true) . "</pre>";

        echo "<h2>🗄️ Configuração do Banco:</h2>";
        DatabaseConfig::debugConfig();

        echo "<h2>🧪 Teste de Conexão:</h2>";
        if (DatabaseConfig::testConnection()) {
            echo "<p style='color: green; font-weight: bold;'>✅ Conexão com banco OK!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Falha na conexão com banco!";
        }

        echo "<h2>📋 Informações da Sessão:</h2>";
        echo "<pre>";
        echo "Logado: " . (Auth::check() ? 'Sim' : 'Não') . "\n";
        if (Auth::check()) {
            echo "Usuário: " . print_r(Auth::user(), true);
            echo "Instância: " . print_r(Auth::instance(), true);
        }
        echo "</pre>";

        echo "<h2>🛣️ Informações de Roteamento:</h2>";
        echo "<pre>";
        echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "\n";
        echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "\n";
        echo "PATH processado: " . $GLOBALS['current_path'] . "\n";
        echo "APP_URL: " . APP_URL . "\n";
        echo "APP_ENV: " . APP_ENV . "\n";
        echo "</pre>";
    }
];

// ✅ SALVAR PATH ATUAL PARA DEBUG
$GLOBALS['current_path'] = $path;

/**
 * ========================================
 * EXECUÇÃO DA ROTA
 * ========================================
 */
if (!isset($routes[$path])) {
    // ✅ LOG DA ROTA NÃO ENCONTRADA
    error_log("❌ Rota não encontrada: {$path}");
    error_log("📋 Rotas disponíveis: " . implode(', ', array_keys($routes)));

    // ✅ REDIRECIONAR BASEADO NO STATUS DE LOGIN
    if (Auth::check()) {
        header('Location: ' . APP_URL . '/dashboard');
    } else {
        header('Location: ' . APP_URL . '/login');
    }
    exit;
}

// ✅ EXECUTAR ROTA
try {
    $routes[$path]();
} catch (Exception $e) {
    error_log("❌ Erro na rota {$path}: " . $e->getMessage());

    // ✅ PÁGINA DE ERRO AMIGÁVEL
    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Erro - " . APP_NAME . "</title>";
    echo "<style>";
    echo "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }";
    echo ".container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }";
    echo ".header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 30px; text-align: center; }";
    echo ".content { padding: 30px; }";
    echo ".error-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0; }";
    echo ".btn { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; }";
    echo ".btn:hover { background: #0056b3; }";
    echo "pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 14px; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<div class='header'>";
    echo "<h1>🚨 Erro do Sistema</h1>";
    echo "<p>Ocorreu um erro inesperado na aplicação</p>";
    echo "</div>";
    echo "<div class='content'>";
    echo "<div class='error-box'>";
    echo "<h3>📋 Detalhes do Erro:</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Rota:</strong> " . htmlspecialchars($path) . "</p>";
    echo "</div>";

    if (!is_production()) {
        echo "<h3>🔍 Informações de Debug:</h3>";
        echo "<pre>";
        echo "HTTP_HOST: " . htmlspecialchars($httpHost) . "\n";
        echo "REQUEST_URI: " . htmlspecialchars($request) . "\n";
        echo "APP_ENV: " . APP_ENV . "\n";
        echo "APP_URL: " . APP_URL . "\n";
        echo "</pre>";

        echo "<h3>📚 Stack Trace:</h3>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

    echo "<a href='" . APP_URL . "/dashboard' class='btn'>← Voltar ao Dashboard</a>";
    echo "<a href='" . APP_URL . "/login' class='btn' style='background: #28a745; margin-left: 10px;'>🏠 Ir para Login</a>";
    echo "</div>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
}

// ✅ LOG DE EXECUÇÃO BEM-SUCEDIDA
error_log("✅ Rota executada com sucesso: {$path}");
