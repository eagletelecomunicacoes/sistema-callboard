<?php

class UserEmailSettingsModel extends Model
{
    protected $table = 'user_email_settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'daily_reports',
        'weekly_reports',
        'monthly_reports',
        'send_time',
        'timezone',
        'include_total_calls',
        'include_avg_duration',
        'include_success_rate',
        'include_top_destinations',
        'include_charts',
        'include_recent_calls',
        'min_duration',
        'max_records',
        'email_enabled',
        'last_email_sent'
    ];

    /**
     * Buscar configurações do usuário
     */
    public function getUserSettings($userId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $settings = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se não existir, criar configurações padrão
            if (!$settings) {
                $this->createDefaultSettings($userId);
                return $this->getUserSettings($userId);
            }

            return $settings;
        } catch (PDOException $e) {
            error_log("Erro ao buscar configurações do usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salvar configurações do usuário
     */
    public function saveUserSettings($userId, $data)
    {
        try {
            // Verificar se já existe
            $existing = $this->getUserSettings($userId);

            if ($existing) {
                // Atualizar
                $sql = "UPDATE {$this->table} SET 
                        daily_reports = :daily_reports,
                        weekly_reports = :weekly_reports,
                        monthly_reports = :monthly_reports,
                        send_time = :send_time,
                        timezone = :timezone,
                        include_total_calls = :include_total_calls,
                        include_avg_duration = :include_avg_duration,
                        include_success_rate = :include_success_rate,
                        include_top_destinations = :include_top_destinations,
                        include_charts = :include_charts,
                        include_recent_calls = :include_recent_calls,
                        min_duration = :min_duration,
                        max_records = :max_records,
                        email_enabled = :email_enabled,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE user_id = :user_id";
            } else {
                // Inserir
                $sql = "INSERT INTO {$this->table} 
                        (user_id, daily_reports, weekly_reports, monthly_reports, send_time, timezone,
                         include_total_calls, include_avg_duration, include_success_rate, 
                         include_top_destinations, include_charts, include_recent_calls,
                         min_duration, max_records, email_enabled) 
                        VALUES 
                        (:user_id, :daily_reports, :weekly_reports, :monthly_reports, :send_time, :timezone,
                         :include_total_calls, :include_avg_duration, :include_success_rate,
                         :include_top_destinations, :include_charts, :include_recent_calls,
                         :min_duration, :max_records, :email_enabled)";
            }

            $data['user_id'] = $userId;

            // Converter checkboxes para boolean
            $booleanFields = [
                'daily_reports',
                'weekly_reports',
                'monthly_reports',
                'include_total_calls',
                'include_avg_duration',
                'include_success_rate',
                'include_top_destinations',
                'include_charts',
                'include_recent_calls',
                'email_enabled'
            ];

            foreach ($booleanFields as $field) {
                $data[$field] = isset($data[$field]) && $data[$field] ? 1 : 0;
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Erro ao salvar configurações do usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar configurações padrão para usuário
     */
    public function createDefaultSettings($userId)
    {
        try {
            $sql = "INSERT INTO {$this->table} (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao criar configurações padrão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuários com relatórios habilitados
     */
    public function getUsersWithReports($reportType = 'daily')
    {
        try {
            $sql = "SELECT u.*, ues.* 
                    FROM users u 
                    INNER JOIN {$this->table} ues ON u.id = ues.user_id 
                    WHERE ues.email_enabled = 1 
                    AND ues.{$reportType}_reports = 1 
                    AND u.status = 'active'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuários com relatórios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualizar último envio de email
     */
    public function updateLastEmailSent($userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET last_email_sent = CURRENT_TIMESTAMP WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar último envio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar estatísticas de email
     */
    public function getEmailStats()
    {
        try {
            $stats = [];

            // Usuários com email configurado
            $sql = "SELECT COUNT(*) as count FROM users WHERE email IS NOT NULL AND email != ''";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['users_with_email'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Usuários ativos
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email_enabled = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Relatórios por tipo
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE daily_reports = 1 AND email_enabled = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['daily_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE weekly_reports = 1 AND email_enabled = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['weekly_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE monthly_reports = 1 AND email_enabled = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['monthly_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            return [];
        }
    }
}
