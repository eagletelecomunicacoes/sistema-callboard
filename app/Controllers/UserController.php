<?php
class UserController
{

    public function index()
    {
        Auth::requireLogin();

        // VERIFICAR SE É ADMIN
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $user = Auth::user();
        $instance = Auth::instance();

        $error = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';

        // Buscar usuários
        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);
            $stmt = $db->query("
                SELECT id, first_name, last_name, email, username, extension_number, role, status, 
                       created_at, last_login, is_online 
                FROM users 
                ORDER BY created_at DESC
            ");
            $users = $stmt->fetchAll();
        } catch (Exception $e) {
            $error = 'Erro ao carregar usuários: ' . $e->getMessage();
            $users = [];
        }

        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function create()
    {
        Auth::requireLogin();

        // VERIFICAR SE É ADMIN
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $user = Auth::user();
        $instance = Auth::instance();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $extensionNumber = trim($_POST['extension_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? 'user';

            // *** CORREÇÃO: Validar apenas números do ramal (Ex: 4554) ***
            $cleanExtension = preg_replace('/[^0-9]/', '', $extensionNumber);

            // Validar formato: deve ter apenas números (Ex: 4554)
            if (!empty($cleanExtension) && !preg_match('/^[0-9]{3,5}$/', $cleanExtension)) {
                $error = 'Ramal deve conter apenas números de 3 a 5 dígitos (Ex: 4554)';
            }

            $cleanExtension = empty($cleanExtension) ? null : $cleanExtension;

            // Validações básicas
            if (empty($error)) {
                if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
                    $error = 'Todos os campos obrigatórios devem ser preenchidos';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Email inválido';
                } elseif (strlen($password) < 6) {
                    $error = 'A senha deve ter pelo menos 6 caracteres';
                } elseif ($password !== $confirmPassword) {
                    $error = 'As senhas não coincidem';
                }
            }

            if (empty($error)) {
                try {
                    $db = DatabaseConfig::getInstanceDB($instance['database_name']);

                    // Verificar se email já existe
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Este email já está em uso';
                    } else {
                        // Verificar se username já existe
                        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                        $stmt->execute([$username]);
                        if ($stmt->fetch()) {
                            $error = 'Este nome de usuário já está em uso';
                        } else {
                            // Verificar se ramal já existe (se fornecido)
                            if ($cleanExtension) {
                                $stmt = $db->prepare("SELECT id FROM users WHERE extension_number = ?");
                                $stmt->execute([$cleanExtension]);
                                if ($stmt->fetch()) {
                                    $error = 'Este ramal já está em uso';
                                }
                            }

                            if (empty($error)) {
                                // Criar usuário
                                $stmt = $db->prepare("
                                    INSERT INTO users (first_name, last_name, email, username, extension_number, password, role) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)
                                ");

                                if ($stmt->execute([
                                    $firstName,
                                    $lastName,
                                    $email,
                                    $username,
                                    $cleanExtension,
                                    password_hash($password, PASSWORD_DEFAULT),
                                    $role
                                ])) {
                                    header('Location: ' . APP_URL . '/users?success=user_created');
                                    exit;
                                } else {
                                    $error = 'Erro ao criar usuário';
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Erro ao criar usuário: ' . $e->getMessage();
                }
            }
        }

        require_once __DIR__ . '/../Views/users/create.php';
    }

    public function edit()
    {
        Auth::requireLogin();

        // VERIFICAR SE É ADMIN
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $userId = $_GET['id'] ?? '';
        if (empty($userId)) {
            header('Location: ' . APP_URL . '/users?error=invalid_user');
            exit;
        }

        $user = Auth::user();
        $instance = Auth::instance();

        $error = '';
        $success = '';

        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);

            // Buscar usuário para edição
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $editUser = $stmt->fetch();

            if (!$editUser) {
                header('Location: ' . APP_URL . '/users?error=user_not_found');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $extensionNumber = trim($_POST['extension_number'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                $role = $_POST['role'] ?? 'user';

                // *** CORREÇÃO: Validar apenas números do ramal (Ex: 4554) ***
                $cleanExtension = preg_replace('/[^0-9]/', '', $extensionNumber);

                // Validar formato: deve ter apenas números (Ex: 4554)
                if (!empty($cleanExtension) && !preg_match('/^[0-9]{3,5}$/', $cleanExtension)) {
                    $error = 'Ramal deve conter apenas números de 3 a 5 dígitos (Ex: 4554)';
                }

                $cleanExtension = empty($cleanExtension) ? null : $cleanExtension;

                // Validações básicas
                if (empty($error)) {
                    if (empty($firstName) || empty($lastName) || empty($email) || empty($username)) {
                        $error = 'Todos os campos obrigatórios devem ser preenchidos';
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = 'Email inválido';
                    } elseif (!empty($password) && strlen($password) < 6) {
                        $error = 'A senha deve ter pelo menos 6 caracteres';
                    } elseif (!empty($password) && $password !== $confirmPassword) {
                        $error = 'As senhas não coincidem';
                    }
                }

                if (empty($error)) {
                    // Verificar se email já existe (exceto o próprio usuário)
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $userId]);
                    if ($stmt->fetch()) {
                        $error = 'Este email já está em uso por outro usuário';
                    } else {
                        // Verificar se username já existe (exceto o próprio usuário)
                        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                        $stmt->execute([$username, $userId]);
                        if ($stmt->fetch()) {
                            $error = 'Este nome de usuário já está em uso por outro usuário';
                        } else {
                            // Verificar se ramal já existe (exceto o próprio usuário)
                            if ($cleanExtension) {
                                $stmt = $db->prepare("SELECT id FROM users WHERE extension_number = ? AND id != ?");
                                $stmt->execute([$cleanExtension, $userId]);
                                if ($stmt->fetch()) {
                                    $error = 'Este ramal já está em uso por outro usuário';
                                }
                            }

                            if (empty($error)) {
                                // Atualizar usuário
                                if (!empty($password)) {
                                    $stmt = $db->prepare("
                                        UPDATE users 
                                        SET first_name = ?, last_name = ?, email = ?, username = ?, extension_number = ?, password = ?, role = ? 
                                        WHERE id = ?
                                    ");
                                    $params = [$firstName, $lastName, $email, $username, $cleanExtension, password_hash($password, PASSWORD_DEFAULT), $role, $userId];
                                } else {
                                    $stmt = $db->prepare("
                                        UPDATE users 
                                        SET first_name = ?, last_name = ?, email = ?, username = ?, extension_number = ?, role = ? 
                                        WHERE id = ?
                                    ");
                                    $params = [$firstName, $lastName, $email, $username, $cleanExtension, $role, $userId];
                                }

                                if ($stmt->execute($params)) {
                                    header('Location: ' . APP_URL . '/users?success=user_updated');
                                    exit;
                                } else {
                                    $error = 'Erro ao atualizar usuário';
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Erro ao editar usuário: ' . $e->getMessage();
        }

        require_once __DIR__ . '/../Views/users/edit.php';
    }

    public function delete()
    {
        Auth::requireLogin();

        // VERIFICAR SE É ADMIN
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $userId = $_POST['id'] ?? '';
        $currentUser = Auth::user();

        if (empty($userId)) {
            header('Location: ' . APP_URL . '/users?error=invalid_user');
            exit;
        }

        // Não permitir que o usuário delete a si mesmo
        if ($userId == $currentUser['id']) {
            header('Location: ' . APP_URL . '/users?error=cannot_delete_self');
            exit;
        }

        try {
            $instance = Auth::instance();
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                header('Location: ' . APP_URL . '/users?success=user_deleted');
            } else {
                header('Location: ' . APP_URL . '/users?error=delete_failed');
            }
        } catch (Exception $e) {
            header('Location: ' . APP_URL . '/users?error=delete_error');
        }
        exit;
    }

    public function changeStatus()
    {
        Auth::requireLogin();

        // VERIFICAR SE É ADMIN
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard?error=access_denied');
            exit;
        }

        $userId = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        $currentUser = Auth::user();

        if (empty($userId) || !in_array($status, ['active', 'inactive'])) {
            header('Location: ' . APP_URL . '/users?error=invalid_data');
            exit;
        }

        // Não permitir que o usuário desative a si mesmo
        if ($userId == $currentUser['id'] && $status === 'inactive') {
            header('Location: ' . APP_URL . '/users?error=cannot_deactivate_self');
            exit;
        }

        try {
            $instance = Auth::instance();
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);

            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $userId])) {
                $message = $status === 'active' ? 'user_activated' : 'user_deactivated';
                header('Location: ' . APP_URL . '/users?success=' . $message);
            } else {
                header('Location: ' . APP_URL . '/users?error=status_change_failed');
            }
        } catch (Exception $e) {
            header('Location: ' . APP_URL . '/users?error=status_change_error');
        }
        exit;
    }

    public function profile()
    {
        Auth::requireLogin();

        $user = Auth::user();
        $instance = Auth::instance();

        $error = '';
        $success = '';

        // Buscar dados atuais do usuário
        try {
            $db = DatabaseConfig::getInstanceDB($instance['database_name']);
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $currentUserData = $stmt->fetch();
        } catch (Exception $e) {
            $currentUserData = $user;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $extensionNumber = trim($_POST['extension_number'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // *** CORREÇÃO: Validar apenas números do ramal (Ex: 4554) ***
            $cleanExtension = preg_replace('/[^0-9]/', '', $extensionNumber);

            // Validar formato: deve ter apenas números (Ex: 4554)
            if (!empty($cleanExtension) && !preg_match('/^[0-9]{3,5}$/', $cleanExtension)) {
                $error = 'Ramal deve conter apenas números de 3 a 5 dígitos (Ex: 4554)';
            }

            $cleanExtension = empty($cleanExtension) ? null : $cleanExtension;

            // Validações básicas
            if (empty($error)) {
                if (empty($firstName) || empty($lastName) || empty($email)) {
                    $error = 'Nome, sobrenome e email são obrigatórios';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Email inválido';
                } elseif (!empty($newPassword) && empty($currentPassword)) {
                    $error = 'Digite a senha atual para alterar a senha';
                } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
                    $error = 'A nova senha deve ter pelo menos 6 caracteres';
                } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
                    $error = 'As senhas não coincidem';
                }
            }

            if (empty($error)) {
                try {
                    $db = DatabaseConfig::getInstanceDB($instance['database_name']);

                    // Verificar se email já existe (exceto o próprio usuário)
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user['id']]);
                    if ($stmt->fetch()) {
                        $error = 'Este email já está em uso por outro usuário';
                    }

                    // Verificar se ramal já existe (exceto o próprio usuário)
                    if (empty($error) && $cleanExtension) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE extension_number = ? AND id != ?");
                        $stmt->execute([$cleanExtension, $user['id']]);
                        if ($stmt->fetch()) {
                            $error = 'Este ramal já está em uso por outro usuário';
                        }
                    }

                    if (empty($error)) {
                        // Se está alterando senha, verificar senha atual
                        if (!empty($newPassword)) {
                            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                            $stmt->execute([$user['id']]);
                            $userData = $stmt->fetch();

                            if (!password_verify($currentPassword, $userData['password'])) {
                                $error = 'Senha atual incorreta';
                            } else {
                                // Atualizar com nova senha
                                $stmt = $db->prepare("
                                    UPDATE users 
                                    SET first_name = ?, last_name = ?, email = ?, extension_number = ?, password = ?, updated_at = CURRENT_TIMESTAMP
                                    WHERE id = ?
                                ");
                                $params = [$firstName, $lastName, $email, $cleanExtension, password_hash($newPassword, PASSWORD_DEFAULT), $user['id']];
                            }
                        } else {
                            // Atualizar sem alterar senha
                            $stmt = $db->prepare("
                                UPDATE users 
                                SET first_name = ?, last_name = ?, email = ?, extension_number = ?, updated_at = CURRENT_TIMESTAMP
                                WHERE id = ?
                            ");
                            $params = [$firstName, $lastName, $email, $cleanExtension, $user['id']];
                        }

                        if (empty($error) && $stmt->execute($params)) {
                            // Atualizar sessão
                            $_SESSION['user_first_name'] = $firstName;
                            $_SESSION['user_last_name'] = $lastName;
                            $_SESSION['user_email'] = $email;

                            $success = 'Perfil atualizado com sucesso!';

                            // Recarregar dados do usuário
                            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->execute([$user['id']]);
                            $currentUserData = $stmt->fetch();
                        } elseif (empty($error)) {
                            $error = 'Erro ao atualizar perfil';
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Erro ao atualizar perfil: ' . $e->getMessage();
                }
            }
        }

        require_once __DIR__ . '/../Views/users/profile.php';
    }
}
