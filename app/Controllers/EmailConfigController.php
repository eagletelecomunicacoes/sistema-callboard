<?php

class EmailController
{
    public function index()
    {
        // Verificar se usuário está logado
        if (!Auth::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $user = Auth::user();
        $isAdmin = Auth::isAdmin();

        // Dados básicos para a view
        $data = [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'smtpConfigured' => $this->isSmtpConfigured(),
            'userSettings' => $this->getUserSettings($user['id']),
            'lastEmailSent' => null,
            'emailsSent30d' => 0,
            'error' => '',
            'success' => ''
        ];

        // Processar formulário se enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'update_user_preferences':
                    if ($this->saveUserSettings($user['id'], $_POST)) {
                        $data['success'] = 'Configurações salvas com sucesso!';
                    } else {
                        $data['error'] = 'Erro ao salvar configurações.';
                    }
                    break;

                case 'send_test_email':
                    $testResult = $this->sendTestEmail($user['email']);
                    if ($testResult['success']) {
                        $data['success'] = $testResult['message'];
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                default:
                    $data['error'] = 'Ação não reconhecida';
            }
        }

        $this->view('email/config', $data);
    }

    public function adminConfig()
    {
        // Verificar se é admin
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/email-config');
            exit;
        }

        $user = Auth::user();
        $instance = Auth::instance();

        // Dados básicos para admin
        $data = [
            'user' => $user,
            'instance' => $instance,
            'smtpConfigured' => $this->isSmtpConfigured(),
            'smtpConfig' => $this->getSmtpConfig(),
            'emailStats' => $this->getEmailStats(),
            'allUsers' => $this->getAllUsersBasic(),
            'lastSmtpTest' => null,
            'error' => '',
            'success' => ''
        ];

        // Processar formulário se enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'update_smtp':
                    if ($this->saveSmtpConfig($_POST)) {
                        $data['success'] = 'Configurações SMTP salvas com sucesso!';
                        $data['smtpConfig'] = $this->getSmtpConfig(); // Recarregar
                    } else {
                        $data['error'] = 'Erro ao salvar configurações SMTP.';
                    }
                    break;

                case 'test_smtp':
                    $testEmail = $_POST['test_email'] ?? $user['email'];
                    $testResult = $this->testSmtpConfig($_POST, $testEmail);
                    if ($testResult['success']) {
                        $data['success'] = $testResult['message'];
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                case 'send_global_test':
                    $testEmail = $_POST['test_email'] ?? $user['email'];
                    $testResult = $this->sendTestEmail($testEmail);
                    if ($testResult['success']) {
                        $data['success'] = 'Email de teste global enviado para ' . $testEmail;
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                default:
                    $data['error'] = 'Ação não reconhecida';
            }
        }

