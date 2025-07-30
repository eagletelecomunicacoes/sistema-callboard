<?php

class ReportsModel
{
    private $db;
    private $mongodb;
    private $mongodbEnabled;

    public function __construct($db, $mongodb, $mongodbEnabled)
    {
        $this->db = $db;
        $this->mongodb = $mongodb;
        $this->mongodbEnabled = $mongodbEnabled;
    }

    public function getDepartments()
    {
        if (!$this->mongodbEnabled) {
            return ['Recepção', 'UTI', 'Enfermagem', 'Administração', 'Farmácia'];
        }

        try {
            $collection = $this->mongodb->selectCollection('mdi');
            $departments = $collection->distinct('p1name', [
                'p1name' => ['$ne' => null, '$ne' => '', '$exists' => true]
            ]);

            sort($departments);
            return array_slice($departments, 0, 20); // Limitar para performance
        } catch (Exception $e) {
            error_log("Erro ao buscar departamentos: " . $e->getMessage());
            return ['Recepção', 'UTI', 'Enfermagem', 'Administração', 'Farmácia'];
        }
    }

    public function getExtensions()
    {
        if (!$this->mongodbEnabled) {
            return ['4554', '4555', '4556', '4557', '4558'];
        }

        try {
            $collection = $this->mongodb->selectCollection('mdi');
            $pipeline = [
                ['$match' => ['p1device' => ['$regex' => '^[A-Z]\d{4}$']]],
                ['$project' => ['extension' => ['$substr' => ['$p1device', 1, 4]]]],
                ['$group' => ['_id' => '$extension']],
                ['$sort' => ['_id' => 1]],
                ['$limit' => 50] // Limitar para performance
            ];

            $extensions = $collection->aggregate($pipeline)->toArray();

            return array_map(function ($item) {
                return $item['_id'];
            }, $extensions);
        } catch (Exception $e) {
            error_log("Erro ao buscar ramais: " . $e->getMessage());
            return ['4554', '4555', '4556', '4557', '4558'];
        }
    }

