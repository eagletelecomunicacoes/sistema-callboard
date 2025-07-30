<?php
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}

class DatabaseConfig
{
    /**
     * Configuração centralizada de banco de dados
     * Detecta automaticamente o ambiente (local, servidor, nuvem)
     */
    private static function getConfig()
    {
        // Detectar ambiente baseado no servidor
        $serverIP = $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Configurações por ambiente
        if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
            // AMBIENTE LOCAL (XAMPP/WAMP)
            return [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'master_db' => 'sistema_cdr_master'
            ];
        } elseif (strpos($httpHost, '10.0.1.55') !== false || $serverIP === '10.0.1.55') {
            // AMBIENTE SERVIDOR DEBIAN (LAMPP)
            return [
                'host' => 'localhost', // ✅ MUDANÇA AQUI - usar localhost mesmo no servidor
                'username' => 'root',
                'password' => '',
                'master_db' => 'sistema_cdr_master'
            ];
        } else {
            // AMBIENTE NUVEM/PRODUÇÃO
            return [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'master_db' => $_ENV['DB_MASTER'] ?? 'sistema_cdr_master'
            ];
        }
    }

    /**
     * Conexão com banco master (gerencia instâncias)
     */
    public static function getMasterDB()
    {
        try {
            $config = self::getConfig();
            $dsn = "mysql:host={$config['host']};dbname={$config['master_db']};charset=utf8mb4";

            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            return $pdo;
        } catch (PDOException $e) {
            error_log("Erro Master DB: " . $e->getMessage());
            throw new Exception("Erro de conexão Master DB: " . $e->getMessage());
        }
    }

    /**
     * Conexão com banco de instância específica
     */
    public static function getInstanceDB($dbName)
    {
        try {
            $config = self::getConfig();
            $dsn = "mysql:host={$config['host']};dbname={$dbName};charset=utf8mb4";

            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            return $pdo;
        } catch (PDOException $e) {
            error_log("Erro Instance DB ({$dbName}): " . $e->getMessage());
            throw new Exception("Erro de conexão Instance DB: " . $e->getMessage());
        }
    }

    /**
     * Obter conexão MySQL para instância atual
     */
    public static function getMySQL()
    {
        $currentInstance = $_SESSION['current_instance'] ?? null;

        if (!$currentInstance) {
            throw new Exception("Nenhuma instância selecionada");
        }

        return self::getInstanceDB($currentInstance['database_name']);
    }

    /**
     * MongoDB - usando coleção específica da instância
     */
    public static function getMongoDB()
    {
        try {
            if (!class_exists('MongoDB\Client')) {
                throw new Exception("MongoDB PHP Library não está instalada");
            }

            $uri = 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0';

            $client = new MongoDB\Client($uri, [], [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ]);

            // Testar conexão
            $client->selectDatabase('admin')->command(['ping' => 1]);

            return $client->selectDatabase('CrctTec0');
        } catch (Exception $e) {
            error_log("Erro MongoDB: " . $e->getMessage());
            throw new Exception("Erro de conexão MongoDB: " . $e->getMessage());
        }
    }

    /**
     * Verificar se instância existe
     */
    public static function instanceExists($subdomain)
    {
        try {
            $pdo = self::getMasterDB();
            $stmt = $pdo->prepare("SELECT * FROM instances WHERE subdomain = ? AND status = 'active'");
            $stmt->execute([$subdomain]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Debug de configuração (remover em produção)
     */
    public static function debugConfig()
    {
        $config = self::getConfig();
        echo "<pre>";
        echo "=== DEBUG DATABASE CONFIG ===\n";
        echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "\n";
        echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'não definido') . "\n";
        echo "Configuração detectada:\n";
        print_r($config);
        echo "==============================\n";
        echo "</pre>";
    }
}
