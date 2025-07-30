/**
 * Reports JavaScript - Sistema CDR
 * Seguindo o padrão das outras páginas
 */

class ReportsManager {
    constructor() {
        this.currentPage = 1;
        this.currentSort = { column: 'callstart', order: 'desc' };
        this.currentFilters = {};
        this.visibleColumns = ['callstart', 'caller', 'callednumber', 'duration', 'extension', 'user_name', 'call_type', 'call_status'];
        this.reportData = null;

        this.init();
    }

    init() {
        console.log('📊 Iniciando ReportsManager...');
        this.bindEvents();
        this.initializeFilters();
        this.showToast('Sistema de relatórios carregado', 'success');
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
                this.showToast('Relatório gerado com sucesso', 'success');

                console.log('✅ Relatório gerado:', result.data);
            } else {
                throw new Error(result.error || 'Erro desconhecido');
            }

        } catch (error) {
            console.error('❌ Erro ao gerar relatório:', error);
            this.showToast('Erro ao gerar relatório: ' + error.message, 'error');
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
        const columns = [
            { key: 'callstart', label: 'Data/Hora' },
            { key: 'caller', label: 'Origem' },
            { key: 'callednumber', label: 'Destino' },
            { key: 'duration', label: 'Duração' },
            { key: 'extension', label: 'Ramal' },
            { key: 'user_name', label: 'Usuário' },
            { key: 'call_type', label: 'Tipo' },
            { key: 'call_status', label: 'Status' }
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

        const columns = [
            { key: 'callstart', label: 'Data/Hora' },
            { key: 'caller', label: 'Origem' },
            { key: 'callednumber', label: 'Destino' },
            { key: 'duration', label: 'Duração' },
            { key: 'extension', label: 'Ramal' },
            { key: 'user_name', label: 'Usuário' },
            { key: 'call_type', label: 'Tipo' },
            { key: 'call_status', label: 'Status' }
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
            case 'interna': return 'bg-success';
            case 'saída': return 'bg-primary';
            case 'entrada': return 'bg-info';
            default: return 'bg-secondary';
        }
    }

    getCallStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'atendida': return 'bg-success';
            case 'não atendida': return 'bg-danger';
            case 'ocupado': return 'bg-warning';
            case 'falha': return 'bg-danger';
            default: return 'bg-secondary';
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
            this.showToast('Erro ao carregar detalhes da chamada', 'error');
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
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-tags me-2"></i>Classificação
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">Tipo:</td>
                            <td><span class="badge ${this.getCallTypeClass(call.call_type)}">${this.escapeHtml(call.call_type)}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status:</td>
                            <td><span class="badge ${this.getCallStatusClass(call.call_status)}">${this.escapeHtml(call.call_status)}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tipo de Destino:</td>
                            <td>${this.escapeHtml(call.destination_type)}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-cogs me-2"></i>Dados Técnicos
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">P1 Device:</td>
                            <td class="font-monospace small">${this.escapeHtml(call.p1device)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">P2 Device:</td>
                            <td class="font-monospace small">${this.escapeHtml(call.p2device)}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">SMDR Time:</td>
                            <td class="font-monospace small">${this.escapeHtml(call.smdrtime)}</td>
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

        this.showToast('Configuração de colunas aplicada', 'success');
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
                this.showToast('Gere um relatório primeiro antes de exportar', 'warning');
                return;
            }

            const filters = { ...this.currentFilters };
            filters.format = format;

            this.showToast('Preparando exportação...', 'info');

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

            this.showToast(`Relatório exportado em ${format.toUpperCase()}`, 'success');

        } catch (error) {
            console.error('❌ Erro ao exportar:', error);
            this.showToast('Erro ao exportar relatório', 'error');
        }
    }

    printReport() {
        if (!this.reportData) {
            this.showToast('Gere um relatório primeiro antes de imprimir', 'warning');
            return;
        }

        window.print();
        this.showToast('Relatório enviado para impressão', 'success');
    }

    // Filtros salvos
    saveFilters() {
        const filters = this.collectFilters();
        const name = prompt('Nome para este conjunto de filtros:');

        if (name && name.trim()) {
            const savedFilters = this.getSavedFilters();
            savedFilters[name.trim()] = filters;
            localStorage.setItem('cdr_saved_filters', JSON.stringify(savedFilters));

            this.showToast('Filtros salvos com sucesso', 'success');
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
            this.showToast('Filtros resetados', 'success');
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

    showToast(message, type = 'info') {
        // Implementar sistema de toast usando Bootstrap ou similar
        console.log(`${type.toUpperCase()}: ${message}`);

        // Se você tiver Toastr ou similar configurado, use aqui
        if (window.toastr) {
            window.toastr[type](message);
        } else if (window.Swal) {
            window.Swal.fire({
                icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'success',
                title: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            // Fallback para alert simples
            if (type === 'error') {
                alert('Erro: ' + message);
            }
        }
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
document.addEventListener('DOMContentLoaded', function () {
    // Verificar se estamos na página de relatórios
    if (document.getElementById('reportFiltersForm')) {
        window.reportsManager = new ReportsManager();
        console.log('📊 ReportsManager inicializado com sucesso!');
    }
});

// Funções globais para compatibilidade
function generateReport() {
    if (window.reportsManager) {
        window.reportsManager.generateReport();
    }
}

function resetFilters() {
    if (window.reportsManager) {
        window.reportsManager.resetFilters();
    }
}

function saveFilters() {
    if (window.reportsManager) {
        window.reportsManager.saveFilters();
    }
}

function exportReport(format) {
    if (window.reportsManager) {
        window.reportsManager.exportReport(format);
    }
}

function printReport() {
    if (window.reportsManager) {
        window.reportsManager.printReport();
    }
}

console.log('📊 Reports.js carregado com sucesso!');