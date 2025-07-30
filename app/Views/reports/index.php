<?php
$currentPage = 'reports';
$pageTitle = 'Relatórios CDR';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-header-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Relatórios CDR
                </h1>
                <p class="page-header-subtitle">Sistema de Análise de Chamadas Telefônicas</p>
            </div>
            <div class="page-header-actions">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-success" id="exportExcelBtn" disabled>
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="exportCsvBtn" disabled>
                        <i class="fas fa-file-csv me-1"></i>CSV
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="printBtn" disabled>
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>

        <?php if (!$mongodbEnabled): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>MongoDB Offline:</strong> Conecte-se ao MongoDB para ver dados reais das chamadas.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-database me-2"></i>
                <strong>MongoDB Conectado:</strong> Sistema pronto para gerar relatórios com dados reais.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Filtros -->
            <div class="col-xl-3 col-lg-4">
                <!-- Card de Filtros -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-content">
                            <h6 class="card-title">
                                <i class="fas fa-filter me-2"></i>Filtros de Relatório
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportFiltersForm">
                            <!-- Período -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt me-2"></i>Período
                                </label>
                                <select class="form-select" id="periodPreset" name="period_preset">
                                    <option value="">Personalizado</option>
                                    <option value="today">Hoje</option>
                                    <option value="yesterday">Ontem</option>
                                    <option value="last7days" selected>Últimos 7 dias</option>
                                    <option value="thismonth">Este mês</option>
                                    <option value="lastmonth">Mês passado</option>
                                    <option value="thisyear">Este ano</option>
                                </select>
                            </div>

                            <!-- Datas Personalizadas -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data Inicial</label>
                                        <input type="date" class="form-control" id="startDate" name="start_date">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data Final</label>
                                        <input type="date" class="form-control" id="endDate" name="end_date">
                                    </div>
                                </div>
                            </div>

                            <!-- Horário -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hora Inicial</label>
                                        <input type="time" class="form-control" id="startTime" name="start_time">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hora Final</label>
                                        <input type="time" class="form-control" id="endTime" name="end_time">
                                    </div>
                                </div>
                            </div>

                            <!-- Ramais -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-phone me-2"></i>Ramais de Origem
                                </label>
                                <select class="form-select" id="originExtension" name="origin_extension[]" multiple>
                                    <?php if (!empty($extensions)): ?>
                                        <?php foreach ($extensions as $ext): ?>
                                            <option value="<?= htmlspecialchars($ext) ?>">Ramal <?= htmlspecialchars($ext) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="4554">Ramal 4554</option>
                                        <option value="4555">Ramal 4555</option>
                                        <option value="4556">Ramal 4556</option>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Ctrl+Click para múltipla seleção</small>
                            </div>

                            <!-- Número de Destino -->
                            <div class="mb-3">
                                <label class="form-label">Número de Destino</label>
                                <input type="text" class="form-control" id="destinationNumber" name="destination_number"
                                    placeholder="Ex: 4554, 31999999999">
                                <small class="text-muted">Números separados por vírgula</small>
                            </div>

                            <!-- Departamentos -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-building me-2"></i>Departamentos
                                </label>
                                <select class="form-select" id="department" name="department[]" multiple>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="Recepção">Recepção</option>
                                        <option value="UTI">UTI</option>
                                        <option value="Enfermagem">Enfermagem</option>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Ctrl+Click para múltipla seleção</small>
                            </div>

                            <!-- Tipo de Chamada -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Chamada</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="callTypeInternal" name="call_type[]" value="internal" checked>
                                    <label class="form-check-label" for="callTypeInternal">
                                        <i class="fas fa-users text-success me-1"></i>Internas
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="callTypeOutbound" name="call_type[]" value="outbound" checked>
                                    <label class="form-check-label" for="callTypeOutbound">
                                        <i class="fas fa-phone text-primary me-1"></i>Saída
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="callTypeInbound" name="call_type[]" value="inbound" checked>
                                    <label class="form-check-label" for="callTypeInbound">
                                        <i class="fas fa-phone-square text-info me-1"></i>Entrada
                                    </label>
                                </div>
                            </div>

                            <!-- Duração -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Duração (segundos)</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" id="minDuration" name="min_duration" min="0" placeholder="Mín">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" id="maxDuration" name="max_duration" min="0" placeholder="Máx">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    0-5s: Não completadas | 5-30s: Curtas | 300s+: Longas
                                </small>
                            </div>

                            <!-- Status da Chamada -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status da Chamada</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="statusAnswered" name="call_status[]" value="answered" checked>
                                    <label class="form-check-label" for="statusAnswered">
                                        <i class="fas fa-check-circle text-success me-1"></i>Atendidas
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="statusUnanswered" name="call_status[]" value="unanswered">
                                    <label class="form-check-label" for="statusUnanswered">
                                        <i class="fas fa-times-circle text-danger me-1"></i>Não Atendidas
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="statusBusy" name="call_status[]" value="busy">
                                    <label class="form-check-label" for="statusBusy">
                                        <i class="fas fa-phone-slash text-warning me-1"></i>Ocupado
                                    </label>
                                </div>
                            </div>

                            <!-- Classificação do Destino -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Destino</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="destMobile" name="destination_type[]" value="mobile" checked>
                                    <label class="form-check-label" for="destMobile">
                                        <i class="fas fa-mobile-alt text-primary me-1"></i>Celular
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="destFixed" name="destination_type[]" value="fixed" checked>
                                    <label class="form-check-label" for="destFixed">
                                        <i class="fas fa-phone text-secondary me-1"></i>Fixo
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="destInternal" name="destination_type[]" value="internal" checked>
                                    <label class="form-check-label" for="destInternal">
                                        <i class="fas fa-building text-success me-1"></i>Interno
                                    </label>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" id="generateReportBtn">
                                    <i class="fas fa-search me-2"></i>Gerar Relatório
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">
                                    <i class="fas fa-undo me-2"></i>Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-info" id="saveFiltersBtn">
                                    <i class="fas fa-save me-2"></i>Salvar Filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Status do Sistema -->
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="card-header-content">
                            <h6 class="card-title">
                                <i class="fas fa-server me-2"></i>Status do Sistema
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-database text-<?= $mongodbEnabled ? 'success' : 'danger' ?> me-2"></i>
                            <span>MongoDB: <?= $mongodbEnabled ? 'Conectado' : 'Offline' ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            <span>Relatórios: Ativo</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-info me-2"></i>
                            <span>Atualizado: <?= date('H:i:s') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área Principal -->
            <div class="col-xl-9 col-lg-8">
                <!-- Estatísticas Resumidas -->
                <div id="reportSummary" class="row mb-4" style="display: none;">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1">Total de Chamadas</h6>
                                        <h3 class="mb-0" id="totalCalls">0</h3>
                                    </div>
                                    <div>
                                        <i class="fas fa-phone fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1">Tempo Total</h6>
                                        <h3 class="mb-0" id="totalDuration">0h</h3>
                                    </div>
                                    <div>
                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1">Duração Média</h6>
                                        <h3 class="mb-0" id="avgDuration">0m</h3>
                                    </div>
                                    <div>
                                        <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1">Ramais Únicos</h6>
                                        <h3 class="mb-0" id="uniqueExtensions">0</h3>
                                    </div>
                                    <div>
                                        <i class="fas fa-users fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Resultados -->
                <div class="card" id="reportResults" style="display: none;">
                    <div class="card-header">
                        <div class="card-header-content">
                            <h6 class="card-title">
                                <i class="fas fa-list me-2"></i>Resultados do Relatório
                            </h6>
                            <div class="card-header-actions">
                                <small class="text-muted" id="reportInfo">0 registros encontrados</small>
                            </div>
                        </div>
                    </div>

                    <!-- Configuração de Colunas (Oculta por padrão) -->
                    <div class="card-body border-bottom" id="columnConfig" style="display: none;">
                        <h6 class="mb-3">Configurar Colunas Visíveis</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_callstart" checked data-column="callstart">
                                    <label class="form-check-label" for="col_callstart">Data/Hora Início</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_caller" checked data-column="caller">
                                    <label class="form-check-label" for="col_caller">Número Origem</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_callednumber" checked data-column="callednumber">
                                    <label class="form-check-label" for="col_callednumber">Número Destino</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_duration" checked data-column="duration">
                                    <label class="form-check-label" for="col_duration">Duração</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_extension" checked data-column="extension">
                                    <label class="form-check-label" for="col_extension">Ramal Origem</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_user_name" checked data-column="user_name">
                                    <label class="form-check-label" for="col_user_name">Usuário/Departamento</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_call_type" checked data-column="call_type">
                                    <label class="form-check-label" for="col_call_type">Tipo da Chamada</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" type="checkbox" id="col_call_status" checked data-column="call_status">
                                    <label class="form-check-label" for="col_call_status">Status da Chamada</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-primary" id="applyColumnConfigBtn">
                                <i class="fas fa-check me-1"></i>Aplicar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetColumnConfigBtn">
                                <i class="fas fa-undo me-1"></i>Padrão
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="toggleColumnConfigBtn">
                                <i class="fas fa-columns me-1"></i>Colunas
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr id="reportTableHeader">
                                        <!-- Headers serão gerados dinamicamente -->
                                    </tr>
                                </thead>
                                <tbody id="reportTableBody">
                                    <!-- Dados serão carregados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando <span id="showingFrom">0</span> a <span id="showingTo">0</span>
                                de <span id="totalRecords">0</span> registros
                            </small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="reportPagination">
                                    <!-- Paginação será gerada dinamicamente -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Estado Inicial -->
                <div class="card" id="initialState">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">Sistema de Relatórios CDR</h4>
                        <p class="text-muted mb-4">
                            Configure os filtros ao lado e clique em "Gerar Relatório" para começar a análise das chamadas.
                        </p>

                        <div class="row justify-content-center mt-4">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-body text-center">
                                                <i class="fas fa-filter fa-2x text-primary mb-2"></i>
                                                <h6>Filtros Avançados</h6>
                                                <small class="text-muted">Múltiplos critérios de busca</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-success">
                                            <div class="card-body text-center">
                                                <i class="fas fa-download fa-2x text-success mb-2"></i>
                                                <h6>Exportação</h6>
                                                <small class="text-muted">Excel, CSV e impressão</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-info">
                                            <div class="card-body text-center">
                                                <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                                <h6>Análises</h6>
                                                <small class="text-muted">Estatísticas detalhadas</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div id="reportLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-3 text-muted">Processando relatório...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes da Chamada -->
