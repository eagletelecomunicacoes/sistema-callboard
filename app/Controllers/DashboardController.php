<?php
class DashboardController
{
    public function index()
    {
        Auth::requireLogin();

        $user = Auth::user();
        $instance = Auth::instance();

        require_once __DIR__ . '/../Models/CDR.php';
        require_once __DIR__ . '/../Models/User.php';

        $cdrModel = new CDR();
        $userModel = new User();

        try {
            // APLICAR FILTROS DE DATA SE EXISTIREM
            $filters = $this->parseFilters($_GET);

            // DADOS REAIS DO MONGODB COM FILTROS
            $stats = $cdrModel->getStats($filters);
            $callsByStatus = $cdrModel->getCallsByStatus($filters);
            $callsByType = $cdrModel->getCallsByType($filters);
            $hourlyData = $cdrModel->getHourlyData($filters);
            $dailyData = $cdrModel->getDailyData(7, $filters);
            $topExtensions = $cdrModel->getTopExtensions(10, $filters);
            $topDestinations = $cdrModel->getTopDestinations(10, $filters);
            $recentCalls = $cdrModel->getRecentCalls(10, $filters); // APENAS 10

            // DADOS DE TRANSFERÊNCIAS
            $transferStats = $cdrModel->getTransferStats($filters);

            // Dados de usuários
            $totalUsers = $userModel->getTotalUsers();
            $onlineUsers = $userModel->getOnlineUsers();

            // DEFINIR VARIÁVEIS QUE ESTAVAM FALTANDO
            $topUsers = $topExtensions;
            $todayVsYesterday = $this->getTodayVsYesterday($cdrModel);
            $weekVsLastWeek = $this->getWeekVsLastWeek($cdrModel);
            $monthVsLastMonth = $this->getMonthVsLastMonth($cdrModel);

            // Garantir que $stats tenha todas as chaves necessárias
            $stats = array_merge([
                'total_calls' => 0,
                'answered_calls' => 0,
                'unanswered_calls' => 0,
                'busy_calls' => 0,
                'failed_calls' => 0,
                'success_rate' => 0,
                'avg_duration' => 0,
                'total_duration' => 0,
                'today_calls' => 0,
                'week_calls' => 0,
                'month_calls' => 0,
                'peak_hour' => '14:00',
                'unique_extensions' => 0,
                'weekly_growth' => 0,
                'total_records' => 0,
                'transfer_calls' => 0
            ], $stats);

            if (isset($weekVsLastWeek['growth']['calls'])) {
                $stats['weekly_growth'] = $weekVsLastWeek['growth']['calls'];
            }

            if (!isset($stats['unique_calls'])) {
                $stats['unique_calls'] = $stats['total_calls'];
            }
        } catch (Exception $e) {
            error_log("Erro ao carregar dashboard: " . $e->getMessage());
            $errorMessage = 'Erro ao carregar dados: ' . $e->getMessage();

            // DEFINIR VALORES PADRÃO PARA EVITAR ERROS
            $stats = [
                'total_calls' => 0,
                'unique_calls' => 0,
                'answered_calls' => 0,
                'unanswered_calls' => 0,
                'busy_calls' => 0,
                'failed_calls' => 0,
                'success_rate' => 0,
                'avg_duration' => 0,
                'total_duration' => 0,
                'today_calls' => 0,
                'week_calls' => 0,
                'month_calls' => 0,
                'peak_hour' => '14:00',
                'unique_extensions' => 0,
                'weekly_growth' => 0,
                'total_records' => 0,
                'transfer_calls' => 0
            ];

            $callsByStatus = [];
            $callsByType = [];
            $hourlyData = [];
            $dailyData = [];
            $topExtensions = [];
            $topDestinations = [];
            $recentCalls = [];
            $topUsers = [];
            $transferStats = [];
            $totalUsers = 0;
            $onlineUsers = 0;
            $filters = [];

            $todayVsYesterday = [
                'today' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'yesterday' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];

            $weekVsLastWeek = [
                'this_week' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'last_week' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];

            $monthVsLastMonth = [
                'this_month' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'last_month' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];
        }

        require_once __DIR__ . '/../Views/dashboard/index.php';
    }

