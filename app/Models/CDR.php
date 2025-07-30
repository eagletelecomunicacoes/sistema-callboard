<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class CDR
{
    private $mongodb;
    private $collection;
    private $mongodbEnabled;

    public function __construct()
    {
        try {
            // USAR A MESMA CONEXÃO DOS RELATÓRIOS
            $this->mongodbEnabled = $this->initMongoDBAtlas();
        } catch (Exception $e) {
            error_log("Erro ao conectar MongoDB: " . $e->getMessage());
            $this->mongodb = null;
            $this->collection = null;
            $this->mongodbEnabled = false;
        }
    }

    /**
     * Inicializar MongoDB Atlas (mesmo método dos relatórios)
     */
    private function initMongoDBAtlas()
    {
        try {
            $uri = 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0';

            $this->mongodb = new \MongoDB\Client($uri, [], [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ]);

            // Testar conexão
            $this->mongodb->selectDatabase('admin')->command(['ping' => 1]);
            
            // USAR O MESMO DATABASE DOS RELATÓRIOS
            $this->mongodb = $this->mongodb->selectDatabase('cdrs');
            $this->collection = $this->mongodb->selectCollection('mdi'); // USAR A COLEÇÃO 'mdi'

            error_log("MongoDB Atlas conectado com sucesso! Database: cdrs, Collection: mdi");
            return true;

        } catch (Exception $e) {
            error_log("Erro MongoDB Atlas: " . $e->getMessage());
            $this->mongodb = null;
            $this->collection = null;
            return false;
        }
    }

    /**
     * Verificar se está conectado
     */
    public function isConnected()
    {
        return $this->collection !== null && $this->mongodbEnabled;
    }

    /**
     * Aplicar filtros de data nas consultas
     */
    private function buildDateFilter($filters = [])
    {
        $dateFilter = [];

        if (!empty($filters['period_preset'])) {
            switch ($filters['period_preset']) {
                case 'today':
                    $today = date('Y/m/d');
                    $dateFilter['smdrtime'] = ['$regex' => "^{$today}"];
                    break;
                case 'yesterday':
                    $yesterday = date('Y/m/d', strtotime('-1 day'));
                    $dateFilter['smdrtime'] = ['$regex' => "^{$yesterday}"];
                    break;
                case 'last7days':
                    $dates = [];
                    for ($i = 0; $i < 7; $i++) {
                        $dates[] = date('Y/m/d', strtotime("-$i days"));
                    }
                    $dateFilter['smdrtime'] = ['$regex' => '^(' . implode('|', $dates) . ')'];
                    break;
                case 'last30days':
                    $dates = [];
                    for ($i = 0; $i < 30; $i++) {
                        $dates[] = date('Y/m/d', strtotime("-$i days"));
                    }
                    $dateFilter['smdrtime'] = ['$regex' => '^(' . implode('|', $dates) . ')'];
                    break;
                case 'thismonth':
                    $currentMonth = date('Y/m');
                    $dateFilter['smdrtime'] = ['$regex' => "^{$currentMonth}"];
                    break;
                case 'lastmonth':
                    $lastMonth = date('Y/m', strtotime('first day of last month'));
                    $dateFilter['smdrtime'] = ['$regex' => "^{$lastMonth}"];
                    break;
            }
        } elseif (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = str_replace('-', '/', $filters['start_date']);
            $endDate = str_replace('-', '/', $filters['end_date']);
            
            if ($startDate === $endDate) {
                $dateFilter['smdrtime'] = ['$regex' => "^{$startDate}"];
            } else {
                // Para range de datas, precisamos de uma lógica mais complexa
                $dateFilter['smdrtime'] = [
                    '$gte' => $startDate . ' 00:00:00',
                    '$lte' => $endDate . ' 23:59:59'
                ];
            }
        }

        return $dateFilter;
    }

    /**
     * ESTATÍSTICAS COM FILTROS E TRANSFERÊNCIAS
     */
    public function getStats($filters = [])
    {
        if (!$this->isConnected()) {
            return [
                'error' => 'MongoDB não conectado',
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
                'total_records' => 0,
                'transfer_calls' => 0
            ];
        }

        try {
            // Filtro de data
            $dateFilter = $this->buildDateFilter($filters);
            
            // Total de registros (incluindo transferências)
            $totalRecords = $this->collection->countDocuments($dateFilter);
            
            // Agregação para chamadas únicas (excluindo transferências)
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'callduration' => ['$first' => '$callduration'],
                        'smdrtime' => ['$first' => '$smdrtime'],
                        'p1device' => ['$first' => '$p1device'],
                        'caller' => ['$first' => '$caller']
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'total_calls' => ['$sum' => 1],
                        'answered_calls' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$gt' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ],
                        'unanswered_calls' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ],
                        'total_duration' => ['$sum' => '$duration_seconds'],
                        'avg_duration' => ['$avg' => '$duration_seconds']
                    ]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            if (empty($result)) {
                return [
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
                    'total_records' => $totalRecords,
                    'transfer_calls' => 0
                ];
            }

            $data = $result[0];
            $total = $data['total_calls'];
            $answered = $data['answered_calls'];
            $transferCalls = $totalRecords - $total; // Diferença = transferências

            return [
                'total_calls' => $total,
                'answered_calls' => $answered,
                'unanswered_calls' => $data['unanswered_calls'],
                'busy_calls' => 0,
                'failed_calls' => 0,
                'success_rate' => $total > 0 ? round(($answered / $total) * 100, 1) : 0,
                'avg_duration' => round(($data['avg_duration'] ?? 0) / 60, 1),
                'total_duration' => $data['total_duration'] ?? 0,
                'today_calls' => $this->getTodayCalls(),
                'week_calls' => $this->getWeekCalls(),
                'month_calls' => $this->getMonthCalls(),
                'peak_hour' => $this->getPeakHour(),
                'unique_extensions' => $this->getUniqueExtensions(),
                'total_records' => $totalRecords,
                'transfer_calls' => $transferCalls
            ];

        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'error' => $e->getMessage(),
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
                'total_records' => 0,
                'transfer_calls' => 0
            ];
        }
    }

    /**
     * ESTATÍSTICAS DE TRANSFERÊNCIAS
     */
    public function getTransferStats($filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            // Buscar registros de transferência (registros duplicados por callid)
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'count' => ['$sum' => 1],
                        'records' => ['$push' => '$$ROOT']
                    ]
                ],
                [
                    '$match' => [
                        'count' => ['$gt' => 1] // Mais de 1 registro = transferência
                    ]
                ],
                [
                    '$project' => [
                        'callid' => '$_id',
                        'transfer_count' => ['$subtract' => ['$count', 1]], // -1 porque o primeiro não é transferência
                        'total_records' => '$count',
                        'records' => 1
                    ]
                ],
                [
                    '$sort' => ['total_records' => -1]
                ],
                [
                    '$limit' => 100
                ]
            ];

            $transfers = $this->collection->aggregate($pipeline)->toArray();
            
            // Estatísticas gerais de transferência
            $transferSummary = [
                'total_transfers' => 0,
                'calls_with_transfers' => count($transfers),
                'avg_transfers_per_call' => 0,
                'max_transfers' => 0,
                'top_transferred_calls' => []
            ];

            if (!empty($transfers)) {
                $totalTransferCount = array_sum(array_column($transfers, 'transfer_count'));
                $transferSummary['total_transfers'] = $totalTransferCount;
                $transferSummary['avg_transfers_per_call'] = round($totalTransferCount / count($transfers), 1);
                $transferSummary['max_transfers'] = max(array_column($transfers, 'transfer_count'));
                $transferSummary['top_transferred_calls'] = array_slice($transfers, 0, 10);
            }

            return $transferSummary;

        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de transferência: " . $e->getMessage());
            return [];
        }
    }

    /**
     * CHAMADAS POR STATUS (baseado na duração)
     */
    public function getCallsByStatus($filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'callduration' => ['$first' => '$callduration']
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$addFields' => [
                        'status' => [
                            '$cond' => [
                                ['$gt' => ['$duration_seconds', 0]],
                                'ANSWERED',
                                'NO ANSWER'
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$status',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            
            if (empty($result)) {
                return [];
            }

            $total = array_sum(array_column($result, 'count'));

            $statusColors = [
                'ANSWERED' => '#28a745',
                'NO ANSWER' => '#dc3545'
            ];

            $statusNames = [
                'ANSWERED' => 'Atendidas',
                'NO ANSWER' => 'Não Atendidas'
            ];

            $data = [];
            foreach ($result as $item) {
                $status = $item['_id'];
                $data[] = [
                    'status' => $statusNames[$status] ?? $status,
                    'count' => $item['count'],
                    'percentage' => $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0,
                    'color' => $statusColors[$status] ?? '#6c757d'
                ];
            }

            return $data;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas por status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * CHAMADAS POR TIPO baseado na direção
     */
    public function getCallsByType($filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'direction' => ['$first' => '$direction'],
                        'isinternal' => ['$first' => '$isinternal']
                    ]
                ],
                [
                    '$addFields' => [
                        'call_type' => [
                            '$cond' => [
                                ['$eq' => ['$isinternal', '1']],
                                'Interna',
                                [
                                    '$cond' => [
                                        ['$eq' => ['$direction', 'I']],
                                        'Entrada',
                                        [
                                            '$cond' => [
                                                ['$eq' => ['$direction', 'O']],
                                                'Saída',
                                                'Externa'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$call_type',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            
            if (empty($result)) {
                return [];
            }

            $total = array_sum(array_column($result, 'count'));

            $typeColors = [
                'Interna' => '#28a745',
                'Entrada' => '#17a2b8',
                'Saída' => '#007bff',
                'Externa' => '#6c757d'
            ];

            $data = [];
            foreach ($result as $item) {
                $type = $item['_id'];
                $data[] = [
                    'type' => $type,
                    'count' => $item['count'],
                    'percentage' => $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0,
                    'color' => $typeColors[$type] ?? '#6c757d'
                ];
            }

            return $data;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas por tipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * DADOS HORÁRIOS REAIS
     */
    public function getHourlyData($filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            // Se não há filtro de data, usar hoje por padrão
            if (empty($dateFilter)) {
                $today = date('Y/m/d');
                $dateFilter['smdrtime'] = ['$regex' => "^{$today}"];
            }

            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$addFields' => [
                        'hour' => [
                            '$toInt' => [
                                '$substr' => ['$smdrtime', 11, 2]
                            ]
                        ],
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'hour' => '$hour',
                            'callid' => '$callid'
                        ],
                        'duration_seconds' => ['$first' => '$duration_seconds']
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.hour',
                        'total' => ['$sum' => 1],
                        'answered' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$gt' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ],
                        'unanswered' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$sort' => ['_id' => 1]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            // Criar array com todas as 24 horas
            $hourlyData = [];
            for ($i = 0; $i < 24; $i++) {
                $total = 0;
                $answered = 0;
                $unanswered = 0;

                foreach ($result as $item) {
                    if ($item['_id'] == $i) {
                        $total = $item['total'];
                        $answered = $item['answered'];
                        $unanswered = $item['unanswered'];
                        break;
                    }
                }

                $hourlyData[] = [
                    'hour' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
                    'total' => $total,
                    'answered' => $answered,
                    'unanswered' => $unanswered
                ];
            }

            return $hourlyData;

        } catch (Exception $e) {
            error_log("Erro ao obter dados por hora: " . $e->getMessage());
            return [];
        }
    }

    /**
     * DADOS DIÁRIOS REAIS
     */
    public function getDailyData($days = 7, $filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $data = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y/m/d', strtotime("-{$i} days"));
                
                $dayFilter = ['smdrtime' => ['$regex' => "^{$date}"]];
                
                // Aplicar filtros adicionais se existirem
                if (!empty($filters)) {
                    $additionalFilter = $this->buildDateFilter($filters);
                    if (!empty($additionalFilter) && isset($additionalFilter['smdrtime'])) {
                        // Se há filtro de data específico, verificar se este dia está incluído
                        $dayFilter = $additionalFilter;
                    }
                }

                $pipeline = [
                    ['$match' => $dayFilter],
                    [
                        '$addFields' => [
                            'duration_seconds' => [
                                '$let' => [
                                    'vars' => [
                                        'parts' => ['$split' => ['$callduration', ':']]
                                    ],
                                    'in' => [
                                        '$add' => [
                                            ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                            ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                            ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$callid',
                            'duration_seconds' => ['$first' => '$duration_seconds']
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => null,
                            'total' => ['$sum' => 1],
                            'answered' => [
                                '$sum' => [
                                    '$cond' => [
                                        ['$gt' => ['$duration_seconds', 0]],
                                        1,
                                        0
                                    ]
                                ]
                            ],
                            'unanswered' => [
                                '$sum' => [
                                    '$cond' => [
                                        ['$eq' => ['$duration_seconds', 0]],
                                        1,
                                        0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                $result = $this->collection->aggregate($pipeline)->toArray();

                $total = !empty($result) ? $result[0]['total'] : 0;
                $answered = !empty($result) ? $result[0]['answered'] : 0;
                $unanswered = !empty($result) ? $result[0]['unanswered'] : 0;

                $data[] = [
                    'date' => str_replace('/', '-', $date),
                    'day_name' => date('D', strtotime(str_replace('/', '-', $date))),
                    'total' => $total,
                    'answered' => $answered,
                    'unanswered' => $unanswered,
                    'success_rate' => $total > 0 ? round(($answered / $total) * 100, 1) : 0
                ];
            }

            return $data;

        } catch (Exception $e) {
            error_log("Erro ao obter dados diários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * TOP RAMAIS REAIS
     */
    public function getTopExtensions($limit = 10, $filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            $pipeline = [
                ['$match' => array_merge($dateFilter, [
                    'p1device' => ['$regex' => '^[A-Z][0-9]{4}$']
                ])],
                [
                    '$addFields' => [
                        'extension' => ['$substr' => ['$p1device', 1, 4]],
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'extension' => '$extension',
                            'callid' => '$callid'
                        ],
                        'duration_seconds' => ['$first' => '$duration_seconds']
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.extension',
                        'total_calls' => ['$sum' => 1],
                        'answered_calls' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$gt' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ],
                        'total_duration' => ['$sum' => '$duration_seconds'],
                        'avg_duration' => ['$avg' => '$duration_seconds']
                    ]
                ],
                [
                    '$addFields' => [
                        'success_rate' => [
                            '$cond' => [
                                ['$gt' => ['$total_calls', 0]],
                                ['$multiply' => [['$divide' => ['$answered_calls', '$total_calls']], 100]],
                                0
                            ]
                        ]
                    ]
                ],
                [
                    '$sort' => ['total_calls' => -1]
                ],
                [
                    '$limit' => $limit
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            $extensions = [];
            foreach ($result as $item) {
                $extensions[] = [
                    'extension' => $item['_id'],
                    'total_calls' => $item['total_calls'],
                    'answered_calls' => $item['answered_calls'],
                    'success_rate' => round($item['success_rate'], 1),
                    'avg_duration' => round(($item['avg_duration'] ?? 0) / 60, 1),
                    'total_duration' => $item['total_duration'] ?? 0
                ];
            }

            return $extensions;

        } catch (Exception $e) {
            error_log("Erro ao obter top ramais: " . $e->getMessage());
            return [];
        }
    }

    /**
     * TOP DESTINOS REAIS
     */
    public function getTopDestinations($limit = 10, $filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => [
                            'callednumber' => '$callednumber',
                            'callid' => '$callid'
                        ],
                        'duration_seconds' => [
                            '$first' => [
                                '$let' => [
                                    'vars' => [
                                        'parts' => ['$split' => ['$callduration', ':']]
                                    ],
                                    'in' => [
                                        '$add' => [
                                            ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                            ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                            ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.callednumber',
                        'total_calls' => ['$sum' => 1],
                        'answered_calls' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$gt' => ['$duration_seconds', 0]],
                                    1,
                                    0
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$addFields' => [
                        'success_rate' => [
                            '$cond' => [
                                ['$gt' => ['$total_calls', 0]],
                                ['$multiply' => [['$divide' => ['$answered_calls', '$total_calls']], 100]],
                                0
                            ]
                        ]
                    ]
                ],
                [
                    '$sort' => ['total_calls' => -1]
                ],
                [
                    '$limit' => $limit
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            $destinations = [];
            foreach ($result as $item) {
                $destinations[] = [
                    'destination' => $item['_id'],
                    'total_calls' => $item['total_calls'],
                    'answered_calls' => $item['answered_calls'],
                    'success_rate' => round($item['success_rate'], 1),
                    'type' => $this->classifyNumber($item['_id'])
                ];
            }

            return $destinations;

        } catch (Exception $e) {
            error_log("Erro ao obter top destinos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * CHAMADAS RECENTES REAIS (APENAS 10)
     */
    public function getRecentCalls($limit = 10, $filters = [])
    {
        if (!$this->isConnected()) return [];

        try {
            $dateFilter = $this->buildDateFilter($filters);
            
            $pipeline = [
                ['$match' => $dateFilter],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'caller' => ['$first' => '$caller'],
                        'callednumber' => ['$first' => '$callednumber'],
                        'callduration' => ['$first' => '$callduration'],
                        'callstart' => ['$first' => '$callstart'],
                        'smdrtime' => ['$first' => '$smdrtime'],
                        'p1device' => ['$first' => '$p1device'],
                        'direction' => ['$first' => '$direction'],
                        'isinternal' => ['$first' => '$isinternal']
                    ]
                ],
                [
                    '$sort' => ['smdrtime' => -1]
                ],
                [
                    '$limit' => $limit
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            $calls = [];
            foreach ($result as $item) {
                $duration = $this->convertDurationToSeconds($item['callduration'] ?? '00:00:00');
                $extension = $this->extractExtension($item['p1device'] ?? '');

                $calls[] = [
                    'callid' => $item['_id'],
                    'src' => $item['caller'],
                    'dst' => $item['callednumber'],
                    'extension' => $extension,
                    'duration' => $duration,
                    'duration_formatted' => $this->formatDuration($duration),
                    'calldate' => $item['callstart'] ?? $item['smdrtime'],
                    'calldate_formatted' => $this->formatDateTime($item['callstart'] ?? $item['smdrtime']),
                    'disposition' => $duration > 0 ? 'ANSWERED' : 'NO ANSWER',
                    'status_formatted' => $duration > 0 ? 'Atendida' : 'Não Atendida',
                    'call_type' => $this->getCallType($item)
                ];
            }

            return $calls;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas recentes: " . $e->getMessage());
            return [];
        }
    }

    // ========== MÉTODOS AUXILIARES ==========

    /**
     * Converter duração HH:MM:SS para segundos
     */
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

    /**
     * Extrair ramal do campo p1device
     */
    private function extractExtension($device)
    {
        if (preg_match('/^[A-Z]([0-9]{4})$/', $device, $matches)) {
            return $matches[1];
        }
        return $device;
    }

    /**
     * Determinar tipo de chamada baseado nos campos da coleção mdi
     */
    private function getCallType($call)
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

        return 'Externa';
    }

    /**
     * Classificar tipo de número
     */
    private function classifyNumber($number)
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

    /**
     * Formatar duração
     */
    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            return gmdate('i:s', $seconds);
        } else {
            return gmdate('H:i:s', $seconds);
        }
    }

    /**
     * Formatar data e hora
     */
    private function formatDateTime($datetime)
    {
        try {
            // Tentar diferentes formatos de data
            if (preg_match('/(\d{4}\/\d{2}\/\d{2})\s+(\d{2}:\d{2}:\d{2})/', $datetime, $matches)) {
                $date = str_replace('/', '-', $matches[1]);
                $time = $matches[2];
                return date('d/m/Y H:i:s', strtotime($date . ' ' . $time));
            }
            
            return date('d/m/Y H:i:s', strtotime($datetime));
        } catch (Exception $e) {
            return $datetime;
        }
    }

    // ========== MÉTODOS DE CONTAGEM POR PERÍODO ==========

    /**
     * Obter chamadas de hoje
     */
    public function getTodayCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $today = date('Y/m/d');

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$today}"]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas de hoje: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas de ontem
     */
    public function getYesterdayCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $yesterday = date('Y/m/d', strtotime('-1 day'));

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$yesterday}"]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas de ontem: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas da semana
     */
    public function getWeekCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            // Últimos 7 dias
            $dates = [];
            for ($i = 0; $i < 7; $i++) {
                $dates[] = date('Y/m/d', strtotime("-$i days"));
            }

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => '^(' . implode('|', $dates) . ')']
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas da semana: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas da semana passada
     */
    public function getLastWeekCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            // Semana passada (7-14 dias atrás)
            $dates = [];
            for ($i = 7; $i < 14; $i++) {
                $dates[] = date('Y/m/d', strtotime("-$i days"));
            }

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => '^(' . implode('|', $dates) . ')']
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas da semana passada: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas do mês
     */
    public function getMonthCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $currentMonth = date('Y/m');

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$currentMonth}"]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas do mês: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas do mês passado
     */
    public function getLastMonthCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $lastMonth = date('Y/m', strtotime('first day of last month'));

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$lastMonth}"]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas do mês passado: " . $e->getMessage());
            return 0;
        }
    }

    // ========== MÉTODOS DE CHAMADAS ATENDIDAS ==========

    /**
     * Obter chamadas atendidas de hoje
     */
    public function getTodayAnsweredCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $today = date('Y/m/d');

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$today}"]
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$match' => [
                        'duration_seconds' => ['$gt' => 0]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas atendidas de hoje: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter chamadas atendidas de ontem
     */
    public function getYesterdayAnsweredCalls()
    {
        if (!$this->isConnected()) return 0;

        try {
            $yesterday = date('Y/m/d', strtotime('-1 day'));

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$yesterday}"]
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$match' => [
                        'duration_seconds' => ['$gt' => 0]
                    ]
                ],
                [
                    '$group' => ['_id' => '$callid']
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter chamadas atendidas de ontem: " . $e->getMessage());
            return 0;
        }
    }

    // ========== MÉTODOS DE DURAÇÃO ==========

    /**
     * Obter duração total de hoje
     */
    public function getTodayDuration()
    {
        if (!$this->isConnected()) return 0;

        try {
            $today = date('Y/m/d');

            $pipeline = [
                [
                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$today}"]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'callduration' => ['$first' => '$callduration']
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'total_duration' => ['$sum' => '$duration_seconds']
                    ]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total_duration'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter duração de hoje: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter duração total de ontem
     */
    public function getYesterdayDuration()
    {
        if (!$this->isConnected()) return 0;

        try {
            $yesterday = date('Y/m/d', strtotime('-1 day'));

            $pipeline = [
                [
                                    '$match' => [
                        'smdrtime' => ['$regex' => "^{$yesterday}"]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$callid',
                        'callduration' => ['$first' => '$callduration']
                    ]
                ],
                [
                    '$addFields' => [
                        'duration_seconds' => [
                            '$let' => [
                                'vars' => [
                                    'parts' => ['$split' => ['$callduration', ':']]
                                ],
                                'in' => [
                                    '$add' => [
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 0]]], 3600]],
                                        ['$multiply' => [['$toInt' => ['$arrayElemAt' => ['$$parts', 1]]], 60]],
                                        ['$toInt' => ['$arrayElemAt' => ['$$parts', 2]]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'total_duration' => ['$sum' => '$duration_seconds']
                    ]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total_duration'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter duração de ontem: " . $e->getMessage());
            return 0;
        }
    }

    // ========== MÉTODOS AUXILIARES FINAIS ==========

    /**
     * Obter horário de pico
     */
    public function getPeakHour()
    {
        if (!$this->isConnected()) return '14:00';

        try {
            $pipeline = [
                [
                    '$addFields' => [
                        'hour' => [
                            '$toInt' => [
                                '$substr' => ['$smdrtime', 11, 2] // Extrair hora do formato "2025/01/20 14:30:00"
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'hour' => '$hour',
                            'callid' => '$callid'
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.hour',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ],
                [
                    '$limit' => 1
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();

            if (!empty($result)) {
                $hour = $result[0]['_id'];
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            }

            return '14:00';

        } catch (Exception $e) {
            error_log("Erro ao obter horário de pico: " . $e->getMessage());
            return '14:00';
        }
    }

    /**
     * Obter ramais únicos
     */
    public function getUniqueExtensions()
    {
        if (!$this->isConnected()) return 0;

        try {
            $pipeline = [
                [
                    '$match' => [
                        'p1device' => ['$regex' => '^[A-Z][0-9]{4}$']
                    ]
                ],
                [
                    '$addFields' => [
                        'extension' => ['$substr' => ['$p1device', 1, 4]]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$extension'
                    ]
                ],
                [
                    '$count' => 'total'
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0]['total'] : 0;

        } catch (Exception $e) {
            error_log("Erro ao obter ramais únicos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar total de documentos
     */
    public function getTotalDocuments()
    {
        if (!$this->isConnected()) return 0;

        try {
            return $this->collection->countDocuments();
        } catch (Exception $e) {
            error_log("Erro ao contar documentos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter documentos de exemplo
     */
    public function getSampleDocuments($limit = 5)
    {
        if (!$this->isConnected()) return [];

        try {
            return $this->collection->find([], ['limit' => $limit])->toArray();
        } catch (Exception $e) {
            error_log("Erro ao obter documentos de exemplo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Estatísticas de debug simples
     */
    public function getDebugStats()
    {
        if (!$this->isConnected()) return [];

        try {
            $pipeline = [
                [
                    '$group' => [
                        '_id' => null,
                        'total_docs' => ['$sum' => 1],
                        'unique_callids' => ['$addToSet' => '$callid'],
                        'directions' => ['$addToSet' => '$direction'],
                        'sample_dates' => ['$addToSet' => '$smdrtime'],
                        'sample_devices' => ['$addToSet' => '$p1device'],
                        'sample_callers' => ['$addToSet' => '$caller']
                    ]
                ],
                [
                    '$project' => [
                        'total_docs' => 1,
                        'unique_callids_count' => ['$size' => '$unique_callids'],
                        'directions' => 1,
                        'sample_dates' => ['$slice' => ['$sample_dates', 5]],
                        'sample_devices' => ['$slice' => ['$sample_devices', 5]],
                        'sample_callers' => ['$slice' => ['$sample_callers', 5]]
                    ]
                ]
            ];

            $result = $this->collection->aggregate($pipeline)->toArray();
            return !empty($result) ? $result[0] : [];

        } catch (Exception $e) {
            error_log("Erro ao obter debug stats: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}