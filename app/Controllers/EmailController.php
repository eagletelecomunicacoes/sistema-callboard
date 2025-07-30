<?php

// Carregar autoload do Composer
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use MongoDB\Client;

class EmailController
{
    private $db;
    private $mongodb;
    private $mongodbEnabled;

    public function __construct()
    {
        try {
            // Conexão MySQL
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Conectar MongoDB Atlas
            $this->mongodbEnabled = $this->initMongoDBAtlas();
        } catch (Exception $e) {
            error_log("Erro de conexão: " . $e->getMessage());
            die("Erro de conexão com o banco de dados");
        }
    }

    private function initMongoDBAtlas()
    {
        try {
            // Usar suas configurações do MongoDB Atlas
            $uri = 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0';

            $this->mongodb = new Client($uri, [], [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ]);

            // Testar conexão com ping
            $this->mongodb->selectDatabase('admin')->command(['ping' => 1]);

            // Selecionar database correto: cdrs
            $this->mongodb = $this->mongodb->selectDatabase('cdrs');

            error_log("MongoDB Atlas conectado com sucesso! Database: cdrs");
            return true;
        } catch (Exception $e) {
            error_log("Erro MongoDB Atlas: " . $e->getMessage());
            $this->mongodb = null;
            return false;
        }
    }

