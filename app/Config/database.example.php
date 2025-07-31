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
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // ✅ NOVA LÓGICA DE DETECÇÃO DE AMBIENTE
        if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
            // AMBIENTE LOCAL (XAMPP/WAMP)
            return [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'master_db' => 'sistema_cdr_master'
            ];
        } elseif (strpos($httpHost, 'eagletelecom.com.br') !== false || strpos($httpHost, 'callboard.eagletelecom.com.br') !== false) {
            // ✅ AMBIENTE PRODUÇÃO (LOCAWEB)
            return [
                'host' => 'callboard.mysql.dbaas.com.br',
                'username' => 'callboard',
                'password' => 'BdAdmin#2024!S',
                'master_db' => 'callboard' // ✅ BANCO MASTER CORRETO
            ];
        } elseif (strpos($httpHost, '10.0.1.55') !== false) {
            // AMBIENTE SERVIDOR DEBIAN (LAMPP)
            return [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'master_db' => 'sistema_cdr_master'
            ];
        } else {
            // AMBIENTE GENÉRICO/NUVEM
            return [
                'host' => $_ENV['DB_HOST'] ?? 'callboard.mysql.dbaas.com.br',
                'username' => $_ENV['DB_USERNAME'] ?? 'callboard',
                'password' => $_ENV['DB_PASSWORD'] ?? 'BdAdmin#2024!S',
                'master_db' => $_ENV['DB_MASTER'] ?? 'callboard'
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
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30 // ✅ TIMEOUT PARA CONEXÕES REMOTAS
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
            
            // ✅ VERIFICAR SE O BANCO EXISTE NO MESMO SERVIDOR
            // Para ambiente de produção, usar o mesmo banco principal
            if (strpos($_SERVER['HTTP_HOST'] ?? '', 'eagletelecom.com.br') !== false) {
                $dbName = 'callboard'; // ✅ FORÇAR USO DO BANCO PRINCIPAL
            }
            
            $dsn = "mysql:host={$config['host']};dbname={$dbName};charset=utf8mb4";

            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30
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
            error_log("Erro ao verificar instância: " . $e->getMessage());
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