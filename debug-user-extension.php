<?php
require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

// TESTE COM O RAMAL QUE VOCÊ CADASTROU
$testUserExtension = '4554'; // MUDE PARA O RAMAL QUE VOCÊ CADASTROU

echo "🔍 DEBUG - Testando ramal específico: $testUserExtension\n\n";

try {
    $uri = 'mongodb+srv://eagletelecom:fN2wHwsLaaboIkwS@crcttec0.ziue1rs.mongodb.net/?retryWrites=true&w=majority&appName=CrctTec0';

    $client = new Client($uri, [], [
        'typeMap' => [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array'
        ]
    ]);

    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "✅ MongoDB conectado!\n\n";

    $database = $client->selectDatabase('cdrs');
    $collection = $database->selectCollection('mdi');

    // Testar busca por ramal específico
    echo "📞 Testando busca por ramal: $testUserExtension\n";

    // Buscar por qualquer canal + ramal
    $extensionFilter = [
        '$or' => [
            ['p1device' => ['$regex' => '^[A-Z]' . $testUserExtension . '$']],
            ['p2device' => ['$regex' => '^[A-Z]' . $testUserExtension . '$']]
        ]
    ];

    echo "�� Filtro usado: " . json_encode($extensionFilter) . "\n\n";

    $extensionRecords = $collection->countDocuments($extensionFilter);
    echo "📊 Total de registros encontrados: " . number_format($extensionRecords) . "\n\n";

    if ($extensionRecords > 0) {
        echo "📋 Exemplos de chamadas encontradas:\n";
        $extensionCalls = $collection->find($extensionFilter, ['limit' => 10])->toArray();

        foreach ($extensionCalls as $index => $call) {
            $device1 = $call['p1device'] ?? 'N/A';
            $device2 = $call['p2device'] ?? 'N/A';
            $caller = $call['caller'] ?? 'N/A';
            $date = $call['smdrtime'] ?? 'N/A';

            echo "  " . ($index + 1) . ". $caller → p1device: $device1, p2device: $device2 ($date)\n";
        }

        // Testar filtro com data de hoje
        echo "\n📅 Testando com data de hoje:\n";
        $today = date('Y/m/d');
        $todayFilter = [
            '$and' => [
                $extensionFilter,
                ['smdrtime' => ['$regex' => '^' . $today]]
            ]
        ];

        $todayRecords = $collection->countDocuments($todayFilter);
        echo "📊 Registros hoje ($today): " . number_format($todayRecords) . "\n";

        // Testar filtro com data de ontem
        $yesterday = date('Y/m/d', strtotime('-1 day'));
        $yesterdayFilter = [
            '$and' => [
                $extensionFilter,
                ['smdrtime' => ['$regex' => '^' . $yesterday]]
            ]
        ];

        $yesterdayRecords = $collection->countDocuments($yesterdayFilter);
        echo "📊 Registros ontem ($yesterday): " . number_format($yesterdayRecords) . "\n";
    } else {
        echo "❌ Nenhum registro encontrado para o ramal $testUserExtension\n\n";

        // Verificar quais ramais existem
        echo "📋 Verificando ramais disponíveis que terminam com $testUserExtension:\n";

        $availableFilter = [
            '$or' => [
                ['p1device' => ['$regex' => $testUserExtension . '$']],
                ['p2device' => ['$regex' => $testUserExtension . '$']]
            ]
        ];

        $availableRecords = $collection->find($availableFilter, ['limit' => 5])->toArray();

        if (count($availableRecords) > 0) {
            foreach ($availableRecords as $index => $record) {
                echo "  " . ($index + 1) . ". p1device: " . ($record['p1device'] ?? 'N/A') . ", p2device: " . ($record['p2device'] ?? 'N/A') . "\n";
            }
        } else {
            echo "  Nenhum ramal encontrado terminando com $testUserExtension\n";
        }
    }

    echo "\n🎉 Debug concluído!\n";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
