<?php
require_once '../app/Config/database.php';
require_once '../app/Helpers/Toastr.php';

class SetupController
{

    public function createFirstAdmin()
    {
        // Verificar se já existem usuários
        if ($this->hasUsers()) {
            Toastr::info('Sistema já foi configurado! Faça login para continuar.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $fullName = trim($_POST['full_name'] ?? '');

                // Validações
                if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
                    Toastr::warning('Todos os campos são obrigatórios');
                    throw new Exception('Campos obrigatórios');
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Toastr::warning('Email inválido. Verifique o formato.');
                    throw new Exception('Email inválido');
                }

                if (strlen($password) < 6) {
                    Toastr::warning('Senha deve ter pelo menos 6 caracteres');
                    throw new Exception('Senha muito curta');
                }

                // Criar primeiro admin
                $mysql = DatabaseConfig::getMySQL();
                $stmt = $mysql->prepare("
                    INSERT INTO users (username, email, password, full_name, role, status) 
                    VALUES (?, ?, ?, ?, 'admin', 'active')
                ");

                $result = $stmt->execute([
                    $username,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $fullName
                ]);

                if ($result) {
                    // Marcar para mostrar credenciais no login
                    $_SESSION['show_credentials'] = true;
                    $_SESSION['first_admin_created'] = true;

                    Toastr::success('🎉 Primeiro administrador criado com sucesso! Sistema configurado e pronto para uso!', 'Configuração Concluída');

                    header('Location: ' . APP_URL . '/login');
                    exit;
                } else {
                    Toastr::error('Erro ao criar administrador no banco de dados');
                    throw new Exception('Erro ao criar administrador');
                }
            } catch (Exception $e) {
                if (!Toastr::hasMessages()) {
                    Toastr::error('Erro inesperado: ' . $e->getMessage());
                }
            }
        }

        // Verificar se o arquivo da view existe
        $viewPath = '../app/Views/setup/first-admin.php';
        if (!file_exists($viewPath)) {
            // Criar diretório se não existir
            $dir = dirname($viewPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            Toastr::error('Arquivo de configuração não encontrado. Verifique a instalação.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        require_once $viewPath;
    }

    private function hasUsers()
    {
        try {
            $mysql = DatabaseConfig::getMySQL();
            $stmt = $mysql->query("SELECT COUNT(*) as total FROM users");
            $result = $stmt->fetch();
            return $result['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