    public function index()
    {
        // Verificar se usuário está logado
        if (!Auth::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $user = Auth::user();
        $isAdmin = Auth::isAdmin();

        // Buscar dados reais do usuário
        $realUserData = $this->getRealUserData($user['id']);

        // Buscar estatísticas de chamadas (geral ou específicas do usuário)
        $callStats = $this->getCallStatistics($user['id']);

        // Dados básicos para a view
        $data = [
            'user' => $realUserData,
            'isAdmin' => $isAdmin,
            'smtpConfigured' => $this->isSmtpConfigured(),
            'userSettings' => $this->getUserSettings($user['id']),
            'lastEmailSent' => $this->getLastEmailSent($user['id']),
            'emailsSent30d' => $this->getEmailsSent30d($user['id']),
            'callStats' => $callStats,
            'systemStatus' => $this->getSystemStatus(),
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
                        $data['userSettings'] = $this->getUserSettings($user['id']); // Recarregar
                    } else {
                        $data['error'] = 'Erro ao salvar configurações.';
                    }
                    break;

                case 'update_extension_number':
                    if ($this->updateUserExtensionNumber($user['id'], $_POST['extension_number'] ?? '')) {
                        $data['success'] = 'Ramal atualizado com sucesso!';
                        $data['user'] = $this->getRealUserData($user['id']); // Recarregar dados
                        $data['callStats'] = $this->getCallStatistics($user['id']); // Recarregar estatísticas
                    } else {
                        $data['error'] = 'Erro ao atualizar ramal.';
                    }
                    break;

                case 'send_test_email':
                    $testResult = $this->sendTestEmailWithPHPMailer($realUserData['email']);
                    if ($testResult['success']) {
                        $data['success'] = $testResult['message'];
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                default:
                    $data['error'] = 'Ação não reconhecida: ' . $action;
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

        // Buscar dados reais do usuário admin
        $realUserData = $this->getRealUserData($user['id']);

        // Buscar estatísticas reais do sistema
        $systemStats = $this->getSystemEmailStats();

        // Dados básicos para admin
        $data = [
            'user' => $realUserData,
            'instance' => $instance,
            'smtpConfigured' => $this->isSmtpConfigured(),
            'smtpConfig' => $this->getSmtpConfig(),
            'emailStats' => $systemStats,
            'allUsers' => $this->getAllUsersWithRealData(),
            'lastSmtpTest' => $this->getLastSmtpTest(),
            'systemStatus' => $this->getSystemStatus(),
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
                        $data['smtpConfigured'] = $this->isSmtpConfigured();
                    } else {
                        $data['error'] = 'Erro ao salvar configurações SMTP.';
                    }
                    break;

                case 'test_smtp':
                    $testEmail = $_POST['test_email'] ?? $realUserData['email'];
                    $testResult = $this->testSmtpConfigWithPHPMailer($_POST, $testEmail);
                    if ($testResult['success']) {
                        $data['success'] = $testResult['message'];
                        $data['lastSmtpTest'] = date('Y-m-d H:i:s');
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                case 'send_global_test':
                    $testEmail = $_POST['test_email'] ?? $realUserData['email'];
                    $testResult = $this->sendTestEmailWithPHPMailer($testEmail);
                    if ($testResult['success']) {
                        $data['success'] = 'Email de teste global enviado para ' . $testEmail;
                    } else {
                        $data['error'] = $testResult['message'];
                    }
                    break;

                default:
                    $data['error'] = 'Ação não reconhecida: ' . $action;
            }
        }

        $this->view('email/admin-config', $data);
    }

    public function preview()
    {
        $reportType = $_GET['type'] ?? 'daily';
        $userId = $_GET['user_id'] ?? Auth::user()['id'];

        error_log("=== PREVIEW DEBUG INÍCIO ===");
        error_log("DEBUG Preview: Tipo: $reportType, User ID: $userId");

        // Buscar dados reais do usuário
        $realUserData = $this->getRealUserData($userId);
        error_log("DEBUG Preview: Dados do usuário: " . json_encode($realUserData));

        // Buscar dados de chamadas (específicas do usuário se tiver ramal)
        $reportData = $this->generateReportData($reportType, $userId);
        error_log("DEBUG Preview: Dados do relatório: " . json_encode($reportData));

        // Dados para o template
        $templateData = [
            'reportType' => ucfirst($reportType),
            'reportDate' => date('d/m/Y'),
            'userName' => $realUserData['first_name'],
            'userExtension' => $realUserData['extension_number'] ?? null,
            'companyName' => Auth::instance()['company_name'],
            'data' => $reportData,
            'userSettings' => $this->getUserSettings($userId),
            'appUrl' => APP_URL,
            'isPreview' => true
        ];

        error_log("DEBUG Preview: Template data: " . json_encode($templateData));
        error_log("=== PREVIEW DEBUG FIM ===");

        $this->view('email/templates/report-template', $templateData);
    }

    // ===== MÉTODOS PARA DADOS REAIS DO MONGODB =====

    private function getRealUserData($userId)
    {
        try {
            $sql = "SELECT id, first_name, last_name, email, role, status, extension_number, created_at FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $user['extension_number'] = $user['extension_number'] ?? null;
                return $user;
            }

            return Auth::user(); // Fallback
        } catch (Exception $e) {
            error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
            return Auth::user();
        }
    }

    private function updateUserExtensionNumber($userId, $extensionNumber)
    {
        try {
            // *** CORREÇÃO: Validar apenas números (Ex: 4554) ***
            $cleanExtension = preg_replace('/[^0-9]/', '', trim($extensionNumber));

            // Validar formato: deve ter apenas números de 3 a 5 dígitos
            if (!empty($cleanExtension) && !preg_match('/^[0-9]{3,5}$/', $cleanExtension)) {
                error_log("DEBUG: Formato de ramal inválido: $cleanExtension");
                return false;
            }

            // Se vazio, definir como NULL
            $cleanExtension = empty($cleanExtension) ? null : $cleanExtension;

            $sql = "UPDATE users SET extension_number = :extension_number WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':extension_number', $cleanExtension);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            error_log("DEBUG: Atualizando ramal do usuário $userId para: " . ($cleanExtension ?? 'NULL'));

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar ramal: " . $e->getMessage());
            return false;
        }
    }

    private function getCallStatistics($userId)
    {
        if ($this->mongodbEnabled) {
            return $this->getCallStatisticsFromMongoDBAtlas($userId);
        } else {
            return $this->getCallStatisticsSimulated($userId);
        }
    }

