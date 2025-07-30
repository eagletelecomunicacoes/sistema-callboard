<?php

class EmailSmtpConfigModel extends Model
{
    protected $table = 'email_smtp_config';
    protected $primaryKey = 'id';

    protected $fillable = [
        'instance_id',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_email',
        'from_name',
        'is_active',
        'last_test'
    ];

    /**
     * Buscar configuração SMTP da instância atual
     */
    public function getInstanceConfig($instanceId = 1)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE instance_id = :instance_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':instance_id', $instanceId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar config SMTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salvar/Atualizar configuração SMTP
     */
    public function saveConfig($data, $instanceId = 1)
    {
        try {
            // Verificar se já existe configuração
            $existing = $this->getInstanceConfig($instanceId);

            if ($existing) {
                // Atualizar
                $sql = "UPDATE {$this->table} SET 
                        smtp_host = :smtp_host,
                        smtp_port = :smtp_port,
                        smtp_username = :smtp_username,
                        smtp_password = :smtp_password,
                        smtp_encryption = :smtp_encryption,
                        from_email = :from_email,
                        from_name = :from_name,
                        is_active = :is_active,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE instance_id = :instance_id";
            } else {
                // Inserir
                $sql = "INSERT INTO {$this->table} 
                        (instance_id, smtp_host, smtp_port, smtp_username, smtp_password, 
                         smtp_encryption, from_email, from_name, is_active) 
                        VALUES 
                        (:instance_id, :smtp_host, :smtp_port, :smtp_username, :smtp_password, 
                         :smtp_encryption, :from_email, :from_name, :is_active)";
                $data['instance_id'] = $instanceId;
            }

            $stmt = $this->db->prepare($sql);

            // Criptografar senha antes de salvar
            if (!empty($data['smtp_password'])) {
                $data['smtp_password'] = $this->encryptPassword($data['smtp_password']);
            }

            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Erro ao salvar config SMTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar configuração SMTP
     */
    public function testConfig($config, $testEmail)
    {
        try {
            // Descriptografar senha
            $config['smtp_password'] = $this->decryptPassword($config['smtp_password']);

            // Configurar PHPMailer
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->Port = $config['smtp_port'];

            if ($config['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($config['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($testEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Teste de Configuração SMTP - ' . date('d/m/Y H:i:s');
            $mail->Body = $this->getTestEmailTemplate();

            $result = $mail->send();

            if ($result) {
                // Atualizar último teste
                $this->updateLastTest($config['instance_id'] ?? 1);
                return ['success' => true, 'message' => 'Email de teste enviado com sucesso!'];
            }

            return ['success' => false, 'message' => 'Falha ao enviar email de teste'];
        } catch (Exception $e) {
            error_log("Erro no teste SMTP: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Atualizar último teste
     */
    private function updateLastTest($instanceId)
    {
        try {
            $sql = "UPDATE {$this->table} SET last_test = CURRENT_TIMESTAMP WHERE instance_id = :instance_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':instance_id', $instanceId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar último teste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se SMTP está configurado
     */
    public function isConfigured($instanceId = 1)
    {
        $config = $this->getInstanceConfig($instanceId);
        return $config && !empty($config['smtp_host']) && !empty($config['smtp_username']);
    }

    /**
     * Criptografar senha
     */
    private function encryptPassword($password)
    {
        $key = 'cdr_email_key_2025'; // Use uma chave mais segura em produção
        return base64_encode(openssl_encrypt($password, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16)));
    }

    /**
     * Descriptografar senha
     */
    private function decryptPassword($encryptedPassword)
    {
        $key = 'cdr_email_key_2025';
        return openssl_decrypt(base64_decode($encryptedPassword), 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }

    /**
     * Template de email de teste
     */
    private function getTestEmailTemplate()
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #3498db;">🧪 Teste de Configuração SMTP</h2>
                <p>Este é um email de teste para verificar se as configurações SMTP estão funcionando corretamente.</p>
                
                <div style="background: #f8f9fc; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>✅ Configuração Testada:</h3>
                    <ul>
                        <li>Conexão com servidor SMTP</li>
                        <li>Autenticação de usuário</li>
                        <li>Envio de email</li>
                        <li>Formatação HTML</li>
                    </ul>
                </div>
                
                <p><strong>Data/Hora do Teste:</strong> ' . date('d/m/Y H:i:s') . '</p>
                <p><strong>Sistema:</strong> CDR System</p>
                
                <hr style="margin: 20px 0;">
                <p style="font-size: 12px; color: #666;">
                    Este email foi enviado automaticamente pelo sistema CDR para testar as configurações SMTP.
                </p>
            </div>
        </body>
        </html>';
    }
}
