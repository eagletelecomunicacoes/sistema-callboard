<?php

/**
 * ========================================
 * CONTROLLER DE AUTENTICAÇÃO
 * ========================================
 * 
 * Gerencia login, logout e autenticação de usuários
 * USA APENAS DatabaseConfig para conexões
 */

class AuthController
{
    /**
     * ========================================
     * PÁGINA DE LOGIN
     * ========================================
     */
    public function login()
    {
        // ✅ SE JÁ ESTÁ LOGADO, REDIRECIONAR
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $error = '';
        $success = $_GET['message'] ?? '';
        $selectedInstance = $_GET['instance'] ?? '';

        // ✅ BUSCAR TODAS AS INSTÂNCIAS ATIVAS (USA DatabaseConfig)
        try {
            $db = DatabaseConfig::getMasterDB();
            $stmt = $db->query("SELECT id, name, subdomain, company_name FROM instances WHERE status = 'active' ORDER BY company_name");
            $instances = $stmt->fetchAll();

            error_log("✅ Instâncias carregadas: " . count($instances));
        } catch (Exception $e) {
            error_log("❌ Erro ao carregar instâncias: " . $e->getMessage());

            // ✅ FALLBACK PARA INSTÂNCIA PADRÃO
            $instances = [
                [
                    'id' => 1,
                    'name' => 'Mirian Dayrell Telecom',
                    'subdomain' => 'miriandayrell',
                    'company_name' => 'Mirian Dayrell Telecom'
                ]
            ];
        }

        // ✅ PROCESSAR FORMULÁRIO DE LOGIN
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $instanceSubdomain = trim($_POST['instance'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($instanceSubdomain) || empty($email) || empty($password)) {
                $error = 'Todos os campos são obrigatórios';
            } else {
                // ✅ BUSCAR INSTÂNCIA
                $instance = Auth::getInstance($instanceSubdomain);

                if (!$instance) {
                    $error = 'Empresa não encontrada ou inativa';
                } else {
                    // ✅ VALIDAR USUÁRIO
                    $user = Auth::validateUser($email, $password, $instance);

                    if ($user) {
                        // ✅ LOGIN BEM-SUCEDIDO
                        Auth::login($user, $instance);
                        header('Location: ' . APP_URL . '/dashboard');
                        exit;
                    } else {
                        $error = 'Email/usuário ou senha incorretos';
                    }
                }
            }
        }

        // ✅ CARREGAR VIEW DE LOGIN
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * ========================================
     * LOGOUT
     * ========================================
     */
    public function logout()
    {
        $message = 'Você foi desconectado com sucesso!';
        Auth::logout();
        header('Location: ' . APP_URL . '/login?message=' . urlencode($message));
        exit;
    }

    /**
     * ========================================
     * VERIFICAR STATUS DE LOGIN (AJAX)
     * ========================================
     */
    public function checkStatus()
    {
        header('Content-Type: application/json');

        echo json_encode([
            'logged_in' => Auth::check(),
            'user' => Auth::user(),
            'instance' => Auth::instance()
        ]);
        exit;
    }

    /**
     * ========================================
     * RENOVAR SESSÃO (AJAX)
     * ========================================
     */
    public function renewSession()
    {
        header('Content-Type: application/json');

        if (Auth::check()) {
            // ✅ ATUALIZAR TIMESTAMP DA SESSÃO
            $_SESSION['last_activity'] = time();

            echo json_encode([
                'success' => true,
                'message' => 'Sessão renovada com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sessão expirada'
            ]);
        }
        exit;
    }
}

// ✅ LOG DE CARREGAMENTO
error_log("📁 AuthController carregado com sucesso");