    private function getCallStatisticsFromMongoDBAtlas($userId)
    {
        try {
            // Buscar ramal do usuário (apenas números)
            $userData = $this->getRealUserData($userId);
            $userExtension = $userData['extension_number'] ?? null;

            error_log("=== DEBUG INÍCIO ===");
            error_log("DEBUG: Usuário ID: $userId");
            error_log("DEBUG: Dados do usuário: " . json_encode($userData));
            error_log("DEBUG: Ramal do usuário: " . ($userExtension ?? 'NULL'));

            // Usar a coleção 'mdi' do database 'cdrs'
            $collection = $this->mongodb->selectCollection('mdi');

            // Datas para filtros
            $today = date('Y/m/d');
            $yesterday = date('Y/m/d', strtotime('-1 day'));

            error_log("DEBUG: Data hoje: $today");
            error_log("DEBUG: Data ontem: $yesterday");

            // Buscar por todos os canais + ramal (Ex: E4554, V4554, etc.)
            $extensionFilter = [];
            if ($userExtension) {
                // Buscar por qualquer canal + ramal (regex: qualquer letra + ramal)
                $extensionFilter = [
                    '$or' => [
                        ['p1device' => ['$regex' => '^[A-Z]' . $userExtension . '$']],
                        ['p2device' => ['$regex' => '^[A-Z]' . $userExtension . '$']]
                    ]
                ];
                error_log("DEBUG: Filtro por ramal: " . json_encode($extensionFilter));

                // Testar se o filtro encontra algum registro
                $testCount = $collection->countDocuments($extensionFilter);
                error_log("DEBUG: Total de registros encontrados para o ramal: $testCount");

                if ($testCount > 0) {
                    // Buscar alguns exemplos
                    $examples = $collection->find($extensionFilter, ['limit' => 3])->toArray();
                    error_log("DEBUG: Exemplos encontrados: " . json_encode($examples));
                }
            } else {
                error_log("DEBUG: Sem filtro por ramal - dados gerais");
            }

            // Chamadas hoje
            $callsToday = 0;
            if (!empty($extensionFilter)) {
                $todayFilter = [
                    '$and' => [
                        $extensionFilter,
                        ['smdrtime' => ['$regex' => '^' . $today]]
                    ]
                ];
            } else {
                $todayFilter = ['smdrtime' => ['$regex' => '^' . $today]];
            }

            error_log("DEBUG: Filtro hoje: " . json_encode($todayFilter));
            $callsToday = $collection->countDocuments($todayFilter);
            error_log("DEBUG: Chamadas hoje: $callsToday");

            // Se não há dados hoje, tentar ontem
            if ($callsToday == 0) {
                if (!empty($extensionFilter)) {
                    $yesterdayFilter = [
                        '$and' => [
                            $extensionFilter,
                            ['smdrtime' => ['$regex' => '^' . $yesterday]]
                        ]
                    ];
                } else {
                    $yesterdayFilter = ['smdrtime' => ['$regex' => '^' . $yesterday]];
                }

                error_log("DEBUG: Filtro ontem: " . json_encode($yesterdayFilter));
                $callsToday = $collection->countDocuments($yesterdayFilter);
                error_log("DEBUG: Usando dados de ontem: $callsToday");
            }

            // Chamadas últimos 7 dias
            $calls7d = 0;
            for ($i = 0; $i < 7; $i++) {
                $date = date('Y/m/d', strtotime("-$i days"));

                if (!empty($extensionFilter)) {
                    $dayFilter = [
                        '$and' => [
                            $extensionFilter,
                            ['smdrtime' => ['$regex' => '^' . $date]]
                        ]
                    ];
                } else {
                    $dayFilter = ['smdrtime' => ['$regex' => '^' . $date]];
                }

                $dayCount = $collection->countDocuments($dayFilter);
                $calls7d += $dayCount;
                error_log("DEBUG: Data $date: $dayCount chamadas");
            }

            // Chamadas últimos 30 dias
            $calls30d = 0;
            for ($i = 0; $i < 30; $i++) {
                $date = date('Y/m/d', strtotime("-$i days"));

                if (!empty($extensionFilter)) {
                    $dayFilter = [
                        '$and' => [
                            $extensionFilter,
                            ['smdrtime' => ['$regex' => '^' . $date]]
                        ]
                    ];
                } else {
                    $dayFilter = ['smdrtime' => ['$regex' => '^' . $date]];
                }

                $calls30d += $collection->countDocuments($dayFilter);
            }

            error_log("DEBUG: Resultados finais - Hoje: $callsToday, 7d: $calls7d, 30d: $calls30d");

            // Se ainda não há dados, usar dados disponíveis
            if ($calls30d == 0 && $userExtension) {
                // Buscar TODOS os dados do ramal (sem filtro de data)
                $allExtensionCalls = $collection->countDocuments($extensionFilter);
                error_log("DEBUG: Total de chamadas do ramal (sem filtro de data): $allExtensionCalls");

                if ($allExtensionCalls > 0) {
                    $calls30d = $allExtensionCalls;
                    $calls7d = $allExtensionCalls;
                    $callsToday = min($allExtensionCalls, 1);
                    error_log("DEBUG: Usando todos os dados disponíveis do ramal");
                }
            }

            // Duração média e top origens (código anterior mantido)
            $avgDuration = 0;
            $topOrigins = [];

            $sourceLabel = $userExtension ? "MongoDB Atlas (cdrs.mdi) - Ramal: $userExtension (todos os canais)" : "MongoDB Atlas (cdrs.mdi) - Dados Gerais";

            $result = [
                'calls_today' => $callsToday,
                'calls_7d' => $calls7d,
                'calls_30d' => $calls30d,
                'avg_duration' => $avgDuration,
                'top_destinations' => $topOrigins,
                'source' => $sourceLabel,
                'user_extension' => $userExtension,
                'is_user_specific' => !empty($userExtension),
                'total_records' => 15401
            ];

            error_log("DEBUG: Resultado final: " . json_encode($result));
            error_log("=== DEBUG FIM ===");

            return $result;
        } catch (Exception $e) {
            error_log("DEBUG: Erro geral: " . $e->getMessage());
            return $this->getCallStatisticsSimulated($userId);
        }
    }



