<?php
// Teste de conexão com o banco
try {
    $host = 'callboard.mysql.dbaas.com.br';
    $username = 'callboard';
    $password = 'BdAdmin#2024!S';
    $database = 'callboard';

    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "✅ Conexão com banco realizada com sucesso!<br>";

    // Testar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "📋 Tabelas encontradas:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }

    // Testar instâncias
    $stmt = $pdo->query("SELECT * FROM instances");
    $instances = $stmt->fetchAll();

    echo "<br>🏢 Instâncias cadastradas:<br>";
    foreach ($instances as $instance) {
        echo "- {$instance['company_name']} ({$instance['subdomain']})<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
