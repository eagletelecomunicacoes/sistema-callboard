<?php
require_once '../app/Config/database.php';

class Instance
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConfig::getMasterDB();
    }

    /**
     * Criar nova instância
     */
    public function createInstance($data)
    {
        try {
            // Validar dados
            $this->validateInstanceData($data);

            // Gerar nome único para o banco
            $dbName = 'cdr_' . $this->generateSafeName($data['subdomain']);

            // Inserir na tabela de instâncias
            $stmt = $this->pdo->prepare("
                INSERT INTO instances (
                    name, subdomain, database_name, company_name, 
                    admin_email, mongodb_collection, smtp_host, 
                    smtp_port, smtp_username, smtp_password, smtp_encryption
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $data['name'],
                $data['subdomain'],
                $dbName,
                $data['company_name'],
                $data['admin_email'],
                $data['mongodb_collection'] ?? 'cdrs',
                $data['smtp_host'] ?? null,
                $data['smtp_port'] ?? 587,
                $data['smtp_username'] ?? null,
                $data['smtp_password'] ?? null,
                $data['smtp_encryption'] ?? 'tls'
            ]);

            if ($result) {
                $instanceId = $this->pdo->lastInsertId();

                // Criar banco de dados da instância
                $this->createInstanceDatabase($dbName);

                // Criar usuário administrador
                $this->createAdminUser($dbName, $data);

                return $instanceId;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar instância: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar banco de dados da instância
     */
    private function createInstanceDatabase($dbName)
    {
        try {
            // Ler template SQL
            $template = file_get_contents('../database/instance_template.sql');
            $sql = str_replace('{INSTANCE_NAME}', str_replace('cdr_', '', $dbName), $template);

            // Executar SQL
            $this->pdo->exec($sql);

            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar banco da instância: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar usuário administrador da instância
     */
    private function createAdminUser($dbName, $data)
    {
        try {
            // Conectar ao banco da instância
            $instancePdo = DatabaseConfig::getInstanceDB($dbName);

            $stmt = $instancePdo->prepare("
                INSERT INTO users (
                    first_name, last_name, email, username, 
                    password, role, status
                ) VALUES (?, ?, ?, ?, ?, 'admin', 'active')
            ");

            return $stmt->execute([
                $data['admin_first_name'],
                $data['admin_last_name'],
                $data['admin_email'],
                $data['admin_username'],
                password_hash($data['admin_password'], PASSWORD_DEFAULT)
            ]);
        } catch (Exception $e) {
            error_log("Erro ao criar admin da instância: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter instância por subdomínio
     */
    public function getBySubdomain($subdomain)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM instances 
                WHERE subdomain = ? AND status = 'active'
            ");
            $stmt->execute([$subdomain]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar instância: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas as instâncias
     */
    public function getAll($limit = 50, $offset = 0)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM instances 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar instâncias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Validar dados da instância
     */
    private function validateInstanceData($data)
    {
        $required = [
            'name',
            'subdomain',
            'company_name',
            'admin_email',
            'admin_first_name',
            'admin_last_name',
            'admin_username',
            'admin_password'
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Campo obrigatório: {$field}");
            }
        }

        // Validar email
        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido");
        }

        // Validar subdomínio único
        if ($this->subdomainExists($data['subdomain'])) {
            throw new Exception("Subdomínio já existe");
        }

        return true;
    }

    /**
     * Verificar se subdomínio existe
     */
    private function subdomainExists($subdomain)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM instances WHERE subdomain = ?");
        $stmt->execute([$subdomain]);
        return $stmt->fetch() !== false;
    }

    /**
     * Gerar nome seguro para banco
     */
    private function generateSafeName($name)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
    }
}
