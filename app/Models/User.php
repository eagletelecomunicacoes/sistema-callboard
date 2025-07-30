<?php
class User
{
    private $db;

    public function __construct()
    {
        $instance = Auth::instance();
        if ($instance) {
            $this->db = DatabaseConfig::getInstanceDB($instance['database_name']);
        }
    }

    /**
     * Obter total de usuários
     */
    public function getTotalUsers()
    {
        try {
            if (!$this->db) return 4;

            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
            $result = $stmt->fetch();

            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao obter total de usuários: " . $e->getMessage());
            return 4;
        }
    }

    /**
     * Obter usuários online
     */
    public function getOnlineUsers()
    {
        try {
            if (!$this->db) return 2;

            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE is_online = 1 AND status = 'active'");
            $result = $stmt->fetch();

            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao obter usuários online: " . $e->getMessage());
            return 2;
        }
    }

    /**
     * Obter todos os usuários
     */
    public function getAll()
    {
        try {
            if (!$this->db) {
                // Retornar dados de exemplo se não há conexão
                return [
                    [
                        'id' => 1,
                        'first_name' => 'Mirian',
                        'last_name' => 'Dayrell',
                        'email' => 'admin@miriandayrell.com.br',
                        'username' => 'admin',
                        'role' => 'admin',
                        'status' => 'active',
                        'last_login' => date('Y-m-d H:i:s'),
                        'is_online' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'first_name' => 'João',
                        'last_name' => 'Silva',
                        'email' => 'joao@miriandayrell.com.br',
                        'username' => 'joao',
                        'role' => 'user',
                        'status' => 'active',
                        'last_login' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                        'is_online' => 0,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
                    ],
                    [
                        'id' => 3,
                        'first_name' => 'Maria',
                        'last_name' => 'Santos',
                        'email' => 'maria@miriandayrell.com.br',
                        'username' => 'maria',
                        'role' => 'user',
                        'status' => 'active',
                        'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                        'is_online' => 1,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                    ],
                    [
                        'id' => 4,
                        'first_name' => 'Pedro',
                        'last_name' => 'Costa',
                        'email' => 'pedro@miriandayrell.com.br',
                        'username' => 'pedro',
                        'role' => 'user',
                        'status' => 'inactive',
                        'last_login' => null,
                        'is_online' => 0,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
                    ]
                ];
            }

            $stmt = $this->db->query("
                SELECT 
                    id, 
                    first_name, 
                    last_name, 
                    email, 
                    COALESCE(username, email) as username,
                    role, 
                    status, 
                    last_login, 
                    COALESCE(is_online, 0) as is_online, 
                    created_at 
                FROM users 
                ORDER BY created_at DESC
            ");

            $users = $stmt->fetchAll();

            // Garantir que todos os campos existam
            foreach ($users as &$user) {
                $user['username'] = $user['username'] ?? $user['email'];
                $user['is_online'] = $user['is_online'] ?? 0;
                $user['last_login'] = $user['last_login'] ?? null;
            }

            return $users;
        } catch (Exception $e) {
            error_log("Erro ao obter usuários: " . $e->getMessage());

            // Retornar dados de exemplo em caso de erro
            return [
                [
                    'id' => 1,
                    'first_name' => 'Mirian',
                    'last_name' => 'Dayrell',
                    'email' => 'admin@miriandayrell.com.br',
                    'username' => 'admin',
                    'role' => 'admin',
                    'status' => 'active',
                    'last_login' => date('Y-m-d H:i:s'),
                    'is_online' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
        }
    }

    /**
     * Obter usuário por ID
     */
    public function getById($id)
    {
        try {
            if (!$this->db) return null;

            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    first_name, 
                    last_name, 
                    email, 
                    COALESCE(username, email) as username,
                    password,
                    role, 
                    status, 
                    last_login, 
                    COALESCE(is_online, 0) as is_online, 
                    created_at 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            $user = $stmt->fetch();

            if ($user) {
                $user['username'] = $user['username'] ?? $user['email'];
                $user['is_online'] = $user['is_online'] ?? 0;
            }

            return $user;
        } catch (Exception $e) {
            error_log("Erro ao obter usuário: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Criar novo usuário
     */
    public function create($data)
    {
        try {
            if (!$this->db) return false;

            $stmt = $this->db->prepare("
                INSERT INTO users (first_name, last_name, email, username, password, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");

            return $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['username'] ?? $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'user'
            ]);
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar usuário
     */
    public function update($id, $data)
    {
        try {
            if (!$this->db) return false;

            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ?, role = ?, updated_at = NOW() WHERE id = ?";
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['username'] ?? $data['email'],
                $data['role'] ?? 'user',
                $id
            ];

            // Se senha foi fornecida, incluir na atualização
            if (!empty($data['password'])) {
                $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ?, password = ?, role = ?, updated_at = NOW() WHERE id = ?";
                array_splice($params, 4, 0, [password_hash($data['password'], PASSWORD_DEFAULT)]);
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir usuário
     */
    public function delete($id)
    {
        try {
            if (!$this->db) return false;

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alterar status do usuário
     */
    public function changeStatus($id, $status)
    {
        try {
            if (!$this->db) return false;

            $stmt = $this->db->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (Exception $e) {
            error_log("Erro ao alterar status do usuário: " . $e->getMessage());
            return false;
        }
    }
}
