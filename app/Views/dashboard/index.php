<?php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard CDR';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/topbar.php';

// Definir período padrão (ontem)
$defaultStartDate = date('Y-m-d', strtotime('-1 day'));
$defaultEndDate = date('Y-m-d', strtotime('-1 day'));
?>

<!-- CSS Modular do Dashboard -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard/base.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard/components.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard/modals.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard/responsive.css">

<div class="main-content">
    <div class="container-fluid">

        <!-- Header Moderno -->
        <div class="dashboard-header">
            <div class="header-content">
                <div class="header-text">
                    <h1 class="header-title">Central de Análise</h1>
                    <p class="header-subtitle">Visão geral das comunicações empresariais</p>
                </div>
                <div class="header-actions">
                    <button class="btn-modern btn-secondary" id="refreshDashboard">
                        <i class="fas fa-sync-alt"></i>
                        <span>Atualizar</span>
                    </button>
                    <a href="<?= APP_URL ?>/reports" class="btn-modern btn-primary">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Status do Sistema -->
        <?php if (isset($errorMessage)): ?>
            <div class="status-card status-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($errorMessage) ?></span>
            </div>
        <?php else: ?>
            <div class="status-card status-success">
                <i class="fas fa-check-circle"></i>
                <span>Sistema operacional - Dados atualizados</span>
                <small>Última atualização: <?= date('H:i') ?></small>
            </div>
        <?php endif; ?>

        <!-- Filtros Modernos Melhorados -->
        <div class="filter-section">
            <div class="filter-header">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    <h3>Filtros de Análise</h3>
                    <span class="filter-period-indicator" id="currentPeriodIndicator">
                        Período: <?= date('d/m/Y', strtotime($defaultStartDate)) ?>
                        <?= $defaultStartDate !== $defaultEndDate ? ' até ' . date('d/m/Y', strtotime($defaultEndDate)) : '' ?>
                    </span>
                </div>
                <div class="filter-actions">
                    <button class="filter-reset" id="resetFilters" title="Limpar filtros">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button class="filter-toggle" data-bs-toggle="collapse" data-bs-target="#filterContent" title="Expandir/Recolher">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filterContent">
                <form id="dateFilterForm" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label><i class="fas fa-calendar-alt"></i> Período</label>
                            <select name="period_preset" id="period_preset" class="form-input">
                                <option value="">Personalizado</option>
                                <option value="today">Hoje</option>
                                <option value="yesterday" selected>Ontem</option>
                                <option value="last7days">Últimos 7 dias</option>
                                <option value="last30days">Últimos 30 dias</option>
                                <option value="thismonth">Este mês</option>
                                <option value="lastmonth">Mês anterior</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-play"></i> Data Inicial</label>
                            <input type="date" name="start_date" id="start_date" class="form-input" value="<?= $_GET['start_date'] ?? $defaultStartDate ?>">
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-stop"></i> Data Final</label>
                            <input type="date" name="end_date" id="end_date" class="form-input" value="<?= $_GET['end_date'] ?? $defaultEndDate ?>">
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-phone"></i> Tipo</label>
                            <select name="call_type_filter" class="form-input">
                                <option value="">Todos os tipos</option>
                                <option value="interna">Internas</option>
                                <option value="entrada">Recebidas</option>
                                <option value="saida">Realizadas</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-check-circle"></i> Status</label>
                            <select name="status_filter" class="form-input">
                                <option value="">Todos os status</option>
                                <option value="answered">Atendidas</option>
                                <option value="unanswered">Não atendidas</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions-row">
                        <button type="submit" class="btn-filter btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Aplicar Filtros</span>
                        </button>
                        <button type="button" class="btn-filter btn-secondary" id="exportFilters">
                            <i class="fas fa-download"></i>
                            <span>Exportar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Métricas Principais com Modal -->
        <div class="metrics-grid">
            <div class="metric-card primary clickable" data-metric="total_calls">
                <div class="metric-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="metric-content">
                    <h3><?= number_format($stats['total_calls'] ?? 0, 0, ',', '.') ?></h3>
                    <p>Total de Chamadas</p>
                    <span class="metric-subtitle">Processadas no período</span>
                </div>
                <div class="metric-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <div class="metric-card success clickable" data-metric="answered_calls">
                <div class="metric-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-content">
                    <h3><?= number_format($stats['answered_calls'] ?? 0, 0, ',', '.') ?></h3>
                    <p>Atendidas</p>
                    <span class="metric-subtitle"><?= $stats['success_rate'] ?? 0 ?>% de eficiência</span>
                </div>
                <div class="metric-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <div class="metric-card warning clickable" data-metric="unanswered_calls">
                <div class="metric-icon">
                    <i class="fas fa-phone-slash"></i>
                </div>
                <div class="metric-content">
                    <h3><?= number_format($stats['unanswered_calls'] ?? 0, 0, ',', '.') ?></h3>
                    <p>Não Atendidas</p>
                    <span class="metric-subtitle">Oportunidades perdidas</span>
                </div>
                <div class="metric-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <div class="metric-card info clickable" data-metric="transfer_calls">
                <div class="metric-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="metric-content">
                    <h3><?= number_format($stats['transfer_calls'] ?? 0, 0, ',', '.') ?></h3>
                    <p>Transferências</p>
                    <span class="metric-subtitle">Redirecionamentos realizados</span>
                </div>
                <div class="metric-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <!-- KPIs Secundários -->
        <div class="kpi-grid">
            <div class="kpi-item">
                <div class="kpi-value"><?= $stats['success_rate'] ?? 0 ?>%</div>
                <div class="kpi-label">Taxa de Atendimento</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-value"><?= str_replace('.', ',', $stats['avg_duration'] ?? 0) ?>min</div>
                <div class="kpi-label">Duração Média</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-value"><?= number_format($stats['unique_extensions'] ?? 0, 0, ',', '.') ?></div>
                <div class="kpi-label">Ramais Ativos</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-value"><?= $stats['peak_hour'] ?? '14:00' ?></div>
                <div class="kpi-label">Horário de Pico</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-value"><?= number_format($stats['today_calls'] ?? 0, 0, ',', '.') ?></div>
                <div class="kpi-label">Hoje</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-value"><?= number_format($stats['total_records'] ?? 0, 0, ',', '.') ?></div>
                <div class="kpi-label">Registros Totais</div>
            </div>
        </div>

        <!-- Gráficos e Análises com Exportação -->
        <div class="charts-section">
            <div class="charts-grid">
                <!-- Gráfico de Status -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4>Distribuição por Status</h4>
                        <div class="chart-actions">
                            <button class="chart-action-btn" data-action="download" data-chart="statusPieChart" title="Baixar imagem">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="chart-action-btn" data-action="print" data-chart="statusPieChart" title="Imprimir">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="chart-action-btn" data-action="fullscreen" data-chart="statusPieChart" title="Tela cheia">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Tipos -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4>Tipos de Chamada</h4>
                        <div class="chart-actions">
                            <button class="chart-action-btn" data-action="download" data-chart="typesPieChart" title="Baixar imagem">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="chart-action-btn" data-action="print" data-chart="typesPieChart" title="Imprimir">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="chart-action-btn" data-action="fullscreen" data-chart="typesPieChart" title="Tela cheia">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="typesPieChart"></canvas>
                    </div>
                </div>

                <!-- Resumo Executivo -->
                <div class="summary-card">
                    <div class="summary-header">
                        <h4>Resumo Executivo</h4>
                        <div class="chart-actions">
                            <button class="chart-action-btn" data-action="export" data-chart="summary" title="Exportar dados">
                                <i class="fas fa-file-export"></i>
                            </button>
                            <button class="chart-action-btn" data-action="print" data-chart="summary" title="Imprimir">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </div>
                    <div class="summary-content">
                        <div class="summary-item">
                            <span class="summary-label">Volume Total</span>
                            <span class="summary-value"><?= number_format($stats['total_calls'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Eficiência</span>
                            <span class="summary-value success"><?= $stats['success_rate'] ?? 0 ?>%</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Tempo Médio</span>
                            <span class="summary-value"><?= str_replace('.', ',', $stats['avg_duration'] ?? 0) ?>min</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Ramais Ativos</span>
                            <span class="summary-value"><?= number_format($stats['unique_extensions'] ?? 0, 0, ',', '.') ?></span>
                        </div>

                        <div class="insight-box">
                            <div class="insight-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="insight-content">
                                <strong>Insight:</strong>
                                <?php if (($stats['success_rate'] ?? 0) >= 80): ?>
                                    Excelente performance! Taxa de atendimento acima da média.
                                <?php elseif (($stats['success_rate'] ?? 0) >= 60): ?>
                                    Performance boa, mas há oportunidades de melhoria.
                                <?php else: ?>
                                    Taxa baixa. Recomenda-se revisar processos de atendimento.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Atividade por Hora com Exportação -->
        <?php if (!empty($hourlyData)): ?>
            <div class="activity-section">
                <div class="section-header">
                    <div class="section-title">
                        <h3>Atividade por Horário</h3>
                        <div class="activity-stats">
                            <span class="activity-stat primary">Total: <?= number_format(array_sum(array_column($hourlyData, 'total')), 0, ',', '.') ?></span>
                            <span class="activity-stat success">Atendidas: <?= number_format(array_sum(array_column($hourlyData, 'answered')), 0, ',', '.') ?></span>
                            <span class="activity-stat warning">Perdidas: <?= number_format(array_sum(array_column($hourlyData, 'unanswered')), 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="chart-actions">
                        <button class="chart-action-btn" data-action="download" data-chart="hourlyChart" title="Baixar imagem">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="chart-action-btn" data-action="print" data-chart="hourlyChart" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="chart-action-btn" data-action="fullscreen" data-chart="hourlyChart" title="Tela cheia">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="activity-chart">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        <?php endif; ?>

        <!-- Performance e Histórico com Mini-filtros -->
        <div class="performance-section">
            <div class="performance-grid">
                <!-- Top Ramais com Mini-filtro -->
                <div class="performance-card">
                    <div class="performance-header">
                        <h4>Performance dos Ramais</h4>
                        <div class="performance-controls">
                            <div class="mini-filter">
                                <select id="extensionsLimit" class="mini-filter-select">
                                    <option value="8">Top 8</option>
                                    <option value="15">Top 15</option>
                                    <option value="25">Top 25</option>
                                    <option value="50">Top 50</option>
                                </select>
                            </div>
                            <div class="mini-filter">
                                <select id="extensionsSort" class="mini-filter-select">
                                    <option value="calls">Por Volume</option>
                                    <option value="efficiency">Por Eficiência</option>
                                    <option value="duration">Por Duração</option>
                                </select>
                            </div>
                            <span class="performance-count" id="extensionsCount"><?= count($topExtensions ?? []) ?> ramais</span>
                        </div>
                    </div>
                    <div class="performance-content">
                        <div class="performance-loading" id="extensionsLoading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                        <div id="extensionsList">
                            <?php if (!empty($topExtensions)): ?>
                                <div class="performance-list">
                                    <?php foreach (array_slice($topExtensions, 0, 8) as $index => $ext): ?>
                                        <div class="performance-item clickable-item" data-type="extension" data-id="<?= $ext['extension'] ?>">
                                            <div class="performance-rank">
                                                <?php if ($index < 3): ?>
                                                    <span class="rank-medal"><?= ['🥇', '🥈', '🥉'][$index] ?></span>
                                                <?php else: ?>
                                                    <span class="rank-number"><?= $index + 1 ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="performance-info">
                                                <div class="performance-name">Ramal <?= $ext['extension'] ?></div>
                                                <div class="performance-stats">
                                                    <span><?= number_format($ext['total_calls'], 0, ',', '.') ?> chamadas</span>
                                                    <span class="efficiency <?= $ext['success_rate'] >= 90 ? 'high' : ($ext['success_rate'] >= 70 ? 'medium' : 'low') ?>">
                                                        <?= $ext['success_rate'] ?>%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="performance-action">
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-phone"></i>
                                    <p>Nenhum ramal encontrado</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Destinos com Mini-filtro -->
                <div class="performance-card">
                    <div class="performance-header">
                        <h4>Destinos Mais Contatados</h4>
                        <div class="performance-controls">
                            <div class="mini-filter">
                                <select id="destinationsLimit" class="mini-filter-select">
                                    <option value="8">Top 8</option>
                                    <option value="15">Top 15</option>
                                    <option value="25">Top 25</option>
                                    <option value="50">Top 50</option>
                                </select>
                            </div>
                            <div class="mini-filter">
                                <select id="destinationsType" class="mini-filter-select">
                                    <option value="">Todos</option>
                                    <option value="celular">Celular</option>
                                    <option value="fixo">Fixo</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                            <span class="performance-count" id="destinationsCount"><?= count($topDestinations ?? []) ?> destinos</span>
                        </div>
                    </div>
                    <div class="performance-content">
                        <div class="performance-loading" id="destinationsLoading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                        <div id="destinationsList">
                            <?php if (!empty($topDestinations)): ?>
                                <div class="performance-list">
                                    <?php foreach (array_slice($topDestinations, 0, 8) as $dest): ?>
                                        <div class="performance-item clickable-item" data-type="destination" data-id="<?= $dest['destination'] ?>">
                                            <div class="performance-info">
                                                <div class="performance-name">
                                                    <?= htmlspecialchars(substr($dest['destination'], 0, 15)) ?><?= strlen($dest['destination']) > 15 ? '...' : '' ?>
                                                    <span class="destination-type <?= strtolower($dest['type']) ?>"><?= $dest['type'] ?></span>
                                                </div>
                                                <div class="performance-stats">
                                                    <span><?= number_format($dest['total_calls'], 0, ',', '.') ?> chamadas</span>
                                                    <span class="efficiency <?= $dest['success_rate'] >= 90 ? 'high' : ($dest['success_rate'] >= 70 ? 'medium' : 'low') ?>">
                                                        <?= $dest['success_rate'] ?>%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="performance-action">
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-phone-alt"></i>
                                    <p>Nenhum destino encontrado</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico Recente com Mini-filtro -->
        <div class="history-section">
            <div class="section-header">
                <h3>Histórico Recente</h3>
                <div class="section-controls">
                    <div class="mini-filter">
                        <select id="historyLimit" class="mini-filter-select">
                            <option value="10">10 registros</option>
                            <option value="25">25 registros</option>
                            <option value="50">50 registros</option>
                            <option value="100">100 registros</option>
                        </select>
                    </div>
                    <div class="mini-filter">
                        <select id="historyType" class="mini-filter-select">
                            <option value="">Todos os tipos</option>
                            <option value="interna">Internas</option>
                            <option value="entrada">Recebidas</option>
                            <option value="saida">Realizadas</option>
                        </select>
                    </div>
                    <span class="history-count" id="historyCount"><?= count($recentCalls ?? []) ?> registros</span>
                    <button class="btn-icon" id="refreshRecentCalls">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="history-content">
                <div class="performance-loading" id="historyLoading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Carregando...</span>
                </div>
                <div id="historyList">
                    <?php if (!empty($recentCalls)): ?>
                        <div class="history-list">
                            <?php foreach ($recentCalls as $call): ?>
                                <div class="history-item clickable-item" data-type="call" data-id="<?= $call['uniqueid'] ?? uniqid() ?>">
                                    <div class="history-time">
                                        <?= date('H:i', strtotime($call['calldate_formatted'])) ?>
                                    </div>
                                    <div class="history-info">
                                        <div class="history-main">
                                            <span class="history-from"><?= htmlspecialchars(substr($call['src'], 0, 12)) ?></span>
                                            <i class="fas fa-arrow-right"></i>
                                            <span class="history-to"><?= htmlspecialchars(substr($call['dst'], 0, 12)) ?></span>
                                        </div>
                                        <div class="history-meta">
                                            <?php if ($call['extension']): ?>
                                                <span class="history-extension">Ramal <?= $call['extension'] ?></span>
                                            <?php endif; ?>
                                            <span class="history-duration"><?= $call['duration_formatted'] ?></span>
                                            <span class="history-type <?= strtolower($call['call_type']) ?>"><?= $call['call_type'] ?></span>
                                        </div>
                                    </div>
                                    <div class="history-status">
                                        <span class="status-badge <?= $call['disposition'] === 'ANSWERED' ? 'success' : 'warning' ?>">
                                            <?= $call['status_formatted'] ?>
                                        </span>
                                    </div>
                                    <div class="performance-action">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state large">
                            <i class="fas fa-phone"></i>
                            <h4>Nenhuma chamada encontrada</h4>
                            <p>Não há registros no período selecionado</p>
                            <button class="btn-modern btn-primary" onclick="location.reload()">
                                <i class="fas fa-refresh"></i>
                                <span>Atualizar</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalhes das Métricas -->
<div class="modal fade" id="metricModal" tabindex="-1" aria-labelledby="metricModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="metricModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>
                    Detalhamento da Métrica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-loading" id="modalLoading">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Carregando detalhes...</p>
                    </div>
                </div>
                <div class="modal-content-data" id="modalContent" style="display: none;">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary" id="exportModalData">
                    <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalhes dos Itens -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Detalhes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-loading" id="detailModalLoading">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Carregando detalhes...</p>
                    </div>
                </div>
                <div class="modal-content-data" id="detailModalContent" style="display: none;">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Fechar
                </button>
                <button type="button" class="btn btn-primary" id="exportDetailData">
                    <i class="fas fa-file-excel me-1"></i>Exportar Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Tela Cheia dos Gráficos -->
<div class="modal fade" id="chartFullscreenModal" tabindex="-1" aria-labelledby="chartFullscreenLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartFullscreenLabel">Gráfico em Tela Cheia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="fullscreen-chart-container">
                    <canvas id="fullscreenChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/dashboard.js"></script>

<!-- Dados para JavaScript -->
<script>
    window.dashboardData = {
        APP_URL: '<?= APP_URL ?>',
        callsByStatus: <?= json_encode($callsByStatus ?? []) ?>,
        callsByType: <?= json_encode($callsByType ?? []) ?>,
        hourlyData: <?= json_encode($hourlyData ?? []) ?>,
        stats: <?= json_encode($stats ?? []) ?>,
        transferStats: <?= json_encode($transferStats ?? []) ?>,
        topExtensions: <?= json_encode($topExtensions ?? []) ?>,
        topDestinations: <?= json_encode($topDestinations ?? []) ?>,
        recentCalls: <?= json_encode($recentCalls ?? []) ?>,
        currentPeriod: {
            start: '<?= $_GET['start_date'] ?? $defaultStartDate ?>',
            end: '<?= $_GET['end_date'] ?? $defaultEndDate ?>'
        }
    };
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>