    public function generateReport($filters)
    {
        if (!$this->mongodbEnabled) {
            return $this->generateDemoReport();
        }

        try {
            $collection = $this->mongodb->selectCollection('mdi');

            // Construir filtro MongoDB básico
            $mongoFilter = $this->buildBasicMongoFilter($filters);

            // Contar total de registros
            $totalRecords = $collection->countDocuments($mongoFilter);

            // Buscar dados com paginação
            $skip = ($filters['page'] - 1) * $filters['per_page'];
            $options = [
                'skip' => $skip,
                'limit' => $filters['per_page'],
                'sort' => ['smdrtime' => -1] // Ordenar por data mais recente
            ];

            $calls = $collection->find($mongoFilter, $options)->toArray();

            // Processar dados das chamadas
            $processedCalls = $this->processCalls($calls);

            // Calcular estatísticas básicas
            $statistics = $this->calculateBasicStatistics($calls);

            return [
                'calls' => $processedCalls,
                'statistics' => $statistics,
                'pagination' => [
                    'current_page' => $filters['page'],
                    'per_page' => $filters['per_page'],
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $filters['per_page'])
                ],
                'filters_applied' => $this->getAppliedFiltersDescription($filters)
            ];
        } catch (Exception $e) {
            error_log("Erro ao gerar relatório: " . $e->getMessage());
            return $this->generateDemoReport();
        }
    }

    private function buildBasicMongoFilter($filters)
    {
        $mongoFilter = [];

        // Filtro por período básico
        if (!empty($filters['period_preset'])) {
            switch ($filters['period_preset']) {
                case 'today':
                    $today = date('Y/m/d');
                    $mongoFilter['smdrtime'] = ['$regex' => '^' . $today];
                    break;
                case 'yesterday':
                    $yesterday = date('Y/m/d', strtotime('-1 day'));
                    $mongoFilter['smdrtime'] = ['$regex' => '^' . $yesterday];
                    break;
                case 'last7days':
                    // Buscar últimos 7 dias
                    $dates = [];
                    for ($i = 0; $i < 7; $i++) {
                        $dates[] = date('Y/m/d', strtotime("-$i days"));
                    }
                    $mongoFilter['smdrtime'] = ['$regex' => '^(' . implode('|', $dates) . ')'];
                    break;
            }
        }

        // Filtro por ramal de origem
        if (!empty($filters['origin_extension']) && is_array($filters['origin_extension'])) {
            $extensionRegex = [];
            foreach ($filters['origin_extension'] as $ext) {
                $extensionRegex[] = '^[A-Z]' . preg_quote($ext) . '$';
            }
            $mongoFilter['p1device'] = ['$regex' => '(' . implode('|', $extensionRegex) . ')'];
        }

        return $mongoFilter;
    }

    private function processCalls($calls)
    {
        $processedCalls = [];

        foreach ($calls as $call) {
            $processed = [
                'callid' => $call['callid'] ?? 'N/A',
                'callstart' => $call['callstart'] ?? 'N/A',
                'callstart_formatted' => $this->formatCallStart($call['callstart'] ?? ''),
                'caller' => $call['caller'] ?? 'N/A',
                'callednumber' => $call['callednumber'] ?? 'N/A',
                'callduration' => $call['callduration'] ?? '00:00:00',
                'duration_formatted' => $this->formatDuration($call['callduration'] ?? '00:00:00'),
                'duration_seconds' => $this->convertDurationToSeconds($call['callduration'] ?? '00:00:00'),
                'direction' => $call['direction'] ?? 'N/A',
                'extension' => $this->extractExtension($call['p1device'] ?? ''),
                'user_name' => $call['p1name'] ?? 'N/A',
                'call_type' => $this->determineCallType($call),
                'call_status' => $this->determineCallStatus($call),
                'destination_type' => $this->classifyDestination($call['callednumber'] ?? ''),
                'p1device' => $call['p1device'] ?? 'N/A',
                'p2device' => $call['p2device'] ?? 'N/A',
                'smdrtime' => $call['smdrtime'] ?? 'N/A'
            ];

            $processedCalls[] = $processed;
        }

        return $processedCalls;
    }

    private function formatCallStart($callstart)
    {
        // Formato: "08:33:58 755545320mS CDR: SMDR OUTPUT 2025/06/06 08:32:39"
        if (preg_match('/(\d{2}:\d{2}:\d{2}).*(\d{4}\/\d{2}\/\d{2})\s+(\d{2}:\d{2}:\d{2})/', $callstart, $matches)) {
            $date = $matches[2];
            $startTime = $matches[3];

            // Converter formato de data
            $formattedDate = date('d/m/Y', strtotime(str_replace('/', '-', $date)));

            return $formattedDate . ' ' . $startTime;
        }

        return $callstart;
    }

    private function formatDuration($duration)
    {
        if (empty($duration) || $duration === '00:00:00') {
            return '0s';
        }

        $parts = explode(':', $duration);
        if (count($parts) !== 3) {
            return $duration;
        }

        $hours = intval($parts[0]);
        $minutes = intval($parts[1]);
        $seconds = intval($parts[2]);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$seconds}s";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        } else {
            return "{$seconds}s";
        }
    }

    private function convertDurationToSeconds($duration)
    {
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

    private function extractExtension($device)
    {
        if (preg_match('/^[A-Z](\d{4})$/', $device, $matches)) {
            return $matches[1];
        }
        return $device;
    }

    private function determineCallType($call)
    {
        $direction = $call['direction'] ?? '';
        $isInternal = $call['isinternal'] ?? '0';

        if ($isInternal === '1') {
            return 'Interna';
        } elseif ($direction === 'I') {
            return 'Entrada';
        } elseif ($direction === 'O') {
            return 'Saída';
        }

        return 'Desconhecido';
    }

    private function determineCallStatus($call)
    {
        $duration = $this->convertDurationToSeconds($call['callduration'] ?? '00:00:00');

        if ($duration > 0) {
            return 'Atendida';
        } else {
            return 'Não Atendida';
        }
    }

    private function classifyDestination($number)
    {
        if (empty($number)) {
            return 'Desconhecido';
        }

        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        if (strlen($cleanNumber) >= 3 && strlen($cleanNumber) <= 5) {
            return 'Interno';
        }

        if (strlen($cleanNumber) === 11 && substr($cleanNumber, 2, 1) === '9') {
            return 'Celular';
        }

        if (strlen($cleanNumber) === 10) {
            return 'Fixo';
        }

        return 'Outros';
    }

    private function calculateBasicStatistics($calls)
    {
        $totalCalls = count($calls);
        $totalDuration = 0;
        $uniqueExtensions = [];

        foreach ($calls as $call) {
            $duration = $this->convertDurationToSeconds($call['callduration'] ?? '00:00:00');
            $totalDuration += $duration;

            $extension = $this->extractExtension($call['p1device'] ?? '');
            if (!empty($extension)) {
                $uniqueExtensions[$extension] = true;
            }
        }

        $avgDuration = $totalCalls > 0 ? round($totalDuration / $totalCalls) : 0;

        return [
            'total_calls' => $totalCalls,
            'total_duration' => $totalDuration,
            'total_duration_formatted' => $this->formatDurationFromSeconds($totalDuration),
            'avg_duration' => $avgDuration,
            'avg_duration_formatted' => $this->formatDurationFromSeconds($avgDuration),
            'unique_extensions' => count($uniqueExtensions)
        ];
    }

    private function formatDurationFromSeconds($seconds)
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . $remainingSeconds . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    private function getAppliedFiltersDescription($filters)
    {
        $descriptions = [];

        if (!empty($filters['period_preset'])) {
            $descriptions[] = 'Período: ' . $this->getPeriodDescription($filters['period_preset']);
        }

        if (!empty($filters['origin_extension'])) {
            $descriptions[] = 'Ramais: ' . implode(', ', $filters['origin_extension']);
        }

        return $descriptions;
    }

    private function getPeriodDescription($preset)
    {
        switch ($preset) {
            case 'today':
                return 'Hoje';
            case 'yesterday':
                return 'Ontem';
            case 'last7days':
                return 'Últimos 7 dias';
            case 'thismonth':
                return 'Este mês';
            case 'lastmonth':
                return 'Mês passado';
            case 'thisyear':
                return 'Este ano';
            default:
                return $preset;
        }
    }

    public function getCallDetails($callId)
    {
        if (!$this->mongodbEnabled) {
            throw new Exception('MongoDB não está disponível');
        }

        try {
            $collection = $this->mongodb->selectCollection('mdi');
            $call = $collection->findOne(['callid' => $callId]);

            if (!$call) {
                throw new Exception('Chamada não encontrada');
            }

            return $this->processCalls([$call])[0];
        } catch (Exception $e) {
            error_log("Erro ao buscar detalhes da chamada: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateDemoReport()
    {
        return [
            'calls' => [],
            'statistics' => [
                'total_calls' => 0,
                'total_duration' => 0,
                'total_duration_formatted' => '0s',
                'avg_duration' => 0,
                'avg_duration_formatted' => '0s',
                'unique_extensions' => 0
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 50,
                'total_records' => 0,
                'total_pages' => 0
            ],
            'filters_applied' => ['MongoDB offline - dados de demonstração']
        ];
    }
}