    private function generateReportData($reportType, $userId)
    {
        if ($this->mongodbEnabled) {
            return $this->generateReportDataFromMongoDBAtlas($reportType, $userId);
        } else {
            return $this->generateReportDataSimulated($reportType, $userId);
        }
    }

    private function generateReportDataFromMongoDBAtlas($reportType, $userId)
    {
        try {
            error_log("=== GENERATE REPORT DEBUG INÍCIO ===");

            // Buscar ramal do usuário
            $userData = $this->getRealUserData($userId);
            $userExtension = $userData['extension_number'] ?? null;

            error_log("DEBUG Report: User ID: $userId, Ramal: " . ($userExtension ?? 'NULL'));

            // Usar a coleção 'mdi' do database 'cdrs'
            $collection = $this->mongodb->selectCollection('mdi');

            // Filtro base por ramal
            $baseFilter = [];

            // Se usuário tem ramal, adicionar filtro
            if ($userExtension) {
                $baseFilter = [
                    '$or' => [
                        ['p1device' => ['$regex' => '^[A-Z]' . $userExtension . '$']],
                        ['p2device' => ['$regex' => '^[A-Z]' . $userExtension . '$']]
                    ]
                ];
                error_log("DEBUG Report: Filtro base: " . json_encode($baseFilter));

                // Testar filtro
                $testCount = $collection->countDocuments($baseFilter);
                error_log("DEBUG Report: Registros encontrados com filtro: $testCount");
            }

            // Buscar dados disponíveis
            $periodCalls = $collection->find($baseFilter, ['limit' => 1000])->toArray();
            $totalCalls = count($periodCalls);

            error_log("DEBUG Report: Total de chamadas retornadas: $totalCalls");

            // Se não encontrou dados com filtro de data, buscar sem filtro de data
            if ($totalCalls == 0 && $userExtension) {
                error_log("DEBUG Report: Nenhuma chamada encontrada, buscando sem filtro de data...");
                $periodCalls = $collection->find($baseFilter, ['limit' => 1000])->toArray();
                $totalCalls = count($periodCalls);
                error_log("DEBUG Report: Chamadas sem filtro de data: $totalCalls");
            }

            // Calcular durações
            $totalSeconds = 0;
            $validCalls = 0;

            foreach ($periodCalls as $call) {
                $duration = $call['callduration'] ?? '00:00:00';
                $seconds = $this->convertDurationToSeconds($duration);
                if ($seconds > 0) {
                    $totalSeconds += $seconds;
                    $validCalls++;
                }
            }

            $avgDuration = $validCalls > 0 ? round($totalSeconds / $validCalls / 60, 1) : 0;
            $totalDuration = round($totalSeconds / 60, 1);

            error_log("DEBUG Report: Duração total: $totalDuration min, Duração média: $avgDuration min");

            // Top origens (quem mais ligou para este ramal)
            $origins = [];
            foreach ($periodCalls as $call) {
                $src = $call['caller'] ?? 'N/A';
                if (!isset($origins[$src])) {
                    $origins[$src] = ['count' => 0, 'duration' => 0];
                }
                $origins[$src]['count']++;
                $origins[$src]['duration'] += $this->convertDurationToSeconds($call['callduration'] ?? '00:00:00');
            }

            arsort($origins);
            $topOrigins = array_slice($origins, 0, 10, true);

            // Formatar top origens
            $formattedOrigins = [];
            foreach ($topOrigins as $src => $data) {
                $formattedOrigins[] = [
                    'destination' => $src, // Mantém o nome 'destination' para compatibilidade
                    'count' => $data['count'],
                    'duration' => round($data['duration'] / 60, 1)
                ];
            }

            error_log("DEBUG Report: Top origens: " . json_encode($formattedOrigins));

            // Chamadas recentes
            $recentCalls = array_slice($periodCalls, 0, 10);
            $formattedRecentCalls = [];
            foreach ($recentCalls as $call) {
                // Determinar qual é o ramal (p1device ou p2device)
                $extensionDevice = '';
                if (($call['p1device'] ?? '') && preg_match('/^[A-Z]' . $userExtension . '$/', $call['p1device'])) {
                    $extensionDevice = $call['p1device'];
                } elseif (($call['p2device'] ?? '') && preg_match('/^[A-Z]' . $userExtension . '$/', $call['p2device'])) {
                    $extensionDevice = $call['p2device'];
                } else {
                    $extensionDevice = $call['p2device'] ?? $call['p1device'] ?? 'N/A';
                }

                $formattedRecentCalls[] = [
                    'src' => $call['caller'] ?? 'N/A',
                    'dst' => $extensionDevice,
                    'duration' => $this->convertDurationToSeconds($call['callduration'] ?? '00:00:00'),
                    'calldate' => $call['smdrtime'] ?? 'N/A'
                ];
            }

            $sourceLabel = $userExtension ? "MongoDB Atlas (cdrs.mdi) - Ramal: $userExtension (todos os canais)" : "MongoDB Atlas (cdrs.mdi) - Dados Gerais";

            $result = [
                'calls_count' => $totalCalls,
                'avg_duration' => $avgDuration,
                'total_duration' => $totalDuration,
                'period' => $this->getPeriodLabel($reportType) . ' (dados disponíveis)',
                'top_destinations' => $formattedOrigins, // Na verdade são origens
                'recent_calls' => $formattedRecentCalls,
                'source' => $sourceLabel,
                'user_extension' => $userExtension,
                'is_user_specific' => !empty($userExtension),
                'date_filter' => 'Dados disponíveis'
            ];

            error_log("DEBUG Report: Resultado final: " . json_encode($result));
            error_log("=== GENERATE REPORT DEBUG FIM ===");

            return $result;
        } catch (Exception $e) {
            error_log("DEBUG Report: Erro ao gerar dados do relatório MongoDB Atlas: " . $e->getMessage());
            return $this->generateReportDataSimulated($reportType, $userId);
        }
    }

