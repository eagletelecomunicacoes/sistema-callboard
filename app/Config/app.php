<?php
// Detectar ambiente automaticamente
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Configurar URL base baseado no ambiente
if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
    // AMBIENTE LOCAL
    define('APP_URL', 'http://localhost/sistema-callboard');
} elseif (strpos($httpHost, '10.0.1.55') !== false) {
    // AMBIENTE SERVIDOR DEBIAN
    define('APP_URL', 'http://10.0.1.55/sistema-callboard');
} else {
    // AMBIENTE NUVEM/PRODUÇÃO
    define('APP_URL', 'https://' . $httpHost);
}

// Configurações gerais
define('APP_NAME', 'Sistema CDR Multi-Instância');
define('APP_VERSION', '1.0.0');

// Configurações MongoDB Atlas
define('MONGODB_URI', 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0');
define('MONGODB_DATABASE', 'cdrs');
define('MONGODB_COLLECTION', 'mdi');

// Configurações de Sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// Configurações de Erro
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload do Composer (se existir)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}