    /**
     * Processar filtros de data
     */
    private function parseFilters($getData)
    {
        $filters = [];

        // Período
        if (!empty($getData['period_preset'])) {
            $filters['period_preset'] = $getData['period_preset'];
        } else {
            $filters['start_date'] = $getData['start_date'] ?? null;
            $filters['end_date'] = $getData['end_date'] ?? null;
        }

        return $filters;
    }

    // ... resto dos métodos permanecem iguais

    /**
     * Comparação hoje vs ontem
     */
    private function getTodayVsYesterday($cdrModel)
    {
        try {
            $today = $cdrModel->getTodayCalls();
            $yesterday = $cdrModel->getYesterdayCalls();

            $todayAnswered = $cdrModel->getTodayAnsweredCalls();
            $yesterdayAnswered = $cdrModel->getYesterdayAnsweredCalls();

            $todayDuration = $cdrModel->getTodayDuration();
            $yesterdayDuration = $cdrModel->getYesterdayDuration();

            return [
                'today' => [
                    'calls' => $today,
                    'duration' => $todayDuration,
                    'answered' => $todayAnswered
                ],
                'yesterday' => [
                    'calls' => $yesterday,
                    'duration' => $yesterdayDuration,
                    'answered' => $yesterdayAnswered
                ],
                'growth' => [
                    'calls' => $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0,
                    'duration' => $yesterdayDuration > 0 ? round((($todayDuration - $yesterdayDuration) / $yesterdayDuration) * 100, 1) : 0,
                    'answered' => $yesterdayAnswered > 0 ? round((($todayAnswered - $yesterdayAnswered) / $yesterdayAnswered) * 100, 1) : 0
                ]
            ];
        } catch (Exception $e) {
            return [
                'today' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'yesterday' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];
        }
    }

    /**
     * Comparação semana vs semana anterior
     */
    private function getWeekVsLastWeek($cdrModel)
    {
        try {
            $thisWeek = $cdrModel->getWeekCalls();
            $lastWeek = $cdrModel->getLastWeekCalls();

            return [
                'this_week' => [
                    'calls' => $thisWeek,
                    'duration' => 0,
                    'answered' => 0
                ],
                'last_week' => [
                    'calls' => $lastWeek,
                    'duration' => 0,
                    'answered' => 0
                ],
                'growth' => [
                    'calls' => $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1) : 0,
                    'duration' => 0,
                    'answered' => 0
                ]
            ];
        } catch (Exception $e) {
            return [
                'this_week' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'last_week' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];
        }
    }

    /**
     * Comparação mês vs mês anterior
     */
    private function getMonthVsLastMonth($cdrModel)
    {
        try {
            $thisMonth = $cdrModel->getMonthCalls();
            $lastMonth = $cdrModel->getLastMonthCalls();

            return [
                'this_month' => [
                    'calls' => $thisMonth,
                    'duration' => 0,
                    'answered' => 0
                ],
                'last_month' => [
                    'calls' => $lastMonth,
                    'duration' => 0,
                    'answered' => 0
                ],
                'growth' => [
                    'calls' => $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0,
                    'duration' => 0,
                    'answered' => 0
                ]
            ];
        } catch (Exception $e) {
            return [
                'this_month' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'last_month' => ['calls' => 0, 'duration' => 0, 'answered' => 0],
                'growth' => ['calls' => 0, 'duration' => 0, 'answered' => 0]
            ];
        }
    }

    public function getData()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../Models/CDR.php';
            $cdrModel = new CDR();

            $type = $_GET['type'] ?? 'stats';

            switch ($type) {
                case 'stats':
                    $data = $cdrModel->getStats();
                    break;
                case 'hourly':
                    $data = $cdrModel->getHourlyData();
                    break;
                case 'daily':
                    $days = intval($_GET['days'] ?? 7);
                    $data = $cdrModel->getDailyData($days);
                    break;
                case 'status':
                    $data = $cdrModel->getCallsByStatus();
                    break;
                case 'types':
                    $data = $cdrModel->getCallsByType();
                    break;
                case 'extensions':
                    $limit = intval($_GET['limit'] ?? 10);
                    $data = $cdrModel->getTopExtensions($limit);
                    break;
                case 'destinations':
                    $limit = intval($_GET['limit'] ?? 10);
                    $data = $cdrModel->getTopDestinations($limit);
                    break;
                case 'recent':
                    $limit = intval($_GET['limit'] ?? 10);
                    $data = $cdrModel->getRecentCalls($limit);
                    break;
                default:
                    throw new Exception('Tipo de dados inválido');
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
