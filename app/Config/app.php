<?php

/**
 * ========================================
 * CONFIGURAÇÃO CENTRALIZADA DA APLICAÇÃO
 * ========================================
 * 
 * Este arquivo centraliza TODAS as configurações da aplicação
 * Detecta automaticamente o ambiente e configura URLs e constantes
 */

/**
 * ========================================
 * DETECÇÃO AUTOMÁTICA DE AMBIENTE
 * ========================================
 */
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

/**
 * ========================================
 * CONFIGURAÇÃO DE URL BASE
 * ========================================
 */
if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
    // ✅ AMBIENTE LOCAL (XAMPP/WAMP)
    define('APP_URL', 'http://localhost/sistema-callboard');
    define('APP_ENV', 'local');
} elseif (strpos($httpHost, '10.0.1.55') !== false) {
    // ✅ AMBIENTE SERVIDOR DEBIAN (LAMPP)
    define('APP_URL', 'http://10.0.1.55/sistema-callboard');
    define('APP_ENV', 'server');
} elseif (strpos($httpHost, 'eagletelecom.com.br') !== false) {
    // ✅ AMBIENTE PRODUÇÃO (LOCAWEB/NUVEM)
    define('APP_URL', 'https://callboard.eagletelecom.com.br');
    define('APP_ENV', 'production');
} else {
    // ✅ AMBIENTE GENÉRICO
    $protocol = $isHttps ? 'https' : 'http';
    define('APP_URL', $protocol . '://' . $httpHost);
    define('APP_ENV', 'generic');
}

/**
 * ========================================
 * CONFIGURAÇÕES GERAIS DA APLICAÇÃO
 * ========================================
 */
define('APP_NAME', 'Sistema CDR Multi-Instância');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistema de gerenciamento de CDRs multi-instância');
define('APP_AUTHOR', 'Lucas André Fernando');

/**
 * ========================================
 * CONFIGURAÇÕES DE MONGODB
 * ========================================
 */
define('MONGODB_URI', 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0');
define('MONGODB_DATABASE', 'cdrs');
define('MONGODB_COLLECTION', 'mdi');

/**
 * ========================================
 * CONFIGURAÇÕES DE SESSÃO
 * ========================================
 */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_ENV === 'production' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hora

/**
 * ========================================
 * CONFIGURAÇÕES DE ERRO
 * ========================================
 */
if (APP_ENV === 'production') {
    // ✅ PRODUÇÃO - OCULTAR ERROS DO USUÁRIO
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../storage/logs/error.log');
} else {
    // ✅ DESENVOLVIMENTO - MOSTRAR ERROS
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

/**
 * ========================================
 * CONFIGURAÇÕES DE TIMEZONE
 * ========================================
 */
date_default_timezone_set('America/Sao_Paulo');

/**
 * ========================================
 * CONFIGURAÇÕES DE UPLOAD
 * ========================================
 */
define('MAX_UPLOAD_SIZE', '10M');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

/**
 * ========================================
 * CONFIGURAÇÕES DE EMAIL
 * ========================================
 */
define('DEFAULT_FROM_EMAIL', 'noreply@eagletelecom.com.br');
define('DEFAULT_FROM_NAME', 'Sistema CDR');

/**
 * ========================================
 * INICIALIZAÇÃO DE SESSÃO
 * ========================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ========================================
 * AUTOLOAD DO COMPOSER
 * ========================================
 */
$composerPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php'
];

foreach ($composerPaths as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        break;
    }
}

/**
 * ========================================
 * FUNÇÕES DE CONVENIÊNCIA
 * ========================================
 */

/**
 * Obter URL completa da aplicação
 * 
 * @param string $path Caminho adicional
 * @return string URL completa
 */
function app_url($path = '')
{
    return APP_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Verificar se está em ambiente de produção
 * 
 * @return bool True se produção
 */
function is_production()
{
    return APP_ENV === 'production';
}

/**
 * Verificar se está em ambiente local
 * 
 * @return bool True se local
 */
function is_local()
{
    return APP_ENV === 'local';
}

/**
 * Obter informações do ambiente
 * 
 * @return array Informações do ambiente
 */
function get_environment_info()
{
    return [
        'app_env' => APP_ENV,
        'app_url' => APP_URL,
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'não definido',
        'is_https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'não definido'
    ];
}

// ✅ LOG DE CARREGAMENTO
error_log("📁 Configuração da aplicação carregada - Ambiente: " . APP_ENV . " - URL: " . APP_URL);
