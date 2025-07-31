<?php

/**
 * ========================================
 * HELPER DE AUTENTICAÇÃO
 * ========================================
 * 
 * Gerencia autenticação, sessões e permissões de usuários
 * USA APENAS DatabaseConfig para conexões
 */

class Auth
{
    /**
     * ========================================
     * VERIFICAR SE USUÁRIO ESTÁ LOGADO
     * ========================================
     * 
     * @return bool True se logado
     */
    public static function check()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['current_instance']);
    }

    /**
     * ========================================
     * OBTER DADOS DO USUÁRIO LOGADO
     * ========================================
     * 
     * @return array|null Dados do usuário ou null
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['user_first_name'] ?? '',
            'last_name' => $_SESSION['user_last_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'username' => $_SESSION['user_username'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user',
            'full_name' => ($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? '')
        ];
    }

    /**
     * ========================================
     * OBTER INSTÂNCIA ATUAL
     * ========================================
     * 
     * @return array|null Dados da instância ou null
     */
    public static function instance()
    {
        return $_SESSION['current_instance'] ?? null;
    }

    /**
     * ========================================
     * VERIFICAR SE É ADMIN
     * ========================================
     * 
     * @return bool True se admin
     */
    public static function isAdmin()
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }

    /**
     * ========================================
     * VERIFICAR SE É SUPER ADMIN
     * ========================================
     * 
     * @return bool True se super admin
     */
    public static function isSuperAdmin()
    {
        $user = self::user();
        return $user && $user['role'] === 'super_admin';
    }

    /**
     * ========================================
     * EXIGIR LOGIN
     * ========================================
     * 
     * Redireciona para login se não estiver logado
     */
    public static function requireLogin()
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * ========================================
     * REALIZAR LOGIN
     * ========================================
     * 
     * @param array $user Dados do usuário
     * @param array $instance Dados da instância
     */
    public static function login($user, $instance)
    {
        // ✅ DEFINIR DADOS NA SESSÃO
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['current_instance'] = $instance;

        // ✅ ATUALIZAR ÚLTIMO LOGIN NO BANCO (USA DatabaseConfig)
        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);
            $stmt = $db->prepare("UPDATE users SET last_login = NOW(), last_ip = ?, is_online = 1 WHERE id = ?");
            $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $user['id']]);

            error_log("✅ Login realizado com sucesso - Usuário: {$user['username']} - Instância: {$instance['subdomain']}");
        } catch (Exception $e) {
            error_log("❌ Erro ao atualizar último login: " . $e->getMessage());
        }
    }

    /**
     * ========================================
     * REALIZAR LOGOUT
     * ========================================
     */
    public static function logout()
    {
        if (self::check()) {
            // ✅ MARCAR COMO OFFLINE (USA DatabaseConfig)
            try {
                $instance = $_SESSION['current_instance'];
                $userId = $_SESSION['user_id'];

                $db = DatabaseConfig::getInstanceDB($instance['database_name']);
                $stmt = $db->prepare("UPDATE users SET is_online = 0 WHERE id = ?");
                $stmt->execute([$userId]);

                error_log("✅ Logout realizado - Usuário ID: {$userId}");
            } catch (Exception $e) {
                error_log("❌ Erro ao marcar usuário offline: " . $e->getMessage());
            }
        }

        // ✅ DESTRUIR SESSÃO
        session_destroy();
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /**
     * ========================================
     * OBTER INSTÂNCIA POR SUBDOMÍNIO
     * ========================================
     * 
     * @param string $subdomain Subdomínio da instância
     * @return array|null Dados da instância ou null
     */
    public static function getInstance($subdomain)
    {
        try {
            // ✅ USA DatabaseConfig PARA CONEXÃO
            $db = DatabaseConfig::getMasterDB();
            $stmt = $db->prepare("SELECT * FROM instances WHERE subdomain = ? AND status = 'active'");
            $stmt->execute([$subdomain]);
            $result = $stmt->fetch();

            if ($result) {
                error_log("✅ Instância encontrada: {$subdomain}");
                return $result;
            }

            // ✅ FALLBACK PARA INSTÂNCIA PADRÃO
            if ($subdomain === 'miriandayrell') {
                error_log("⚠️ Usando fallback para instância: {$subdomain}");
                return [
                    'id' => 1,
                    'name' => 'Mirian Dayrell Telecom',
                    'subdomain' => 'miriandayrell',
                    'database_name' => 'callboard',
                    'company_name' => 'Mirian Dayrell Telecom',
                    'status' => 'active'
                ];
            }

            error_log("❌ Instância não encontrada: {$subdomain}");
            return null;
        } catch (Exception $e) {
            error_log("❌ Erro ao obter instância: " . $e->getMessage());

            // ✅ FALLBACK EM CASO DE ERRO
            if ($subdomain === 'miriandayrell') {
                return [
                    'id' => 1,
                    'name' => 'Mirian Dayrell Telecom',
                    'subdomain' => 'miriandayrell',
                    'database_name' => 'callboard',
                    'company_name' => 'Mirian Dayrell Telecom',
                    'status' => 'active'
                ];
            }
            return null;
        }
    }

    /**
     * ========================================
     * VALIDAR USUÁRIO
     * ========================================
     * 
     * @param string $email Email ou username
     * @param string $password Senha
     * @param array $instance Dados da instância
     * @return array|null Dados do usuário ou null
     */
    public static function validateUser($email, $password, $instance)
    {
        try {
            // ✅ USA DatabaseConfig PARA CONEXÃO
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);

            // ✅ BUSCAR POR EMAIL OU USERNAME
            $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND status = 'active'");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                error_log("✅ Usuário validado com sucesso: {$email}");
                return $user;
            }

            error_log("❌ Falha na validação do usuário: {$email}");
            return null;
        } catch (Exception $e) {
            error_log("❌ Erro ao validar usuário: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ========================================
     * VERIFICAR SE ESTÁ LOGADO (ALIAS)
     * ========================================
     * 
     * @return bool True se logado
     */
    public static function isLoggedIn()
    {
        return self::check();
    }

    /**
     * ========================================
     * OBTER PERMISSÕES DO USUÁRIO
     * ========================================
     * 
     * @return array Lista de permissões
     */
    public static function getPermissions()
    {
        $user = self::user();
        if (!$user) {
            return [];
        }

        $permissions = ['view_dashboard'];

        switch ($user['role']) {
            case 'super_admin':
                $permissions = array_merge($permissions, [
                    'manage_instances',
                    'manage_users',
                    'manage_settings',
                    'view_reports',
                    'export_data',
                    'manage_email'
                ]);
                break;

            case 'admin':
                $permissions = array_merge($permissions, [
                    'manage_users',
                    'view_reports',
                    'export_data',
                    'manage_email'
                ]);
                break;

            case 'user':
            default:
                $permissions = array_merge($permissions, [
                    'view_reports'
                ]);
                break;
        }

        return $permissions;
    }

    /**
     * ========================================
     * VERIFICAR PERMISSÃO ESPECÍFICA
     * ========================================
     * 
     * @param string $permission Permissão a verificar
     * @return bool True se tem permissão
     */
    public static function hasPermission($permission)
    {
        $permissions = self::getPermissions();
        return in_array($permission, $permissions);
    }
}

// ✅ LOG DE CARREGAMENTO
error_log("📁 Auth Helper carregado com sucesso");
