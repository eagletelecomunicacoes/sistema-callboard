<?php
class Auth
{
    public static function check()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['current_instance']);
    }

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

    public static function instance()
    {
        return $_SESSION['current_instance'] ?? null;
    }

    public static function isAdmin()
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }

    public static function isSuperAdmin()
    {
        $user = self::user();
        return $user && $user['role'] === 'super_admin';
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function login($user, $instance)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['current_instance'] = $instance;

        // Atualizar último login no banco
        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);
            $stmt = $db->prepare("UPDATE users SET last_login = NOW(), last_ip = ?, is_online = 1 WHERE id = ?");
            $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $user['id']]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar último login: " . $e->getMessage());
        }
    }

    public static function logout()
    {
        if (self::check()) {
            // Marcar como offline
            try {
                $instance = $_SESSION['current_instance'];
                $userId = $_SESSION['user_id'];

                $db = DatabaseConfig::getInstanceDB($instance['database_name']);
                $stmt = $db->prepare("UPDATE users SET is_online = 0 WHERE id = ?");
                $stmt->execute([$userId]);
            } catch (Exception $e) {
                error_log("Erro ao marcar usuário offline: " . $e->getMessage());
            }
        }

        session_destroy();
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    public static function getInstance($subdomain)
    {
        try {
            $db = DatabaseConfig::getMasterDB();
            $stmt = $db->prepare("SELECT * FROM instances WHERE subdomain = ? AND status = 'active'");
            $stmt->execute([$subdomain]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao obter instância: " . $e->getMessage());

            // Fallback para instância padrão
            if ($subdomain === 'miriandayrell') {
                return [
                    'id' => 1,
                    'name' => 'Mirian Dayrell Telecom',
                    'subdomain' => 'miriandayrell',
                    'database_name' => 'cdr_miriandayrell',
                    'company_name' => 'Mirian Dayrell Telecom',
                    'status' => 'active'
                ];
            }
            return null;
        }
    }

    public static function validateUser($email, $password, $instance)
    {
        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);

            // Buscar por email OU username
            $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND status = 'active'");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }

            return null;
        } catch (Exception $e) {
            error_log("Erro ao validar usuário: " . $e->getMessage());
            return null;
        }
    }

    public static function isLoggedIn()
    {
        return self::check();
    }
}