<div class="modal fade" id="callDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detalhes da Chamada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="callDetailsContent">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.APP_URL = '<?= APP_URL ?>';
    window.MONGODB_ENABLED = <?= $mongodbEnabled ? 'true' : 'false' ?>;

    // JavaScript corrigido
    class ReportsManager {
        constructor() {
            this.currentPage = 1;
            this.currentSort = {
                column: 'callstart',
                order: 'desc'
            };
            this.currentFilters = {};
            this.visibleColumns = ['callstart', 'caller', 'callednumber', 'duration', 'extension', 'user_name', 'call_type', 'call_status'];
            this.reportData = null;

            this.init();
        }

        init() {
            console.log('📊 Iniciando ReportsManager...');
            this.bindEvents();
            this.initializeFilters();
        }

        bindEvents() {
            // Botão gerar relatório
            document.getElementById('generateReportBtn')?.addEventListener('click', () => {
                this.generateReport();
            });

            // Botão resetar filtros
            document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
                this.resetFilters();
            });

            // Botão salvar filtros
            document.getElementById('saveFiltersBtn')?.addEventListener('click', () => {
                this.saveFilters();
            });

            // Botões de exportação
            document.getElementById('exportExcelBtn')?.addEventListener('click', () => {
                this.exportReport('excel');
            });

            document.getElementById('exportCsvBtn')?.addEventListener('click', () => {
                this.exportReport('csv');
            });

            document.getElementById('printBtn')?.addEventListener('click', () => {
                this.printReport();
            });

            // Configuração de colunas
            document.getElementById('toggleColumnConfigBtn')?.addEventListener('click', () => {
                this.toggleColumnConfig();
            });

            document.getElementById('applyColumnConfigBtn')?.addEventListener('click', () => {
                this.applyColumnConfig();
            });

            document.getElementById('resetColumnConfigBtn')?.addEventListener('click', () => {
                this.resetColumnConfig();
            });

            // Período pré-definido
            document.getElementById('periodPreset')?.addEventListener('change', (e) => {
                this.handlePeriodPresetChange(e.target.value);
            });
        }

        initializeFilters() {
            // Definir data padrão (últimos 7 dias)
            const today = new Date();
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);

            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            if (startDate) startDate.value = this.formatDate(weekAgo);
            if (endDate) endDate.value = this.formatDate(today);
        }

        formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        handlePeriodPresetChange(preset) {
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const today = new Date();

            if (preset === '' || !startDate || !endDate) {
                return;
            }

            let start, end;

            switch (preset) {
                case 'today':
                    start = end = today;
                    break;
                case 'yesterday':
                    start = end = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                    break;
                case 'last7days':
                    start = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    end = today;
                    break;
                case 'thismonth':
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = today;
                    break;
                case 'lastmonth':
                    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    end = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'thisyear':
                    start = new Date(today.getFullYear(), 0, 1);
                    end = today;
                    break;
                default:
                    return;
            }

            startDate.value = this.formatDate(start);
            endDate.value = this.formatDate(end);
        }

        async generateReport(page = 1) {
            try {
                this.showLoading();
                this.hideInitialState();

                const filters = this.collectFilters();
                filters.page = page;
                filters.per_page = 50;
                filters.sort_by = this.currentSort.column;
                filters.sort_order = this.currentSort.order;

                console.log('🔍 Gerando relatório:', filters);

                const response = await fetch(window.APP_URL + '/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: this.encodeFormData(filters)
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.reportData = result.data;
                    this.currentPage = page;
                    this.currentFilters = filters;

                    this.displayReport(result.data);
                    this.enableExportButtons();

                    console.log('✅ Relatório gerado:', result.data);
                } else {
                    throw new Error(result.error || 'Erro desconhecido');
                }

            } catch (error) {
                console.error('❌ Erro ao gerar relatório:', error);
                alert('Erro ao gerar relatório: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }

        collectFilters() {
            const form = document.getElementById('reportFiltersForm');
            const formData = new FormData(form);
            const filters = {};

            // Campos simples
            const simpleFields = [
                'period_preset', 'start_date', 'end_date', 'start_time', 'end_time',
                'destination_number', 'min_duration', 'max_duration'
            ];

            simpleFields.forEach(field => {
                const value = formData.get(field);
                if (value && value.trim() !== '') {
                    filters[field] = value.trim();
                }
            });

            // Campos múltiplos
            const multipleFields = [
                'origin_extension', 'department', 'call_type', 'call_status', 'destination_type'
            ];

            multipleFields.forEach(field => {
                const values = formData.getAll(field + '[]');
                if (values.length > 0) {
                    filters[field] = values;
                }
            });

            return filters;
        }

        displayReport(data) {
            this.updateStatistics(data.statistics);
            this.updateTable(data.calls);
            this.updatePagination(data.pagination);
            this.updateReportInfo(data);

            this.showReportResults();
        }

        updateStatistics(stats) {
            const elements = {
                totalCalls: document.getElementById('totalCalls'),
                totalDuration: document.getElementById('totalDuration'),
                avgDuration: document.getElementById('avgDuration'),
                uniqueExtensions: document.getElementById('uniqueExtensions')
            };

            if (elements.totalCalls) elements.totalCalls.textContent = this.formatNumber(stats.total_calls);
            if (elements.totalDuration) elements.totalDuration.textContent = stats.total_duration_formatted;
            if (elements.avgDuration) elements.avgDuration.textContent = stats.avg_duration_formatted;
            if (elements.uniqueExtensions) elements.uniqueExtensions.textContent = this.formatNumber(stats.unique_extensions);

            const summaryDiv = document.getElementById('reportSummary');
            if (summaryDiv) summaryDiv.style.display = 'block';
        }

        updateTable(calls) {
            const tableHeader = document.getElementById('reportTableHeader');
            const tableBody = document.getElementById('reportTableBody');

            if (!tableHeader || !tableBody) {
                console.error('❌ Elementos da tabela não encontrados');
                return;
            }

            // Limpar tabela
            tableHeader.innerHTML = '';
            tableBody.innerHTML = '';

            if (calls.length === 0) {
                tableBody.innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Nenhum registro encontrado com os filtros aplicados.</p>
                    </td>
                </tr>
            `;
                return;
            }

            // Gerar cabeçalhos
            this.generateTableHeaders(tableHeader);

            // Gerar linhas
            calls.forEach(call => {
                const row = this.generateTableRow(call);
                tableBody.appendChild(row);
            });
        }

        generateTableHeaders(container) {
            const columns = [{
                    key: 'callstart',
                    label: 'Data/Hora'
                },
                {
                    key: 'caller',
                    label: 'Origem'
                },
                {
                    key: 'callednumber',
                    label: 'Destino'
                },
                {
                    key: 'duration',
                    label: 'Duração'
                },
                {
                    key: 'extension',
                    label: 'Ramal'
                },
                {
                    key: 'user_name',
                    label: 'Usuário'
                },
                {
                    key: 'call_type',
                    label: 'Tipo'
                },
                {
                    key: 'call_status',
                    label: 'Status'
                }
            ];

            columns.forEach(column => {
                if (this.visibleColumns.includes(column.key)) {
                    const th = document.createElement('th');
                    th.className = 'sortable';
                    th.innerHTML = `<i class="fas fa-sort me-1"></i>${column.label}`;
                    th.dataset.column = column.key;
                    th.style.cursor = 'pointer';
                    th.addEventListener('click', () => this.sortTable(column.key));

                    // Aplicar ordenação atual
                    if (this.currentSort.column === column.key) {
                        th.classList.remove('sortable');
                        th.classList.add(this.currentSort.order === 'asc' ? 'sort-asc' : 'sort-desc');
                    }

                    container.appendChild(th);
                }
            });
        }

        generateTableRow(call) {
            const row = document.createElement('tr');
            row.className = 'clickable-row';
            row.style.cursor = 'pointer';
            row.addEventListener('click', () => this.showCallDetails(call.callid));

            const columns = [{
                    key: 'callstart',
                    label: 'Data/Hora'
                },
                {
                    key: 'caller',
                    label: 'Origem'
                },
                {
                    key: 'callednumber',
                    label: 'Destino'
                },
                {
                    key: 'duration',
                    label: 'Duração'
                },
                {
                    key: 'extension',
                    label: 'Ramal'
                },
                {
                    key: 'user_name',
                    label: 'Usuário'
                },
                {
                    key: 'call_type',
                    label: 'Tipo'
                },
                {
                    key: 'call_status',
                    label: 'Status'
                }
            ];

            columns.forEach(column => {
                if (this.visibleColumns.includes(column.key)) {
                    const td = document.createElement('td');
                    td.innerHTML = this.formatCellValue(call, column);
                    row.appendChild(td);
                }
            });

            return row;
        }

        formatCellValue(call, column) {
            const value = call[column.key] || 'N/A';

            switch (column.key) {
                case 'callstart':
                    return `<small class="text-nowrap">${this.escapeHtml(call.callstart_formatted || value)}</small>`;

                case 'caller':
                case 'callednumber':
                    return `<span class="fw-medium">${this.escapeHtml(value)}</span>`;

                case 'duration':
                    const seconds = call.duration_seconds || 0;
                    let className = 'text-warning';
                    if (seconds < 30) className = 'text-danger';
                    else if (seconds > 300) className = 'text-success';

                    return `<span class="${className}">${this.escapeHtml(call.duration_formatted || value)}</span>`;

                case 'extension':
                    return `<span class="badge bg-primary">${this.escapeHtml(value)}</span>`;

                case 'user_name':
                    return `<span class="text-truncate" style="max-width: 150px;" title="${this.escapeHtml(value)}">${this.escapeHtml(value)}</span>`;

                case 'call_type':
                    const typeClass = this.getCallTypeClass(value);
                    return `<span class="badge ${typeClass}">${this.escapeHtml(value)}</span>`;

                case 'call_status':
                    const statusClass = this.getCallStatusClass(value);
                    return `<span class="badge ${statusClass}">${this.escapeHtml(value)}</span>`;

                default:
                    return this.escapeHtml(value);
            }
        }

        getCallTypeClass(type) {
            switch (type.toLowerCase()) {
                case 'interna':
                    return 'bg-success';
                case 'saída':
                    return 'bg-primary';
                case 'entrada':
                    return 'bg-info';
                default:
                    return 'bg-secondary';
            }
        }

        getCallStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'atendida':
                    return 'bg-success';
                case 'não atendida':
                    return 'bg-danger';
                case 'ocupado':
                    return 'bg-warning';
                case 'falha':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        updatePagination(pagination) {
            const container = document.getElementById('reportPagination');
            const showingFrom = document.getElementById('showingFrom');
            const showingTo = document.getElementById('showingTo');
            const totalRecords = document.getElementById('totalRecords');

            if (!container) return;

            // Atualizar informações
            const from = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const to = Math.min(pagination.current_page * pagination.per_page, pagination.total_records);

            if (showingFrom) showingFrom.textContent = this.formatNumber(from);
            if (showingTo) showingTo.textContent = this.formatNumber(to);
            if (totalRecords) totalRecords.textContent = this.formatNumber(pagination.total_records);

            // Gerar paginação
            container.innerHTML = '';

            if (pagination.total_pages <= 1) {
                return;
            }

            // Botão anterior
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${pagination.current_page <= 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" data-page="${pagination.current_page - 1}">Anterior</a>`;
            container.appendChild(prevLi);

            // Páginas
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const pageLi = document.createElement('li');
                pageLi.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
                pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                container.appendChild(pageLi);
            }

            // Botão próximo
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" data-page="${pagination.current_page + 1}">Próximo</a>`;
            container.appendChild(nextLi);

            // Adicionar eventos
            container.querySelectorAll('a[data-page]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(e.target.dataset.page);
                    if (page && page !== pagination.current_page) {
                        this.generateReport(page);
                    }
                });
            });
        }

        updateReportInfo(data) {
            const reportInfo = document.getElementById('reportInfo');
            if (!reportInfo) return;

            const total = data.pagination.total_records;
            const filters = data.filters_applied;

            let info = `${this.formatNumber(total)} registro${total !== 1 ? 's' : ''} encontrado${total !== 1 ? 's' : ''}`;

            if (filters && filters.length > 0) {
                info += ` • Filtros: ${filters.join(', ')}`;
            }

            reportInfo.textContent = info;
        }

        sortTable(column) {
            if (this.currentSort.column === column) {
                this.currentSort.order = this.currentSort.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.currentSort.column = column;
                this.currentSort.order = 'desc';
            }

            this.generateReport(1);
        }

        async showCallDetails(callId) {
            try {
                const response = await fetch(`${window.APP_URL}/reports/call-details?call_id=${encodeURIComponent(callId)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.displayCallDetails(result.data);
                } else {
                    throw new Error(result.error);
                }

            } catch (error) {
                console.error('❌ Erro ao buscar detalhes:', error);
                alert('Erro ao carregar detalhes da chamada: ' + error.message);
            }
        }

        displayCallDetails(call) {
            const modal = new bootstrap.Modal(document.getElementById('callDetailsModal'));
            const content = document.getElementById('callDetailsContent');

            if (!content) return;

            content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informações Básicas
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">Call ID:</td>
                            <td class="font-monospace">${this.escapeHtml(call.callid)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Data/Hora:</td>
                            <td>${this.escapeHtml(call.callstart_formatted)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Duração:</td>
                            <td>${this.escapeHtml(call.duration_formatted)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Direção:</td>
                            <td>${this.escapeHtml(call.direction)}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-users me-2"></i>Participantes
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">Origem:</td>
                            <td>${this.escapeHtml(call.caller)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Destino:</td>
                            <td>${this.escapeHtml(call.callednumber)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Ramal:</td>
                            <td><span class="badge bg-primary">${this.escapeHtml(call.extension)}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Usuário:</td>
                            <td>${this.escapeHtml(call.user_name)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;

            modal.show();
        }

        // Configuração de colunas
        toggleColumnConfig() {
            const config = document.getElementById('columnConfig');
            if (config) {
                config.style.display = config.style.display === 'none' ? 'block' : 'none';
            }
        }

        applyColumnConfig() {
            this.visibleColumns = [];
            document.querySelectorAll('.column-toggle:checked').forEach(checkbox => {
                this.visibleColumns.push(checkbox.dataset.column);
            });

            if (this.reportData) {
                this.updateTable(this.reportData.calls);
            }

            const config = document.getElementById('columnConfig');
            if (config) config.style.display = 'none';

            alert('Configuração de colunas aplicada');
        }

        resetColumnConfig() {
            this.visibleColumns = ['callstart', 'caller', 'callednumber', 'duration', 'extension', 'user_name', 'call_type', 'call_status'];

            document.querySelectorAll('.column-toggle').forEach(checkbox => {
                checkbox.checked = this.visibleColumns.includes(checkbox.dataset.column);
            });

            this.applyColumnConfig();
        }

        // Exportação
        async exportReport(format) {
            try {
                if (!this.currentFilters || Object.keys(this.currentFilters).length === 0) {
                    alert('Gere um relatório primeiro antes de exportar');
                    return;
                }

                const filters = {
                    ...this.currentFilters
                };
                filters.format = format;

                const response = await fetch(window.APP_URL + '/reports/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: this.encodeFormData(filters)
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                // Download do arquivo
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `relatorio-cdr-${new Date().toISOString().split('T')[0]}.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                alert(`Relatório exportado em ${format.toUpperCase()}`);

            } catch (error) {
                console.error('❌ Erro ao exportar:', error);
                alert('Erro ao exportar relatório');
            }
        }

        printReport() {
            if (!this.reportData) {
                alert('Gere um relatório primeiro antes de imprimir');
                return;
            }

            window.print();
        }

        // Filtros salvos
        saveFilters() {
            const filters = this.collectFilters();
            const name = prompt('Nome para este conjunto de filtros:');

            if (name && name.trim()) {
                const savedFilters = this.getSavedFilters();
                savedFilters[name.trim()] = filters;
                localStorage.setItem('cdr_saved_filters', JSON.stringify(savedFilters));

                alert('Filtros salvos com sucesso');
            }
        }

        getSavedFilters() {
            try {
                return JSON.parse(localStorage.getItem('cdr_saved_filters') || '{}');
            } catch {
                return {};
            }
        }

        resetFilters() {
            const form = document.getElementById('reportFiltersForm');
            if (form) {
                form.reset();
                this.initializeFilters();
            }
        }

        // Utilitários
        showLoading() {
            const loading = document.getElementById('reportLoading');
            const results = document.getElementById('reportResults');
            const initial = document.getElementById('initialState');

            if (loading) loading.style.display = 'block';
            if (results) results.style.display = 'none';
            if (initial) initial.style.display = 'none';
        }

        hideLoading() {
            const loading = document.getElementById('reportLoading');
            if (loading) loading.style.display = 'none';
        }

        showReportResults() {
            const results = document.getElementById('reportResults');
            const initial = document.getElementById('initialState');

            if (results) results.style.display = 'block';
            if (initial) initial.style.display = 'none';
        }

        hideInitialState() {
            const initial = document.getElementById('initialState');
            if (initial) initial.style.display = 'none';
        }

        enableExportButtons() {
            const buttons = ['exportExcelBtn', 'exportCsvBtn', 'printBtn'];
            buttons.forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) btn.disabled = false;
            });
        }

        formatNumber(num) {
            return new Intl.NumberFormat('pt-BR').format(num);
        }

        escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        encodeFormData(data) {
            const params = new URLSearchParams();

            Object.keys(data).forEach(key => {
                const value = data[key];
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(key + '[]', v));
                } else if (value !== null && value !== undefined) {
                    params.append(key, value);
                }
            });

            return params.toString();
        }
    }

    // Inicializar quando DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar se estamos na página de relatórios
        if (document.getElementById('reportFiltersForm')) {
            window.reportsManager = new ReportsManager();
            console.log('📊 ReportsManager inicializado com sucesso!');
        }
    });
</script>

<style>
    /* Estilos específicos para a página de relatórios */
    .clickable-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-row:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: translateX(2px);
    }

    .table th.sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
        transition: all 0.2s ease;
    }

    .table th.sortable:hover {
        background-color: #e9ecef;
    }

    .table th.sort-asc::after {
        content: '↑';
        position: absolute;
        right: 8px;
        color: #007bff;
        font-weight: bold;
    }

    .table th.sort-desc::after {
        content: '↓';
        position: absolute;
        right: 8px;
        color: #007bff;
        font-weight: bold;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .col-xl-3 {
            margin-bottom: 2rem;
        }

        .table-responsive {
            font-size: 0.85rem;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.25rem;
        }

        .page-header-actions {
            margin-top: 1rem;
        }
    }

    /* Print styles */
    @media print {

        .col-xl-3,
        .btn-group,
        .pagination,
        .card-footer,
        .page-header-actions {
            display: none !important;
        }

        .col-xl-9 {
            width: 100% !important;
            max-width: 100% !important;
        }

        .table {
            font-size: 10px !important;
        }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>