        $this->view('email/admin-config', $data);
    }

    public function preview()
    {
        $reportType = $_GET['type'] ?? 'daily';
        $userId = $_GET['user_id'] ?? Auth::user()['id'];

        // Dados básicos para preview
        $templateData = [
            'reportType' => ucfirst($reportType),
            'reportDate' => date('d/m/Y'),
            'userName' => Auth::user()['first_name'],
            'companyName' => Auth::instance()['company_name'],
            'data' => [
                'calls_count' => 150,
                'avg_duration' => 3.5,
                'period' => 'hoje',
                'top_destinations' => [
                    ['destination' => '11999887766', 'count' => 25],
                    ['destination' => '11888776655', 'count' => 18],
                    ['destination' => '11777665544', 'count' => 12]
                ],
                'recent_calls' => [
                    ['src' => '1001', 'dst' => '11999887766', 'duration' => 180, 'calldate' => date('Y-m-d H:i:s')],
                    ['src' => '1002', 'dst' => '11888776655', 'duration' => 240, 'calldate' => date('Y-m-d H:i:s')],
                    ['src' => '1003', 'dst' => '11777665544', 'duration' => 120, 'calldate' => date('Y-m-d H:i:s')]
                ]
            ],
            'userSettings' => ['custom_filters' => ['include_charts' => true]],
            'appUrl' => APP_URL,
            'isPreview' => true
        ];

        $this->view('email/templates/report-template', $templateData);
    }

    // ===== MÉTODOS PRIVADOS =====

    private function sendTestEmail($toEmail)
    {
        try {
            // Verificar se SMTP está configurado
            if (!$this->isSmtpConfigured()) {
                return [
                    'success' => false,
                    'message' => 'SMTP não configurado. Configure nas configurações administrativas.'
                ];
            }

            $smtpConfig = $this->getSmtpConfig();

            // Usar PHPMailer se disponível, senão usar mail() nativo
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendEmailWithPHPMailer($smtpConfig, $toEmail);
            } else {
                return $this->sendEmailNative($toEmail);
            }
        } catch (Exception $e) {
            error_log("Erro no teste de email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    private function sendEmailWithPHPMailer($config, $toEmail)
    {
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $this->decryptPassword($config['smtp_password']);
            $mail->Port = $config['smtp_port'];

            if ($config['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($config['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Configurações do email
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = '🧪 Teste de Email - Sistema CDR - ' . date('d/m/Y H:i:s');
            $mail->Body = $this->getTestEmailTemplate();

            $result = $mail->send();

            if ($result) {
                $this->logEmailSent($toEmail, 'test', 'sent');
                return [
                    'success' => true,
                    'message' => 'Email de teste enviado com sucesso para ' . $toEmail
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao enviar email de teste'
            ];
        } catch (Exception $e) {
            $this->logEmailSent($toEmail, 'test', 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro PHPMailer: ' . $e->getMessage()
            ];
        }
    }

    private function sendEmailNative($toEmail)
    {
        try {
            $subject = '🧪 Teste de Email - Sistema CDR - ' . date('d/m/Y H:i:s');
            $message = $this->getTestEmailTemplate();

            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: Sistema CDR <noreply@sistema.com>',
                'Reply-To: noreply@sistema.com',
                'X-Mailer: PHP/' . phpversion()
            ];

            $result = mail($toEmail, $subject, $message, implode("\r\n", $headers));

            if ($result) {
                $this->logEmailSent($toEmail, 'test', 'sent');
                return [
                    'success' => true,
                    'message' => 'Email de teste enviado com sucesso para ' . $toEmail . ' (usando mail() nativo)'
                ];
            } else {
                $this->logEmailSent($toEmail, 'test', 'failed', 'Falha na função mail()');
                return [
                    'success' => false,
                    'message' => 'Falha ao enviar email usando função mail() nativa'
                ];
            }
        } catch (Exception $e) {
            $this->logEmailSent($toEmail, 'test', 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro mail() nativo: ' . $e->getMessage()
            ];
        }
    }

    private function testSmtpConfig($postData, $testEmail)
    {
        try {
            // Validar dados obrigatórios
            $required = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email', 'from_name'];
            foreach ($required as $field) {
                if (empty($postData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Campo obrigatório não preenchido: {$field}"
                    ];
                }
            }

            // Tentar enviar email de teste com as configurações fornecidas
            $config = [
                'smtp_host' => $postData['smtp_host'],
                'smtp_port' => $postData['smtp_port'],
                'smtp_username' => $postData['smtp_username'],
                'smtp_password' => $postData['smtp_password'], // Não criptografada ainda
                'smtp_encryption' => $postData['smtp_encryption'] ?? 'tls',
                'from_email' => $postData['from_email'],
                'from_name' => $postData['from_name']
            ];

            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                require_once __DIR__ . '/../../vendor/autoload.php';

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = $config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->Port = $config['smtp_port'];

                if ($config['smtp_encryption'] === 'ssl') {
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($config['smtp_encryption'] === 'tls') {
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                }

                $mail->setFrom($config['from_email'], $config['from_name']);
                $mail->addAddress($testEmail);

                $mail->isHTML(true);
                $mail->Subject = '🧪 Teste de Configuração SMTP - ' . date('d/m/Y H:i:s');
                $mail->Body = $this->getTestEmailTemplate();

                $result = $mail->send();

                if ($result) {
                    // Atualizar último teste no banco
                    $this->updateLastTest();
                    return [
                        'success' => true,
                        'message' => 'Teste SMTP realizado com sucesso! Email enviado para ' . $testEmail
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'PHPMailer não disponível. Instale via Composer.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro no teste SMTP: ' . $e->getMessage()
            ];
        }
    }

    private function getTestEmailTemplate()
    {
        $companyName = Auth::instance()['company_name'] ?? 'Sistema CDR';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Teste de Email</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="margin: 0; font-size: 24px;">�� Teste de Email</h1>
                <p style="margin: 10px 0 0 0; opacity: 0.9;">' . htmlspecialchars($companyName) . '</p>
            </div>
            
            <div style="background: white; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;">
                <h2 style="color: #2c3e50; margin-top: 0;">✅ Teste Realizado com Sucesso!</h2>
                
                <p>Este é um email de teste para verificar se as configurações SMTP estão funcionando corretamente.</p>
                
                <div style="background: #f8f9fc; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #3498db;">📋 Informações do Teste:</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</li>
                        <li><strong>Sistema:</strong> ' . htmlspecialchars($companyName) . '</li>
                        <li><strong>Versão:</strong> ' . APP_VERSION . '</li>
                        <li><strong>Servidor:</strong> ' . $_SERVER['SERVER_NAME'] . '</li>
                    </ul>
                </div>
                
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <strong>🎉 Parabéns!</strong> Suas configurações de email estão funcionando perfeitamente.
                </div>
                
                <p style="text-align: center; margin: 30px 0;">
                    <a href="' . APP_URL . '/dashboard" style="background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                        🚀 Acessar Dashboard
                    </a>
                </p>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                
                <p style="font-size: 12px; color: #666; text-align: center; margin: 0;">
                    Este email foi enviado automaticamente pelo sistema CDR para testar as configurações SMTP.<br>
                    Não é necessário responder esta mensagem.
                </p>
            </div>
        </body>
        </html>';
    }

    private function isSmtpConfigured()
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT COUNT(*) as count FROM email_smtp_config WHERE smtp_host IS NOT NULL AND smtp_host != ''";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getSmtpConfig()
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT * FROM email_smtp_config WHERE instance_id = 1 LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config) {
                return [
                    'smtp_host' => '',
                    'smtp_port' => 587,
                    'smtp_username' => '',
                    'smtp_password' => '',
                    'smtp_encryption' => 'tls',
                    'from_email' => '',
                    'from_name' => Auth::instance()['company_name'] ?? 'Sistema CDR'
                ];
            }

            return $config;
        } catch (Exception $e) {
            error_log("Erro ao buscar config SMTP: " . $e->getMessage());
            return [];
        }
    }

    private function saveSmtpConfig($data)
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);

            // Verificar se já existe
            $existing = $this->getSmtpConfig();

            if (!empty($existing['id'])) {
                // Atualizar
                $sql = "UPDATE email_smtp_config SET 
                        smtp_host = :smtp_host,
                        smtp_port = :smtp_port,
                        smtp_username = :smtp_username,
                        smtp_password = :smtp_password,
                        smtp_encryption = :smtp_encryption,
                        from_email = :from_email,
                        from_name = :from_name,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE instance_id = 1";
            } else {
                // Inserir
                $sql = "INSERT INTO email_smtp_config 
                        (instance_id, smtp_host, smtp_port, smtp_username, smtp_password, 
                         smtp_encryption, from_email, from_name) 
                        VALUES 
                        (1, :smtp_host, :smtp_port, :smtp_username, :smtp_password, 
                         :smtp_encryption, :from_email, :from_name)";
            }

            $stmt = $pdo->prepare($sql);

            // Criptografar senha
            $data['smtp_password'] = $this->encryptPassword($data['smtp_password']);

            return $stmt->execute([
                'smtp_host' => $data['smtp_host'],
                'smtp_port' => $data['smtp_port'],
                'smtp_username' => $data['smtp_username'],
                'smtp_password' => $data['smtp_password'],
                'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
                'from_email' => $data['from_email'],
                'from_name' => $data['from_name']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao salvar SMTP: " . $e->getMessage());
            return false;
        }
    }

    private function getUserSettings($userId)
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT * FROM user_email_settings WHERE user_id = :user_id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $settings = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$settings) {
                // Criar configurações padrão
                $this->createDefaultUserSettings($userId);
                return $this->getUserSettings($userId);
            }

            return $settings;
        } catch (Exception $e) {
            error_log("Erro ao buscar configurações do usuário: " . $e->getMessage());
            return [
                'daily_reports' => 0,
                'weekly_reports' => 0,
                'monthly_reports' => 0,
                'send_time' => '08:00:00',
                'timezone' => 'America/Sao_Paulo'
            ];
        }
    }

    private function saveUserSettings($userId, $data)
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);

            // Verificar se já existe
            $existing = $this->getUserSettings($userId);

            if (!empty($existing['id'])) {
                // Atualizar
                $sql = "UPDATE user_email_settings SET 
                        daily_reports = :daily_reports,
                        weekly_reports = :weekly_reports,
                        monthly_reports = :monthly_reports,
                        send_time = :send_time,
                        timezone = :timezone,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE user_id = :user_id";
            } else {
                // Inserir
                $sql = "INSERT INTO user_email_settings 
                        (user_id, daily_reports, weekly_reports, monthly_reports, send_time, timezone) 
                        VALUES 
                        (:user_id, :daily_reports, :weekly_reports, :monthly_reports, :send_time, :timezone)";
            }

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                'user_id' => $userId,
                'daily_reports' => isset($data['daily_reports']) ? 1 : 0,
                'weekly_reports' => isset($data['weekly_reports']) ? 1 : 0,
                'monthly_reports' => isset($data['monthly_reports']) ? 1 : 0,
                'send_time' => $data['send_time'] ?? '08:00:00',
                'timezone' => $data['timezone'] ?? 'America/Sao_Paulo'
            ]);
        } catch (Exception $e) {
            error_log("Erro ao salvar configurações do usuário: " . $e->getMessage());
            return false;
        }
    }

    private function createDefaultUserSettings($userId)
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "INSERT INTO user_email_settings (user_id) VALUES (:user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao criar configurações padrão: " . $e->getMessage());
            return false;
        }
    }

    private function getEmailStats()
    {
        return [
            'users_with_email' => 2,
            'emails_today' => 0,
            'emails_week' => 0,
            'success_rate' => 100,
            'active_users' => 2,
            'daily_users' => 0,
            'weekly_users' => 0,
            'monthly_users' => 0,
            'failures_7d' => 0
        ];
    }

    private function logEmailSent($toEmail, $type, $status, $errorMessage = null)
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "INSERT INTO email_logs (user_id, report_type, email_to, subject, status, error_message, sent_at) 
                    VALUES (:user_id, :report_type, :email_to, :subject, :status, :error_message, :sent_at)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => Auth::user()['id'],
                'report_type' => $type,
                'email_to' => $toEmail,
                'subject' => 'Teste de Email - ' . date('d/m/Y H:i:s'),
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null
            ]);
        } catch (Exception $e) {
            error_log("Erro ao registrar log de email: " . $e->getMessage());
        }
    }

    private function updateLastTest()
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "UPDATE email_smtp_config SET last_test = CURRENT_TIMESTAMP WHERE instance_id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar último teste: " . $e->getMessage());
        }
    }

    private function encryptPassword($password)
    {
        $key = 'cdr_email_key_2025_secure';
        return base64_encode(openssl_encrypt($password, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16)));
    }

    private function decryptPassword($encryptedPassword)
    {
        $key = 'cdr_email_key_2025_secure';
        return openssl_decrypt(base64_decode($encryptedPassword), 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }

    private function view($viewPath, $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>🚨 View não encontrada: {$viewPath}</h1>";
            echo "<p>Arquivo esperado: {$viewFile}</p>";
        }
    }

    private function getAllUsersBasic()
    {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT id, first_name, last_name, email, role, status, created_at FROM users ORDER BY first_name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['daily_reports'] = 0;
                $user['weekly_reports'] = 0;
                $user['monthly_reports'] = 0;
                $user['email_enabled'] = 1;
                $user['last_email_sent'] = null;
            }

            return $users;
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }
}
