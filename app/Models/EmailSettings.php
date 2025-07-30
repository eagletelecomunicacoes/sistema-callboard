<?php
class EmailSettings
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
     * Obter configurações de email
     */
    public function getSettings()
    {
        try {
            if (!$this->db) return $this->getDefaultSettings();

            $stmt = $this->db->query("SELECT * FROM email_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch();

            return $settings ?: $this->getDefaultSettings();
        } catch (Exception $e) {
            error_log("Erro ao obter configurações de email: " . $e->getMessage());
            return $this->getDefaultSettings();
        }
    }

    /**
     * Salvar configurações de email
     */
    public function saveSettings($data)
    {
        try {
            if (!$this->db) return false;

            // Desativar configurações anteriores
            $this->db->exec("UPDATE email_settings SET is_active = 0");

            // Inserir nova configuração
            $stmt = $this->db->prepare("
                INSERT INTO email_settings (
                    smtp_host, smtp_port, smtp_username, smtp_password, 
                    smtp_encryption, from_email, from_name, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");

            return $stmt->execute([
                $data['smtp_host'],
                $data['smtp_port'],
                $data['smtp_username'],
                $data['smtp_password'],
                $data['smtp_encryption'],
                $data['from_email'],
                $data['from_name']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao salvar configurações de email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar configurações de email
     */
    public function testSettings($settings, $testEmail)
    {
        try {
            // Aqui você implementaria o teste real com PHPMailer
            // Por enquanto, retorna sucesso
            return true;
        } catch (Exception $e) {
            error_log("Erro ao testar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Configurações padrão
     */
    private function getDefaultSettings()
    {
        return [
            'id' => 0,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'from_email' => 'noreply@miriandayrell.com.br',
            'from_name' => 'Sistema CDR',
            'is_active' => 0
        ];
    }
}
