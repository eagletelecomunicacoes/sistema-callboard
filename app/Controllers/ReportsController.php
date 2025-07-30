<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/ReportsModel.php';

use MongoDB\Client;

class ReportsController
{
    private $db;
    private $mongodb;
    private $mongodbEnabled;
    private $reportsModel;

    public function __construct()
    {
        try {
            // Conexão MySQL
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=cdr_miriandayrell", DB_USERNAME, DB_PASSWORD);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Conectar MongoDB Atlas
            $this->mongodbEnabled = $this->initMongoDBAtlas();

            // Inicializar Model
            $this->reportsModel = new ReportsModel($this->db, $this->mongodb, $this->mongodbEnabled);
        } catch (Exception $e) {
            error_log("Erro de conexão ReportsController: " . $e->getMessage());
            $this->mongodbEnabled = false;
            $this->reportsModel = new ReportsModel($this->db, null, false);
        }
    }

    private function initMongoDBAtlas()
    {
        try {
            $uri = 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0';

            $this->mongodb = new Client($uri, [], [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ]);

            $this->mongodb->selectDatabase('admin')->command(['ping' => 1]);
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

        // Buscar dados para filtros
        $departments = $this->reportsModel->getDepartments();
        $extensions = $this->reportsModel->getExtensions();

        // Dados para a view
        $data = [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'departments' => $departments,
            'extensions' => $extensions,
            'mongodbEnabled' => $this->mongodbEnabled,
            'pageTitle' => 'Relatórios CDR',
            'currentPage' => 'reports'
        ];

        $this->view('reports/index', $data);
    }

    public function generateReport()
    {
        // Verificar se é requisição AJAX
        if (
            !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Requisição inválida']);
            exit;
        }

        try {
            // Obter filtros do POST
            $filters = $this->parseFilters($_POST);

            // Gerar relatório usando o Model
            $reportData = $this->reportsModel->generateReport($filters);

            // Retornar JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $reportData
            ]);
        } catch (Exception $e) {
            error_log("Erro ao gerar relatório: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function exportReport()
    {
        try {
            $format = $_POST['format'] ?? 'csv';
            $filters = $this->parseFilters($_POST);

            // Gerar dados do relatório
            $reportData = $this->reportsModel->generateReport($filters);

            if ($format === 'excel') {
                $this->exportToExcel($reportData);
            } else {
                $this->exportToCSV($reportData);
            }
        } catch (Exception $e) {
            error_log("Erro ao exportar relatório: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao exportar relatório']);
        }
    }

    public function getCallDetails()
    {
        try {
            $callId = $_GET['call_id'] ?? '';

            if (empty($callId)) {
                throw new Exception('Call ID não fornecido');
            }

            $callDetails = $this->reportsModel->getCallDetails($callId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $callDetails
            ]);
        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da chamada: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function parseFilters($postData)
    {
        $filters = [];

        // Período
        if (!empty($postData['period_preset'])) {
            $filters['period_preset'] = $postData['period_preset'];
        } else {
            $filters['start_date'] = $postData['start_date'] ?? null;
            $filters['end_date'] = $postData['end_date'] ?? null;
        }

        $filters['start_time'] = $postData['start_time'] ?? null;
        $filters['end_time'] = $postData['end_time'] ?? null;

        // Ramais
        $filters['origin_extension'] = $postData['origin_extension'] ?? [];
        $filters['destination_number'] = $postData['destination_number'] ?? '';

        // Departamentos
        $filters['department'] = $postData['department'] ?? [];

        // Tipos de chamada
        $filters['call_type'] = $postData['call_type'] ?? [];

        // Duração
        $filters['min_duration'] = $postData['min_duration'] ?? null;
        $filters['max_duration'] = $postData['max_duration'] ?? null;

        // Status
        $filters['call_status'] = $postData['call_status'] ?? [];

        // Classificação do destino
        $filters['destination_type'] = $postData['destination_type'] ?? [];

        // Paginação
        $filters['page'] = intval($postData['page'] ?? 1);
        $filters['per_page'] = intval($postData['per_page'] ?? 50);

        // Ordenação
        $filters['sort_by'] = $postData['sort_by'] ?? 'callstart';
        $filters['sort_order'] = $postData['sort_order'] ?? 'desc';

        return $filters;
    }

    private function exportToCSV($reportData)
    {
        $filename = 'relatorio-cdr-' . date('Y-m-d-H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalhos
        $headers = [
            'Data/Hora Início',
            'Número Origem',
            'Número Destino',
            'Duração',
            'Ramal Origem',
            'Usuário/Departamento',
            'Tipo da Chamada',
            'Status da Chamada',
            'Call ID'
        ];
        fputcsv($output, $headers, ';');

        // Dados
        foreach ($reportData['calls'] as $call) {
            $row = [
                $call['callstart_formatted'],
                $call['caller'],
                $call['callednumber'],
                $call['duration_formatted'],
                $call['extension'],
                $call['user_name'],
                $call['call_type'],
                $call['call_status'],
                $call['callid']
            ];
            fputcsv($output, $row, ';');
        }

        fclose($output);
    }

    private function exportToExcel($reportData)
    {
        // Por simplicidade, usar CSV por enquanto
        $this->exportToCSV($reportData);
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
            echo "<p><a href='" . APP_URL . "/dashboard'>← Voltar ao Dashboard</a></p>";
        }
    }
}
