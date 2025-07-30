<?php
require_once '../app/Config/database.php';
require_once '../app/Config/email.php';

class EmailReport
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConfig::getMySQL();
    }

    public function sendDailyReport($emails, $stats, $isTest = false)
    {
        try {
            $subject = $isTest ? '[TESTE] ' : '';
            $subject .= 'Relatório CDR - ' . date('d/m/Y', strtotime($stats['date']));

            $htmlContent = $this->generateReportHTML($stats);

            return $this->sendEmail($emails, $subject, $htmlContent);
        } catch (Exception $e) {
            throw new Exception("Erro ao enviar relatório: " . $e->getMessage());
        }
    }

    public function generateReportHTML($stats)
    {
        $date = date('d/m/Y', strtotime($stats['date']));
        $totalCalls = number_format($stats['total_calls'], 0, ',', '.');
        $inboundCalls = number_format($stats['inbound_calls'], 0, ',', '.');
        $outboundCalls = number_format($stats['outbound_calls'], 0, ',', '.');

        // Calcula percentuais
        $inboundPercent = $stats['total_calls'] > 0 ? round(($stats['inbound_calls'] / $stats['total_calls']) * 100, 1) : 0;
        $outboundPercent = $stats['total_calls'] > 0 ? round(($stats['outbound_calls'] / $stats['total_calls']) * 100, 1) : 0;

        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Relatório CDR - ' . $date . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f5f7fa;
                }
                .container {
                    background: white;
                    border-radius: 10px;
                    padding: 30px;
                    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #667eea;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #667eea;
                    margin: 0;
                    font-size: 28px;
                }
                .header p {
                    color: #666;
                    margin: 10px 0 0 0;
                    font-size: 16px;
                }
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                .stat-card {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 10px;
                    text-align: center;
                }
                .stat-number {
                    font-size: 32px;
                    font-weight: bold;
                    display: block;
                    margin-bottom: 5px;
                }
                .stat-label {
                    font-size: 14px;
                    opacity: 0.9;
                }
                .section {
                    margin-bottom: 30px;
                }
                .section h2 {
                    color: #667eea;
                    border-bottom: 2px solid #e1e8ed;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .table th,
                .table td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #e1e8ed;
                }
                .table th {
                    background: #f8f9fa;
                    font-weight: bold;
                    color: #667eea;
                }
                .table tr:hover {
                    background: #f8f9fa;
                }
                .chart-container {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .chart-bar {
                    display: flex;
                    align-items: center;
                    margin-bottom: 10px;
                }
                .chart-label {
                    width: 60px;
                    font-size: 12px;
                    margin-right: 10px;
                }
                .chart-bar-fill {
                    height: 20px;
                    background: #667eea;
                    border-radius: 3px;
                    margin-right: 10px;
                }
                .chart-value {
                    font-size: 12px;
                    font-weight: bold;
                }
                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #e1e8ed;
                    color: #666;
                    font-size: 14px;
                }
                .footer a {
                    color: #667eea;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📞 Relatório CDR</h1>
                    <p>Eagle Telecom - ' . $date . '</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">' . $totalCalls . '</span>
                        <div class="stat-label">📊 Total de Chamadas</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">' . $inboundCalls . '</span>
                        <div class="stat-label">📞 Recebidas (' . $inboundPercent . '%)</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">' . $outboundCalls . '</span>
                        <div class="stat-label">📱 Realizadas (' . $outboundPercent . '%)</div>
                    </div>
                </div>';

        // Top Usuários
        if (!empty($stats['top_users'])) {
            $html .= '
                <div class="section">
                    <h2>🏆 Top Usuários</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>👤 Usuário</th>
                                <th>📞 Chamadas</th>
                                <th>📅 Última Chamada</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($stats['top_users'] as $user) {
                $userName = htmlspecialchars($user->_id ?? 'N/A');
                $userCalls = number_format($user->total_calls ?? 0, 0, ',', '.');
                $lastCall = htmlspecialchars($user->last_call ?? 'N/A');

                $html .= "
                            <tr>
                                <td>$userName</td>
                                <td>$userCalls</td>
                                <td>$lastCall</td>
                            </tr>";
            }

            $html .= '
                        </tbody>
                    </table>
                </div>';
        }

        // Gráfico por hora
        if (!empty($stats['calls_by_hour'])) {
            $html .= '
                <div class="section">
                    <h2>📈 Distribuição por Horário</h2>
                    <div class="chart-container">';

            $maxCalls = max(array_column($stats['calls_by_hour'], 'calls'));

            foreach ($stats['calls_by_hour'] as $hourData) {
                if ($hourData['calls'] > 0) {
                    $width = $maxCalls > 0 ? ($hourData['calls'] / $maxCalls) * 300 : 0;
                    $html .= '
                        <div class="chart-bar">
                            <div class="chart-label">' . $hourData['hour'] . '</div>
                            <div class="chart-bar-fill" style="width: ' . $width . 'px;"></div>
                            <div class="chart-value">' . $hourData['calls'] . '</div>
                        </div>';
                }
            }

            $html .= '
                    </div>
                </div>';
        }

        $html .= '
                <div class="footer">
                    <p>Relatório gerado automaticamente pelo Sistema CDR</p>
                    <p><a href="' . APP_URL . '">Acessar Sistema Completo</a></p>
                    <p>&copy; ' . date('Y') . ' Eagle Telecom - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function sendEmail($emails, $subject, $htmlContent)
    {
        try {
            // Usando PHPMailer (instalar via Composer)
            require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once '../vendor/phpmailer/phpmailer/src/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Configuração SMTP
            $mail->isSMTP();
            $mail->Host = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::SMTP_USER;
            $mail->Password = EmailConfig::SMTP_PASS;
            $mail->SMTPSecure = EmailConfig::SMTP_SECURE;
            $mail->Port = EmailConfig::SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // Remetente
            $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);

            // Destinatários
            foreach ($emails as $email) {
                $mail->addAddress($email);
            }

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlContent;

            return $mail->send();
        } catch (Exception $e) {
            throw new Exception("Erro no envio: " . $e->getMessage());
        }
    }

    public function logReport($emails, $stats, $status, $error = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_reports (emails, total_calls, status, error_message, sent_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");

            return $stmt->execute([
                json_encode($emails),
                $stats['total_calls'] ?? 0,
                $status,
                $error
            ]);
        } catch (Exception $e) {
            // Log em arquivo se falhar no banco
            error_log("Erro ao salvar log de email: " . $e->getMessage());
        }
    }

    public function getLastReport()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM email_reports 
                ORDER BY sent_at DESC 
                LIMIT 1
            ");

            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}