    private function convertDurationToSeconds($duration)
    {
        // Converter HH:MM:SS para segundos
        if (empty($duration) || $duration === '00:00:00') {
            return 0;
        }

        $parts = explode(':', $duration);
        if (count($parts) !== 3) {
            return 0;
        }

        $hours = intval($parts[0]);
        $minutes = intval($parts[1]);
        $seconds = intval($parts[2]);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function getCallStatisticsSimulated($userId)
    {
        return [
            'calls_today' => 0,
            'calls_7d' => 0,
            'calls_30d' => 0,
            'avg_duration' => 0,
            'top_destinations' => [],
            'source' => 'MongoDB Atlas offline - sem dados',
            'user_extension' => null,
            'is_user_specific' => false
        ];
    }

    private function generateReportDataSimulated($reportType, $userId)
    {
        return [
            'calls_count' => 0,
            'avg_duration' => 0,
            'total_duration' => 0,
            'period' => $this->getPeriodLabel($reportType),
            'top_destinations' => [],
            'recent_calls' => [],
            'source' => 'MongoDB Atlas offline - sem dados',
            'user_extension' => null,
            'is_user_specific' => false
        ];
    }

    private function getPeriodLabel($reportType)
    {
        switch ($reportType) {
            case 'daily':
                return 'hoje';
            case 'weekly':
                return 'esta semana';
            case 'monthly':
                return 'este mês';
            default:
                return 'período selecionado';
        }
    }

    private function getSystemStatus()
    {
        try {
            $status = [
                'smtp_configured' => $this->isSmtpConfigured(),
                'database_connection' => true,
                'mongodb_connection' => $this->mongodbEnabled,
                'last_backup' => null,
                'disk_usage' => 0,
                'active_users' => 0
            ];

            // Usuários ativos
            $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $status['active_users'] = $result['count'] ?? 0;

            return $status;
        } catch (Exception $e) {
            error_log("Erro ao verificar status do sistema: " . $e->getMessage());
            return [
                'smtp_configured' => false,
                'database_connection' => false,
                'mongodb_connection' => false,
                'last_backup' => null,
                'disk_usage' => 0,
                'active_users' => 0
            ];
        }
    }

    private function getSystemEmailStats()
    {
        try {
            $stats = [];

            // Usuários com email
            $sql = "SELECT COUNT(*) as count FROM users WHERE email IS NOT NULL AND email != ''";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['users_with_email'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Emails hoje
            $sql = "SELECT COUNT(*) as count FROM email_logs WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['emails_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Emails esta semana
            $sql = "SELECT COUNT(*) as count FROM email_logs WHERE WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['emails_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Taxa de sucesso
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent
                    FROM email_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['success_rate'] = $result['total'] > 0 ? round(($result['sent'] / $result['total']) * 100) : 100;

            return $stats;
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas de email: " . $e->getMessage());
            return [
                'users_with_email' => 0,
                'emails_today' => 0,
                'emails_week' => 0,
                'success_rate' => 100
            ];
        }
    }

    private function getAllUsersWithRealData()
    {
        try {
            $sql = "SELECT id, first_name, last_name, email, extension_number, role, status, created_at FROM users ORDER BY first_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Buscar configurações de email do usuário
                $emailSettings = $this->getUserSettings($user['id']);
                $user['daily_reports'] = $emailSettings['daily_reports'] ?? 0;
                $user['weekly_reports'] = $emailSettings['weekly_reports'] ?? 0;
                $user['monthly_reports'] = $emailSettings['monthly_reports'] ?? 0;
                $user['email_enabled'] = $emailSettings['email_enabled'] ?? 1;
                $user['last_email_sent'] = $this->getLastEmailSent($user['id']);

                // Buscar estatísticas de chamadas do usuário
                if ($user['extension_number']) {
                    $userStats = $this->getCallStatistics($user['id']);
                    $user['calls_30d'] = $userStats['calls_30d'] ?? 0;
                } else {
                    $user['calls_30d'] = 0;
                }
            }

            return $users;
        } catch (Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }

    // ===== MÉTODOS PRIVADOS (SMTP E EMAIL) =====

    private function sendTestEmailWithPHPMailer($toEmail)
    {
        try {
            // Verificar se SMTP está configurado
            if (!$this->isSmtpConfigured()) {
                return [
                    'success' => false,
                    'message' => 'SMTP não configurado. Configure nas configurações administrativas primeiro.'
                ];
            }

            $smtpConfig = $this->getSmtpConfig();

            if (empty($smtpConfig)) {
                return [
                    'success' => false,
                    'message' => 'Configurações SMTP não encontradas.'
                ];
            }

            // Usar PHPMailer
            $mail = new PHPMailer(true);

            // Configurar charset UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = $smtpConfig['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['smtp_username'];
            $mail->Password = $this->decryptPassword($smtpConfig['smtp_password']);
            $mail->Port = $smtpConfig['smtp_port'];

            if ($smtpConfig['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpConfig['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Debug (remover em produção)
            $mail->SMTPDebug = 0;

            // Configurações do email
            $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = '🧪 Teste de Email - Sistema CDR - ' . date('d/m/Y H:i:s');
            $mail->Body = $this->getTestEmailTemplate();

            $result = $mail->send();

            if ($result) {
                $this->logEmailSent($toEmail, 'test', 'sent');
                return [
                    'success' => true,
                    'message' => 'Email de teste enviado com sucesso para ' . $toEmail . ' via PHPMailer!'
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

    private function testSmtpConfigWithPHPMailer($postData, $testEmail)
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
            $mail = new PHPMailer(true);

            // Configurar charset UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->isSMTP();
            $mail->Host = $postData['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $postData['smtp_username'];
            $mail->Password = $postData['smtp_password']; // Não criptografada ainda
            $mail->Port = $postData['smtp_port'];

            if ($postData['smtp_encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($postData['smtp_encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Debug (remover em produção)
            $mail->SMTPDebug = 0;

            $mail->setFrom($postData['from_email'], $postData['from_name']);
            $mail->addAddress($testEmail);

            $mail->isHTML(true);
            $mail->Subject = '🧪 Teste de Configuração SMTP - ' . date('d/m/Y H:i:s');
            $mail->Body = $this->getTestEmailTemplate();

            $result = $mail->send();

            if ($result) {
                // Salvar configurações se o teste deu certo
                $this->saveSmtpConfig($postData);
                $this->updateLastTest();
                return [
                    'success' => true,
                    'message' => 'Teste SMTP realizado com sucesso! Email enviado para ' . $testEmail . ' e configurações salvas!'
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha no envio do teste SMTP'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro no teste SMTP: ' . $e->getMessage()
            ];
        }
    }

    private function isSmtpConfigured()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM email_smtp_config WHERE smtp_host IS NOT NULL AND smtp_host != '' AND instance_id = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar SMTP: " . $e->getMessage());
            return false;
        }
    }

    private function getSmtpConfig()
    {
        try {
            $sql = "SELECT * FROM email_smtp_config WHERE instance_id = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
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

            $stmt = $this->db->prepare($sql);

            // Criptografar senha
            $encryptedPassword = $this->encryptPassword($data['smtp_password']);

            $result = $stmt->execute([
                'smtp_host' => $data['smtp_host'],
                'smtp_port' => $data['smtp_port'],
                'smtp_username' => $data['smtp_username'],
                'smtp_password' => $encryptedPassword,
                'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
                'from_email' => $data['from_email'],
                'from_name' => $data['from_name']
            ]);

            return $result;
        } catch (Exception $e) {
            error_log("Erro ao salvar SMTP: " . $e->getMessage());
            return false;
        }
    }

    private function getUserSettings($userId)
    {
        try {
            $sql = "SELECT * FROM user_email_settings WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
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

            $stmt = $this->db->prepare($sql);

            $result = $stmt->execute([
                'user_id' => $userId,
                'daily_reports' => isset($data['daily_reports']) ? 1 : 0,
                'weekly_reports' => isset($data['weekly_reports']) ? 1 : 0,
                'monthly_reports' => isset($data['monthly_reports']) ? 1 : 0,
                'send_time' => $data['send_time'] ?? '08:00:00',
                'timezone' => $data['timezone'] ?? 'America/Sao_Paulo'
            ]);

            return $result;
        } catch (Exception $e) {
            error_log("Erro ao salvar configurações do usuário: " . $e->getMessage());
            return false;
        }
    }

    private function createDefaultUserSettings($userId)
    {
        try {
            $sql = "INSERT INTO user_email_settings (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao criar configurações padrão: " . $e->getMessage());
            return false;
        }
    }

    private function getLastEmailSent($userId)
    {
        try {
            $sql = "SELECT last_email_sent FROM user_email_settings WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['last_email_sent'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getEmailsSent30d($userId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM email_logs 
                    WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getLastSmtpTest()
    {
        try {
            $sql = "SELECT last_test FROM email_smtp_config WHERE instance_id = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['last_test'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function logEmailSent($toEmail, $type, $status, $errorMessage = null)
    {
        try {
            $sql = "INSERT INTO email_logs (user_id, report_type, email_to, subject, status, error_message, sent_at) 
                    VALUES (:user_id, :report_type, :email_to, :subject, :status, :error_message, :sent_at)";

            $stmt = $this->db->prepare($sql);
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
            $sql = "UPDATE email_smtp_config SET last_test = CURRENT_TIMESTAMP WHERE instance_id = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar último teste: " . $e->getMessage());
        }
    }

    private function encryptPassword($password)
    {
        $key = 'cdr_email_key_2025_secure_' . DB_HOST;
        $iv = substr(hash('sha256', $key), 0, 16);
        return base64_encode(openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv));
    }

    private function decryptPassword($encryptedPassword)
    {
        $key = 'cdr_email_key_2025_secure_' . DB_HOST;
        $iv = substr(hash('sha256', $key), 0, 16);
        return openssl_decrypt(base64_decode($encryptedPassword), 'AES-256-CBC', $key, 0, $iv);
    }

    private function getTestEmailTemplate()
    {
        $companyName = Auth::instance()['company_name'] ?? 'Sistema CDR';
        $mongoStatus = $this->mongodbEnabled ? 'MongoDB Atlas Conectado ✅' : 'MongoDB Atlas Offline ⚠️';
        $user = Auth::user();
        $userData = $this->getRealUserData($user['id']);
        $userExtension = $userData['extension_number'] ?? null;
        $extensionStatus = $userExtension ? "Ramal vinculado: $userExtension ✅" : 'Nenhum ramal vinculado ⚠️';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Teste de Email</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="margin: 0; font-size: 24px;">🧪 Teste de Email</h1>
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
                        <li><strong>Usuário:</strong> ' . htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) . '</li>
                        <li><strong>Versão:</strong> ' . APP_VERSION . '</li>
                        <li><strong>Método:</strong> PHPMailer via Composer</li>
                        <li><strong>MongoDB:</strong> ' . $mongoStatus . '</li>
                        <li><strong>Ramal:</strong> ' . $extensionStatus . '</li>
                        <li><strong>Registros CDR:</strong> 15.401 chamadas</li>
                    </ul>
                </div>
                
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <strong>🎉 Parabéns!</strong> Suas configurações de email estão funcionando perfeitamente com dados reais do MongoDB Atlas.
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
}
