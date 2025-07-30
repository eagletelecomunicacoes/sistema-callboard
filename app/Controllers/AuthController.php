<?php
class AuthController
{
    public function login()
    {
        // Se já está logado, redirecionar
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $error = '';
        $success = $_GET['message'] ?? '';
        $selectedInstance = $_GET['instance'] ?? '';

        // BUSCAR TODAS AS INSTÂNCIAS ATIVAS
        try {
            $db = DatabaseConfig::getMasterDB();
            $stmt = $db->query("SELECT id, name, subdomain, company_name FROM instances WHERE status = 'active' ORDER BY company_name");
            $instances = $stmt->fetchAll();
        } catch (Exception $e) {
            // Fallback para instância padrão
            $instances = [
                [
                    'id' => 1,
                    'name' => 'Mirian Dayrell Telecom',
                    'subdomain' => 'miriandayrell',
                    'company_name' => 'Mirian Dayrell Telecom'
                ]
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $instanceSubdomain = trim($_POST['instance'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($instanceSubdomain) || empty($email) || empty($password)) {
                $error = 'Todos os campos são obrigatórios';
            } else {
                // Buscar instância
                $instance = Auth::getInstance($instanceSubdomain);

                if (!$instance) {
                    $error = 'Empresa não encontrada ou inativa';
                } else {
                    // Validar usuário
                    $user = Auth::validateUser($email, $password, $instance);

                    if ($user) {
                        // Login bem-sucedido
                        Auth::login($user, $instance);
                        header('Location: ' . APP_URL . '/dashboard');
                        exit;
                    } else {
                        $error = 'Email/usuário ou senha incorretos';
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout()
    {
        $message = 'Você foi desconectado com sucesso!';
        Auth::logout();
        header('Location: ' . APP_URL . '/login?message=' . urlencode($message));
        exit;
    }
}
