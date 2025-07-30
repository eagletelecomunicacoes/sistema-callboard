<?php
$pageTitle = 'Relatórios';
$currentPage = 'reports';
require_once '../app/Views/layouts/header.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        ❌ <?php echo htmlspecialchars($error); ?>
    </div>
<?php else: ?>

    <!-- Filtros Avançados -->
    <div class="card">
        <div class="card-header">
            <h2>🔍 Filtros de Relatório</h2>
        </div>
        <div class="card-body">
            <form method="GET" action="/reports">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>📅 Data Início:</label>
                        <input type="date" name="date_start" value="<?php echo htmlspecialchars($data['filters']['date_start'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <label>📅 Data Fim:</label>
                        <input type="date" name="date_end" value="<?php echo htmlspecialchars($data['filters']['date_end'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <label>👤 Usuário:</label>
                        <input type="text" name="user" value="<?php echo htmlspecialchars($data['filters']['user'] ?? ''); ?>" placeholder="Nome do usuário">
                    </div>
                    <div class="filter-group">
                        <label>🔄 Direção:</label>
                        <select name="direction">
                            <option value="">Todas</option>
                            <option value="I" <?php echo ($data['filters']['direction'] ?? '') == 'I' ? 'selected' : ''; ?>>Recebidas</option>
                            <option value="O" <?php echo ($data['filters']['direction'] ?? '') == 'O' ? 'selected' : ''; ?>>Realizadas</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>📊 Limite:</label>
                        <select name="limit">
                            <option value="50" <?php echo ($data['filters']['limit'] ?? 100) == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo ($data['filters']['limit'] ?? 100) == 100 ? 'selected' : ''; ?>>100</option>
                            <option value="500" <?php echo ($data['filters']['limit'] ?? 100) == 500 ? 'selected' : ''; ?>>500</option>
                            <option value="1000" <?php echo ($data['filters']['limit'] ?? 100) == 1000 ? 'selected' : ''; ?>>1000</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                        <a href="/reports" class="btn btn-secondary">🔄 Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estatísticas do Período -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($data['stats']['total'] ?? 0, 0, ',', '.'); ?></span>
                <div class="stat-label">Total no Período</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📞</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($data['stats']['inbound'] ?? 0, 0, ',', '.'); ?></span>
                <div class="stat-label">Chamadas Recebidas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📱</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($data['stats']['outbound'] ?? 0, 0, ',', '.'); ?></span>
                <div class="stat-label">Chamadas Realizadas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <span class="stat-number"><?php
                                            $total = $data['stats']['total'] ?? 0;
                                            $inbound = $data['stats']['inbound'] ?? 0;
                                            echo $total > 0 ? number_format(($inbound / $total) * 100, 1) : 0;
                                            ?>%</span>
                <div class="stat-label">% Recebidas</div>
            </div>
        </div>
    </div>

    <!-- Ações de Exportação -->
    <div class="card">
        <div class="card-header">
            <h2>📤 Exportar Dados</h2>
        </div>
        <div class="card-body">
            <div class="export-actions">
                <button class="btn btn-success" onclick="exportData('csv')">📊 Exportar CSV</button>
                <button class="btn btn-info" onclick="exportData('json')">📄 Exportar JSON</button>
                <button class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir</button>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Resultados
                (<?php echo number_format(count($data['calls'] ?? []), 0, ',', '.'); ?> de
                <?php echo number_format($data['pagination']['total_records'] ?? 0, 0, ',', '.'); ?>)
            </h2>
        </div>
        <div class="card-body">
            <?php if (!empty($data['calls'])): ?>
                <div class="table-responsive">
                    <table class="table" id="reportsTable">
                        <thead>
                            <tr>
                                <th>🆔 Call ID</th>
                                <th>📅 Data/Hora</th>
                                <th>👤 Usuário</th>
                                <th>📞 Origem</th>
                                <th>📱 Destino</th>
                                <th>⏱️ Duração</th>
                                <th>🔄 Direção</th>
                                <th>📊 Registros</th>
                                <th>🔍 Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['calls'] as $call): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($call->callid ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($call->smdrtime ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($call->p1name ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($call->caller ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($call->callednumber ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($call->callduration ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $direction = $call->direction ?? '';
                                        $badge = $direction == 'I' ? 'badge-success' : ($direction == 'O' ? 'badge-danger' : 'badge-secondary');
                                        $text = $direction == 'I' ? '📞 Recebida' : ($direction == 'O' ? '📱 Realizada' : '🔄 Interna');
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo $text; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $call->total_records ?? 1; ?></span>
                                    </td>
                                    <td>
                                        <a href="/reports/details?call_id=<?php echo urlencode($call->callid ?? ''); ?>"
                                            class="btn btn-sm btn-info">
                                            🔍 Detalhes
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($data['pagination']['total_pages'] > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php
                            $currentPage = $data['pagination']['current_page'];
                            $totalPages = $data['pagination']['total_pages'];
                            $filters = $data['filters'];

                            // Monta query string para manter filtros
                            $queryString = http_build_query($filters);
                            ?>

                            <?php if ($currentPage > 1): ?>
                                <a href="/reports?<?php echo $queryString; ?>&page=<?php echo $currentPage - 1; ?>" class="btn btn-sm btn-secondary">
                                    ← Anterior
                                </a>
                            <?php endif; ?>

                            <span class="pagination-info">
                                Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                            </span>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="/reports?<?php echo $queryString; ?>&page=<?php echo $currentPage + 1; ?>" class="btn btn-sm btn-secondary">
                                    Próxima →
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    ℹ️ Nenhum registro encontrado com os filtros aplicados.
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<script>
    function exportData(format) {
        const filters = <?php echo json_encode($data['filters'] ?? []); ?>;

        const formData = new FormData();
        formData.append('format', format);
        formData.append('filters', JSON.stringify(filters));

        fetch('/reports/export', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (format === 'csv') {
                    return response.blob();
                } else {
                    return response.json();
                }
            })
            .then(data => {
                if (format === 'csv') {
                    const url = window.URL.createObjectURL(data);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'relatorio_cdr_' + new Date().toISOString().split('T')[0] + '.csv';
                    a.click();
                } else {
                    const blob = new Blob([JSON.stringify(data, null, 2)], {
                        type: 'application/json'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'relatorio_cdr_' + new Date().toISOString().split('T')[0] + '.json';
                    a.click();
                }
            })
            .catch(error => {
                alert('Erro ao exportar: ' + error.message);
            });
    }
</script>

<?php require_once '../app/Views/layouts/footer.php'; ?>