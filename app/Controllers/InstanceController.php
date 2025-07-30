<?php
class InstanceController
{

    public function index()
    {
        Auth::requireLogin();

        // Apenas super admin pode gerenciar instâncias
        if (!Auth::isSuperAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $error = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';

        // Buscar instâncias
        try {
            $db = DatabaseConfig::getMasterDB();
            $stmt = $db->query("
                SELECT id, name, subdomain, company_name, admin_email, status, created_at 
                FROM instances 
                ORDER BY created_at DESC
            ");
            $instances = $stmt->fetchAll();
        } catch (Exception $e) {
            $error = 'Erro ao carregar instâncias: ' . $e->getMessage();
            $instances = [];
        }

        require_once __DIR__ . '/../Views/instances/index.php';
    }

    public function create()
    {
        Auth::requireLogin();

        // Apenas super admin pode criar instâncias
        if (!Auth::isSuperAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $subdomain = trim($_POST['subdomain'] ?? '');
            $companyName = trim($_POST['company_name'] ?? '');
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPassword = $_POST['admin_password'] ?? '';

            // Validações
            if (empty($name) || empty($subdomain) || empty($companyName) || empty($adminEmail) || empty($adminPassword)) {
                $error = 'Todos os campos são obrigatórios';
            } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email do administrador inválido';
            } elseif (strlen($adminPassword) < 6) {
                $error = 'A senha deve ter pelo menos 6 caracteres';
            } elseif (!preg_match('/^[a-z0-9]+$/', $subdomain)) {
                $error = 'Subdomínio deve conter apenas letras minúsculas e números';
            } else {
                try {
                    $db = DatabaseConfig::getMasterDB();

                    // Verificar se subdomínio já existe
                    $stmt = $db->prepare("SELECT id FROM instances WHERE subdomain = ?");
                    $stmt->execute([$subdomain]);
                    if ($stmt->fetch()) {
                        $error = 'Este subdomínio já está em uso';
                    } else {
                        $databaseName = 'cdr_' . $subdomain;

                        // Criar instância
                        $stmt = $db->prepare("
                            INSERT INTO instances (name, subdomain, database_name, company_name, admin_email) 
                            VALUES (?, ?, ?, ?, ?)
                        ");

                        if ($stmt->execute([$name, $subdomain, $databaseName, $companyName, $adminEmail])) {
                            // Criar banco da instância e usuário admin
                            $instanceDB = DatabaseConfig::getInstanceDB($databaseName);

                            // Criar usuário admin da instância
                            $stmt = $instanceDB->prepare("
                                INSERT INTO users (first_name, last_name, email, username, password, role) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");

                            $nameParts = explode(' ', $companyName);
                            $firstName = $nameParts[0];
                            $lastName = isset($nameParts[1]) ? $nameParts[1] : 'Admin';

                            $stmt->execute([
                                $firstName,
                                $lastName,
                                $adminEmail,
                                'admin',
                                password_hash($adminPassword, PASSWORD_DEFAULT),
                                'admin'
                            ]);

                            header('Location: ' . APP_URL . '/instances?success=instance_created');
                            exit;
                        } else {
                            $error = 'Erro ao criar instância';
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Erro ao criar instância: ' . $e->getMessage();
                }
            }
        }

        require_once __DIR__ . '/../Views/instances/create.php';
    }
}
