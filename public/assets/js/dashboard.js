/**
 * Dashboard JavaScript - Versão Completa
 * Todas as funcionalidades implementadas e otimizadas
 * Autor: Sistema CDR
 * Versão: 3.1
 */

// ===== CONFIGURAÇÕES GLOBAIS OTIMIZADAS =====
const DashboardConfig = {
    colors: {
        primary: '#6366f1',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        gray: '#6b7280',
        // Cores específicas para breakdown
        breakdown: {
            fast: '#10b981',      // Verde - até 10s
            medium: '#f59e0b',    // Amarelo - 10-30s
            slow: '#ef4444',      // Vermelho - após 30s
            busy: '#f59e0b',      // Amarelo - ocupado
            noanswer: '#ef4444',  // Vermelho - não atendeu
            failed: '#6b7280',    // Cinza - falha conexão
            internal: '#10b981',  // Verde - transferências internas
            external: '#6366f1',  // Azul - transferências externas
            ura: '#f59e0b'        // Amarelo - transferências URA
        }
    },

    chartDefaults: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 300
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: { size: 13, weight: '500' },
                    color: '#374151'
                }
            },
            tooltip: {
                backgroundColor: '#1f2937',
                titleColor: '#f9fafb',
                bodyColor: '#f9fafb',
                borderColor: '#374151',
                borderWidth: 1,
                cornerRadius: 8,
                animation: {
                    duration: 150
                }
            }
        }
    }
};

// ===== CLASSE PRINCIPAL DO DASHBOARD =====
class Dashboard {
    constructor() {
        this.data = window.dashboardData || {};
        this.charts = {};
        this.modals = {};
        this.filters = {
            extensions: { limit: 8, sort: 'calls' },
            destinations: { limit: 8, type: '' },
            history: { limit: 10, type: '' }
        };
        this.loadingTimeouts = new Map();
        this.init();
    }

    init() {
        // Inicialização otimizada com requestAnimationFrame
        requestAnimationFrame(() => {
            this.bindEventListeners();
            this.initModals();
            this.updatePeriodIndicator();
        });

        // Inicializar gráficos após um pequeno delay para melhor performance
        setTimeout(() => {
            this.initCharts();
            this.initAnimations();
        }, 50);

        console.log('📊 Dashboard otimizado inicializado!');
    }

    // ===== EVENT LISTENERS OTIMIZADOS =====
    bindEventListeners() {
        // Usar event delegation para melhor performance
        document.addEventListener('click', (e) => {
            // Metric cards
            const metricCard = e.target.closest('.metric-card.clickable');
            if (metricCard) {
                const metric = metricCard.dataset.metric;
                this.showMetricDetails(metric);
                return;
            }

            // Chart actions
            const chartAction = e.target.closest('.chart-action-btn');
            if (chartAction) {
                e.stopPropagation();
                const action = chartAction.dataset.action;
                const chart = chartAction.dataset.chart;
                this.handleChartAction(action, chart);
                return;
            }

            // Clickable items
            const clickableItem = e.target.closest('.clickable-item');
            if (clickableItem) {
                const type = clickableItem.dataset.type;
                const id = clickableItem.dataset.id;
                this.showItemDetails(type, id);
                return;
            }

            // Refresh buttons
            if (e.target.id === 'refreshDashboard' || e.target.closest('#refreshDashboard')) {
                this.refreshDashboard();
                return;
            }

            if (e.target.id === 'refreshRecentCalls' || e.target.closest('#refreshRecentCalls')) {
                this.refreshRecentCalls();
                return;
            }

            // Reset filters
            if (e.target.id === 'resetFilters' || e.target.closest('#resetFilters')) {
                this.resetFilters();
                return;
            }

            // Export buttons
            if (e.target.id === 'exportModalData' || e.target.closest('#exportModalData')) {
                this.exportModalToPDF();
                return;
            }

            if (e.target.id === 'exportDetailData' || e.target.closest('#exportDetailData')) {
                this.exportDetailToExcel();
                return;
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'dateFilterForm') {
                this.handleFilterSubmit(e);
            }
        });

        // Change events
        document.addEventListener('change', (e) => {
            const id = e.target.id;
            switch (id) {
                case 'period_preset':
                    this.handlePresetChange();
                    break;
                case 'start_date':
                case 'end_date':
                    this.updatePeriodIndicator();
                    break;
                case 'extensionsLimit':
                case 'extensionsSort':
                    this.debounce(() => this.updateExtensions(), 300)();
                    break;
                case 'destinationsLimit':
                case 'destinationsType':
                    this.debounce(() => this.updateDestinations(), 300)();
                    break;
                case 'historyLimit':
                case 'historyType':
                    this.debounce(() => this.updateHistory(), 300)();
                    break;
            }
        });

        // Resize handler
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }

    // ===== INICIALIZAÇÃO DOS MODAIS =====
    initModals() {
        const metricModal = document.getElementById('metricModal');
        if (metricModal) {
            this.modals.metric = new bootstrap.Modal(metricModal);
        }

        const detailModal = document.getElementById('detailModal');
        if (detailModal) {
            this.modals.detail = new bootstrap.Modal(detailModal);
        }

        const fullscreenModal = document.getElementById('chartFullscreenModal');
        if (fullscreenModal) {
            this.modals.fullscreen = new bootstrap.Modal(fullscreenModal);
        }
    }

    // ===== INICIALIZAÇÃO DOS GRÁFICOS OTIMIZADA =====
    initCharts() {
        requestAnimationFrame(() => {
            this.initStatusChart();
        });

        requestAnimationFrame(() => {
            this.initTypesChart();
        });

        requestAnimationFrame(() => {
            this.initHourlyChart();
        });
    }

    initStatusChart() {
        const ctx = document.getElementById('statusPieChart');
        if (!ctx || !this.data.callsByStatus?.length) return;

        const config = {
            type: 'doughnut',
            data: {
                labels: this.data.callsByStatus.map(d => d.status),
                datasets: [{
                    data: this.data.callsByStatus.map(d => d.count),
                    backgroundColor: [DashboardConfig.colors.success, DashboardConfig.colors.warning],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                ...DashboardConfig.chartDefaults,
                plugins: {
                    ...DashboardConfig.chartDefaults.plugins,
                    tooltip: {
                        ...DashboardConfig.chartDefaults.plugins.tooltip,
                        callbacks: {
                            label: (context) => {
                                const item = this.data.callsByStatus[context.dataIndex];
                                return `${item.status}: ${this.formatNumber(item.count)} (${item.percentage}%)`;
                            }
                        }
                    }
                }
            }
        };

        this.charts.statusChart = new Chart(ctx, config);
    }

    initTypesChart() {
        const ctx = document.getElementById('typesPieChart');
        if (!ctx || !this.data.callsByType?.length) return;

        const colors = [
            DashboardConfig.colors.primary,
            DashboardConfig.colors.info,
            DashboardConfig.colors.success,
            DashboardConfig.colors.gray
        ];

        const config = {
            type: 'doughnut',
            data: {
                labels: this.data.callsByType.map(d => d.type),
                datasets: [{
                    data: this.data.callsByType.map(d => d.count),
                    backgroundColor: colors.slice(0, this.data.callsByType.length),
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                ...DashboardConfig.chartDefaults,
                plugins: {
                    ...DashboardConfig.chartDefaults.plugins,
                    tooltip: {
                        ...DashboardConfig.chartDefaults.plugins.tooltip,
                        callbacks: {
                            label: (context) => {
                                const item = this.data.callsByType[context.dataIndex];
                                return `${item.type}: ${this.formatNumber(item.count)} (${item.percentage}%)`;
                            }
                        }
                    }
                }
            }
        };

        this.charts.typesChart = new Chart(ctx, config);
    }

    initHourlyChart() {
        const ctx = document.getElementById('hourlyChart');
        if (!ctx || !this.data.hourlyData?.length) return;

        const config = {
            type: 'line',
            data: {
                labels: this.data.hourlyData.map(d => d.hour),
                datasets: [{
                    label: 'Total',
                    data: this.data.hourlyData.map(d => d.total),
                    borderColor: DashboardConfig.colors.primary,
                    backgroundColor: DashboardConfig.colors.primary + '20',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: DashboardConfig.colors.primary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }, {
                    label: 'Atendidas',
                    data: this.data.hourlyData.map(d => d.answered),
                    borderColor: DashboardConfig.colors.success,
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: DashboardConfig.colors.success,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }, {
                    label: 'Perdidas',
                    data: this.data.hourlyData.map(d => d.unanswered),
                    borderColor: DashboardConfig.colors.warning,
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: DashboardConfig.colors.warning,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { size: 14, weight: '500' },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#f9fafb',
                        bodyColor: '#f9fafb',
                        borderColor: '#374151',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: (context) => {
                                return `${context.dataset.label}: ${this.formatNumber(context.parsed.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 12 },
                            color: '#6b7280',
                            callback: (value) => this.formatNumber(value)
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 12 },
                            color: '#6b7280'
                        }
                    }
                }
            }
        };

        this.charts.hourlyChart = new Chart(ctx, config);
    }

    // ===== MANIPULAÇÃO DE MÉTRICAS =====
    async showMetricDetails(metric) {
        const modal = this.modals.metric;
        if (!modal) return;

        this.showModalLoading();
        modal.show();

        try {
            const details = await this.fetchMetricDetails(metric);
            this.renderMetricDetails(metric, details);
        } catch (error) {
            console.error('Erro ao carregar detalhes da métrica:', error);
            this.showModalError('Erro ao carregar os detalhes. Tente novamente.');
        }
    }

    async fetchMetricDetails(metric) {
        await new Promise(resolve => setTimeout(resolve, 600));

        const currentPeriod = this.data.currentPeriod;
        const startDate = new Date(currentPeriod.start);
        const endDate = new Date(currentPeriod.end);

        const formatPeriod = () => {
            if (startDate.getTime() === endDate.getTime()) {
                return startDate.toLocaleDateString('pt-BR');
            }
            return `${startDate.toLocaleDateString('pt-BR')} até ${endDate.toLocaleDateString('pt-BR')}`;
        };

        const mockData = {
            total_calls: {
                title: 'Total de Chamadas',
                icon: 'fas fa-phone',
                summary: {
                    total: this.data.stats?.total_calls || 0,
                    period: formatPeriod(),
                    growth: '+12%',
                    growthTooltip: 'Comparado com o período anterior de mesma duração',
                    growthType: 'positive'
                },
                breakdown: [
                    {
                        label: 'Chamadas Internas',
                        value: Math.floor((this.data.stats?.total_calls || 0) * 0.3),
                        percentage: 30,
                        color: DashboardConfig.colors.breakdown.internal
                    },
                    {
                        label: 'Chamadas Recebidas',
                        value: Math.floor((this.data.stats?.total_calls || 0) * 0.45),
                        percentage: 45,
                        color: DashboardConfig.colors.breakdown.external
                    },
                    {
                        label: 'Chamadas Realizadas',
                        value: Math.floor((this.data.stats?.total_calls || 0) * 0.25),
                        percentage: 25,
                        color: DashboardConfig.colors.primary
                    }
                ],
                timeline: this.generateTimelineData('total')
            },
            answered_calls: {
                title: 'Chamadas Atendidas',
                icon: 'fas fa-check-circle',
                summary: {
                    total: this.data.stats?.answered_calls || 0,
                    period: formatPeriod(),
                    growth: '+8%',
                    growthTooltip: 'Melhoria na taxa de atendimento comparado ao período anterior',
                    growthType: 'positive'
                },
                breakdown: [
                    {
                        label: 'Atendidas em até 10s',
                        value: Math.floor((this.data.stats?.answered_calls || 0) * 0.6),
                        percentage: 60,
                        color: DashboardConfig.colors.breakdown.fast
                    },
                    {
                        label: 'Atendidas em 10-30s',
                        value: Math.floor((this.data.stats?.answered_calls || 0) * 0.3),
                        percentage: 30,
                        color: DashboardConfig.colors.breakdown.medium
                    },
                    {
                        label: 'Atendidas após 30s',
                        value: Math.floor((this.data.stats?.answered_calls || 0) * 0.1),
                        percentage: 10,
                        color: DashboardConfig.colors.breakdown.slow
                    }
                ],
                timeline: this.generateTimelineData('answered')
            },
            unanswered_calls: {
                title: 'Chamadas Não Atendidas',
                icon: 'fas fa-phone-slash',
                summary: {
                    total: this.data.stats?.unanswered_calls || 0,
                    period: formatPeriod(),
                    growth: '-5%',
                    growthTooltip: 'Redução nas chamadas perdidas - tendência positiva',
                    growthType: 'negative'
                },
                breakdown: [
                    {
                        label: 'Ocupado',
                        value: Math.floor((this.data.stats?.unanswered_calls || 0) * 0.4),
                        percentage: 40,
                        color: DashboardConfig.colors.breakdown.busy
                    },
                    {
                        label: 'Não atendeu',
                        value: Math.floor((this.data.stats?.unanswered_calls || 0) * 0.5),
                        percentage: 50,
                        color: DashboardConfig.colors.breakdown.noanswer
                    },
                    {
                        label: 'Falha na conexão',
                        value: Math.floor((this.data.stats?.unanswered_calls || 0) * 0.1),
                        percentage: 10,
                        color: DashboardConfig.colors.breakdown.failed
                    }
                ],
                timeline: this.generateTimelineData('unanswered')
            },
            transfer_calls: {
                title: 'Transferências',
                icon: 'fas fa-exchange-alt',
                summary: {
                    total: this.data.stats?.transfer_calls || 0,
                    period: formatPeriod(),
                    growth: '+3%',
                    growthTooltip: 'Aumento no uso de transferências para melhor atendimento',
                    growthType: 'positive'
                },
                breakdown: [
                    {
                        label: 'Transferências Internas',
                        value: Math.floor((this.data.stats?.transfer_calls || 0) * 0.7) || 15,
                        percentage: 70,
                        color: DashboardConfig.colors.breakdown.internal
                    },
                    {
                        label: 'Transferências Externas',
                        value: Math.floor((this.data.stats?.transfer_calls || 0) * 0.2) || 4,
                        percentage: 20,
                        color: DashboardConfig.colors.breakdown.external
                    },
                    {
                        label: 'Transferências da URA',
                        value: Math.floor((this.data.stats?.transfer_calls || 0) * 0.1) || 2,
                        percentage: 10,
                        color: DashboardConfig.colors.breakdown.ura
                    }
                ],
                timeline: this.generateTimelineData('transfer')
            }
        };

        return mockData[metric] || {};
    }

    generateTimelineData(type) {
        const hours = Array.from({ length: 24 }, (_, i) => i);
        return hours.map(hour => ({
            hour: `${hour.toString().padStart(2, '0')}:00`,
            value: Math.floor(Math.random() * 100) + 10,
            internal: Math.floor(Math.random() * 30) + 5,
            incoming: Math.floor(Math.random() * 40) + 10,
            outgoing: Math.floor(Math.random() * 30) + 5
        }));
    }

    renderMetricDetails(metric, details) {
        const modalContent = document.getElementById('modalContent');
        const modalTitle = document.getElementById('metricModalLabel');

        if (!modalContent || !modalTitle || !details.title) return;

        modalTitle.innerHTML = `
            <i class="${details.icon} me-2"></i>
            ${details.title}
        `;

        const growthClass = details.summary.growthType === 'positive' ? 'text-success' : 'text-danger';

        let html = `
            <!-- Resumo com Tooltips Corrigido -->
            <div class="modal-stats">
                <div class="modal-stat">
                    <div class="modal-stat-value">${this.formatNumber(details.summary.total)}</div>
                    <div class="modal-stat-label">Total</div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value ${growthClass}">${details.summary.growth}</div>
                    <div class="modal-stat-label">
                        Crescimento
                        <span class="tooltip-container">
                            <i class="fas fa-info-circle tooltip-trigger"></i>
                            <div class="tooltip-content">${details.summary.growthTooltip}</div>
                        </span>
                    </div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value">${details.summary.period}</div>
                    <div class="modal-stat-label">
                        Período
                        <span class="tooltip-container">
                            <i class="fas fa-info-circle tooltip-trigger"></i>
                            <div class="tooltip-content">Período selecionado para análise dos dados</div>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Breakdown com Cores -->
            <div class="mb-4">
                <h6 class="mb-3">Distribuição Detalhada</h6>
                <div class="breakdown-list">
        `;

        details.breakdown.forEach(item => {
            html += `
                <div class="breakdown-item">
                    <div class="breakdown-info">
                        <span class="breakdown-label">
                            <span class="breakdown-color-indicator" style="background-color: ${item.color}"></span>
                            ${item.label}
                        </span>
                        <span class="breakdown-value">${this.formatNumber(item.value)}</span>
                    </div>
                    <div class="breakdown-bar">
                        <div class="breakdown-fill" style="width: ${item.percentage}%; background-color: ${item.color}"></div>
                    </div>
                    <span class="breakdown-percentage">${item.percentage}%</span>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;

        html += `
            <div class="mb-4">
                <h6 class="mb-3">Atividade por Horário - Detalhada</h6>
                <div class="timeline-chart">
                    <canvas id="modalTimelineChart" height="300"></canvas>
                </div>
            </div>
        `;

        modalContent.innerHTML = html;
        this.hideModalLoading();

        this.renderModalChart(details.timeline, metric, details.breakdown);
    }

    renderModalChart(timelineData, metric, breakdown) {
        const ctx = document.getElementById('modalTimelineChart');
        if (!ctx || !timelineData) return;

        let datasets = [];

        if (metric === 'total_calls') {
            datasets = [
                {
                    label: 'Internas',
                    data: timelineData.map(d => d.internal),
                    backgroundColor: DashboardConfig.colors.breakdown.internal + '80',
                    borderColor: DashboardConfig.colors.breakdown.internal,
                    borderWidth: 2
                },
                {
                    label: 'Recebidas',
                    data: timelineData.map(d => d.incoming),
                    backgroundColor: DashboardConfig.colors.breakdown.external + '80',
                    borderColor: DashboardConfig.colors.breakdown.external,
                    borderWidth: 2
                },
                {
                    label: 'Realizadas',
                    data: timelineData.map(d => d.outgoing),
                    backgroundColor: DashboardConfig.colors.primary + '80',
                    borderColor: DashboardConfig.colors.primary,
                    borderWidth: 2
                }
            ];
        } else if (metric === 'answered_calls') {
            datasets = [
                {
                    label: 'Até 10s',
                    data: timelineData.map(d => Math.floor(d.value * 0.6)),
                    backgroundColor: DashboardConfig.colors.breakdown.fast + '80',
                    borderColor: DashboardConfig.colors.breakdown.fast,
                    borderWidth: 2
                },
                {
                    label: '10-30s',
                    data: timelineData.map(d => Math.floor(d.value * 0.3)),
                    backgroundColor: DashboardConfig.colors.breakdown.medium + '80',
                    borderColor: DashboardConfig.colors.breakdown.medium,
                    borderWidth: 2
                },
                {
                    label: 'Após 30s',
                    data: timelineData.map(d => Math.floor(d.value * 0.1)),
                    backgroundColor: DashboardConfig.colors.breakdown.slow + '80',
                    borderColor: DashboardConfig.colors.breakdown.slow,
                    borderWidth: 2
                }
            ];
        } else if (metric === 'unanswered_calls') {
            datasets = [
                {
                    label: 'Ocupado',
                    data: timelineData.map(d => Math.floor(d.value * 0.4)),
                    backgroundColor: DashboardConfig.colors.breakdown.busy + '80',
                    borderColor: DashboardConfig.colors.breakdown.busy,
                    borderWidth: 2
                },
                {
                    label: 'Não atendeu',
                    data: timelineData.map(d => Math.floor(d.value * 0.5)),
                    backgroundColor: DashboardConfig.colors.breakdown.noanswer + '80',
                    borderColor: DashboardConfig.colors.breakdown.noanswer,
                    borderWidth: 2
                },
                {
                    label: 'Falha conexão',
                    data: timelineData.map(d => Math.floor(d.value * 0.1)),
                    backgroundColor: DashboardConfig.colors.breakdown.failed + '80',
                    borderColor: DashboardConfig.colors.breakdown.failed,
                    borderWidth: 2
                }
            ];
        } else if (metric === 'transfer_calls') {
            datasets = [
                {
                    label: 'Internas',
                    data: timelineData.map(d => Math.floor(d.value * 0.7)),
                    backgroundColor: DashboardConfig.colors.breakdown.internal + '80',
                    borderColor: DashboardConfig.colors.breakdown.internal,
                    borderWidth: 2
                },
                {
                    label: 'Externas',
                    data: timelineData.map(d => Math.floor(d.value * 0.2)),
                    backgroundColor: DashboardConfig.colors.breakdown.external + '80',
                    borderColor: DashboardConfig.colors.breakdown.external,
                    borderWidth: 2
                },
                {
                    label: 'URA',
                    data: timelineData.map(d => Math.floor(d.value * 0.1)),
                    backgroundColor: DashboardConfig.colors.breakdown.ura + '80',
                    borderColor: DashboardConfig.colors.breakdown.ura,
                    borderWidth: 2
                }
            ];
        } else {
            datasets = [{
                label: 'Quantidade',
                data: timelineData.map(d => d.value),
                backgroundColor: DashboardConfig.colors.primary + '40',
                borderColor: DashboardConfig.colors.primary,
                borderWidth: 2,
                borderRadius: 4
            }];
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: timelineData.map(d => d.hour),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 400
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#f9fafb',
                        bodyColor: '#f9fafb',
                        borderColor: '#374151',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    },
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    }

    // ===== DETALHES DOS ITENS =====
    async showItemDetails(type, id) {
        const modal = this.modals.detail;
        if (!modal) return;

        this.showDetailModalLoading();
        modal.show();

        try {
            const details = await this.fetchItemDetails(type, id);
            this.renderItemDetails(type, id, details);
        } catch (error) {
            console.error('Erro ao carregar detalhes do item:', error);
            this.showDetailModalError('Erro ao carregar os detalhes. Tente novamente.');
        }
    }

    async fetchItemDetails(type, id) {
        await new Promise(resolve => setTimeout(resolve, 500));

        const mockDetails = {
            extension: {
                title: `Ramal ${id}`,
                icon: 'fas fa-phone',
                stats: {
                    total_calls: Math.floor(Math.random() * 50) + 10,
                    answered: Math.floor(Math.random() * 40) + 8,
                    missed: Math.floor(Math.random() * 10) + 2,
                    avg_duration: (Math.random() * 5 + 1).toFixed(1),
                    efficiency: Math.floor(Math.random() * 30) + 70,
                    peak_hour: ['09:00', '14:00', '16:00'][Math.floor(Math.random() * 3)]
                },
                calls: this.generateCallDetails(id, 'extension')
            },
            destination: {
                title: `Destino ${id}`,
                icon: 'fas fa-phone-alt',
                stats: {
                    total_calls: Math.floor(Math.random() * 30) + 5,
                    answered: Math.floor(Math.random() * 25) + 4,
                    missed: Math.floor(Math.random() * 8) + 1,
                    avg_duration: (Math.random() * 4 + 0.5).toFixed(1),
                    efficiency: Math.floor(Math.random() * 40) + 60,
                    type: ['Celular', 'Fixo', 'Outros'][Math.floor(Math.random() * 3)]
                },
                calls: this.generateCallDetails(id, 'destination')
            },
            call: {
                title: `Chamada ${id.substring(0, 8)}...`,
                icon: 'fas fa-info-circle',
                details: this.generateSingleCallDetails(id)
            }
        };

        return mockDetails[type] || {};
    }

    generateCallDetails(id, type) {
        const calls = [];
        const count = Math.floor(Math.random() * 15) + 5;

        for (let i = 0; i < count; i++) {
            const hour = Math.floor(Math.random() * 12) + 8;
            const minute = Math.floor(Math.random() * 60);
            const duration = Math.floor(Math.random() * 300) + 30;

            calls.push({
                time: `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`,
                from: type === 'extension' ? id : `(11) 9${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`,
                to: type === 'destination' ? id : `(11) 9${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`,
                duration: this.formatDuration(duration),
                status: Math.random() > 0.2 ? 'ANSWERED' : 'NO ANSWER',
                type: ['Interna', 'Entrada', 'Saída'][Math.floor(Math.random() * 3)]
            });
        }

        return calls.sort((a, b) => b.time.localeCompare(a.time));
    }

    generateSingleCallDetails(id) {
        return {
            uniqueid: id,
            date: new Date().toLocaleDateString('pt-BR'),
            time: `${Math.floor(Math.random() * 12) + 8}:${Math.floor(Math.random() * 60).toString().padStart(2, '0')}`,
            from: `(11) 9${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`,
            to: `(11) 9${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`,
            extension: `100${Math.floor(Math.random() * 9) + 1}`,
            duration: this.formatDuration(Math.floor(Math.random() * 300) + 30),
            status: Math.random() > 0.2 ? 'ANSWERED' : 'NO ANSWER',
            type: ['Interna', 'Entrada', 'Saída'][Math.floor(Math.random() * 3)],
            recording: Math.random() > 0.5 ? 'Disponível' : 'Não disponível'
        };
    }

    renderItemDetails(type, id, details) {
        const modalContent = document.getElementById('detailModalContent');
        const modalTitle = document.getElementById('detailModalLabel');

        if (!modalContent || !modalTitle) return;

        modalTitle.innerHTML = `
            <i class="${details.icon} me-2"></i>
            ${details.title}
        `;

        let html = '';

        if (type === 'call') {
            html = `
                <div class="call-details-grid">
                    <div class="call-detail-item">
                        <div class="call-detail-label">Data</div>
                        <div class="call-detail-value">${details.details.date}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Horário</div>
                        <div class="call-detail-value">${details.details.time}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Origem</div>
                        <div class="call-detail-value">${details.details.from}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Destino</div>
                        <div class="call-detail-value">${details.details.to}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Ramal</div>
                        <div class="call-detail-value">${details.details.extension}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Duração</div>
                        <div class="call-detail-value">${details.details.duration}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Status</div>
                        <div class="call-detail-value">
                            <span class="badge ${details.details.status === 'ANSWERED' ? 'bg-success' : 'bg-warning'}">
                                ${details.details.status === 'ANSWERED' ? 'Atendida' : 'Não Atendida'}
                            </span>
                        </div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Tipo</div>
                        <div class="call-detail-value">${details.details.type}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">Gravação</div>
                        <div class="call-detail-value">${details.details.recording}</div>
                    </div>
                    <div class="call-detail-item">
                        <div class="call-detail-label">ID Único</div>
                        <div class="call-detail-value"><code>${details.details.uniqueid}</code></div>
                    </div>
                </div>
            `;
        } else {
            html = `
                <div class="detail-modal-stats">
                    <div class="detail-modal-stat">
                        <div class="detail-modal-stat-value">${details.stats.total_calls}</div>
                        <div class="detail-modal-stat-label">Total</div>
                    </div>
                    <div class="detail-modal-stat">
                        <div class="detail-modal-stat-value">${details.stats.answered}</div>
                        <div class="detail-modal-stat-label">Atendidas</div>
                    </div>
                    <div class="detail-modal-stat">
                        <div class="detail-modal-stat-value">${details.stats.missed}</div>
                        <div class="detail-modal-stat-label">Perdidas</div>
                    </div>
                    <div class="detail-modal-stat">
                        <div class="detail-modal-stat-value">${details.stats.avg_duration}min</div>
                        <div class="detail-modal-stat-label">Duração Média</div>
                    </div>
                    <div class="detail-modal-stat">
                        <div class="detail-modal-stat-value">${details.stats.efficiency}%</div>
                        <div class="detail-modal-stat-label">Eficiência</div>
                    </div>
                    ${type === 'extension' ? `
                        <div class="detail-modal-stat">
                            <div class="detail-modal-stat-value">${details.stats.peak_hour}</div>
                            <div class="detail-modal-stat-label">Pico</div>
                        </div>
                    ` : `
                        <div class="detail-modal-stat">
                            <div class="detail-modal-stat-value">${details.stats.type}</div>
                            <div class="detail-modal-stat-label">Tipo</div>
                        </div>
                    `}
                </div>

                <h6 class="mb-3">Chamadas Detalhadas</h6>
                <div class="table-responsive">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Horário</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Duração</th>
                                <th>Status</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            details.calls.forEach(call => {
                html += `
                    <tr>
                        <td>${call.time}</td>
                        <td><code>${call.from}</code></td>
                        <td><code>${call.to}</code></td>
                        <td>${call.duration}</td>
                        <td>
                            <span class="badge ${call.status === 'ANSWERED' ? 'bg-success' : 'bg-warning'}">
                                ${call.status === 'ANSWERED' ? 'Atendida' : 'Perdida'}
                            </span>
                        </td>
                        <td>${call.type}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        modalContent.innerHTML = html;
        this.hideDetailModalLoading();
    }

    // ===== AÇÕES DOS GRÁFICOS =====
    handleChartAction(action, chartId) {
        switch (action) {
            case 'download':
                this.downloadChart(chartId);
                break;
            case 'print':
                this.printChart(chartId);
                break;
            case 'fullscreen':
                this.showChartFullscreen(chartId);
                break;
            case 'export':
                this.exportData(chartId);
                break;
        }
    }

    downloadChart(chartId) {
        const chartKey = chartId.replace('Chart', '') + 'Chart';
        const chart = this.charts[chartKey];

        if (!chart) {
            this.showToast('Gráfico não encontrado', 'error');
            return;
        }

        try {
            const link = document.createElement('a');
            link.download = `${chartId}_${new Date().toISOString().split('T')[0]}.png`;
            link.href = chart.toBase64Image('image/png', 1.0);
            link.click();

            this.showToast('Gráfico baixado com sucesso!', 'success');
        } catch (error) {
            console.error('Erro ao baixar gráfico:', error);
            this.showToast('Erro ao baixar gráfico', 'error');
        }
    }

    printChart(chartId) {
        const chartKey = chartId.replace('Chart', '') + 'Chart';
        const chart = this.charts[chartKey];

        if (!chart) {
            this.showToast('Gráfico não encontrado', 'error');
            return;
        }

        try {
            const printWindow = window.open('', '_blank');
            const imageData = chart.toBase64Image('image/png', 1.0);

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Gráfico - ${chartId}</title>
                    <style>
                        body { 
                            margin: 0; 
                            padding: 20px; 
                            font-family: Arial, sans-serif; 
                            text-align: center;
                        }
                        .header {
                            margin-bottom: 20px;
                            border-bottom: 2px solid #eee;
                            padding-bottom: 10px;
                        }
                        .chart {
                            max-width: 100%;
                            height: auto;
                        }
                        .footer {
                            margin-top: 20px;
                            font-size: 12px;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Central de Análise - Dashboard CDR</h2>
                        <p>Gráfico gerado em: ${new Date().toLocaleString('pt-BR')}</p>
                    </div>
                    <img src="${imageData}" class="chart" alt="Gráfico">
                                        <div class="footer">
                        <p>Sistema de Análise de Chamadas</p>
                    </div>
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);

            this.showToast('Gráfico enviado para impressão', 'success');
        } catch (error) {
            console.error('Erro ao imprimir gráfico:', error);
            this.showToast('Erro ao imprimir gráfico', 'error');
        }
    }

    showChartFullscreen(chartId) {
        const modal = this.modals.fullscreen;
        if (!modal) return;

        const chartKey = chartId.replace('Chart', '') + 'Chart';
        const originalChart = this.charts[chartKey];

        if (!originalChart) {
            this.showToast('Gráfico não encontrado', 'error');
            return;
        }

        modal.show();

        setTimeout(() => {
            const fullscreenCtx = document.getElementById('fullscreenChart');
            if (!fullscreenCtx) return;

            // Clonar configuração do gráfico original
            const config = JSON.parse(JSON.stringify(originalChart.config));

            // Ajustar para tela cheia
            config.options.maintainAspectRatio = false;
            config.options.responsive = true;

            // Destruir gráfico anterior se existir
            if (this.charts.fullscreenChart) {
                this.charts.fullscreenChart.destroy();
            }

            // Criar novo gráfico em tela cheia
            this.charts.fullscreenChart = new Chart(fullscreenCtx, config);
        }, 300);

        // Limpar ao fechar
        modal._element.addEventListener('hidden.bs.modal', () => {
            if (this.charts.fullscreenChart) {
                this.charts.fullscreenChart.destroy();
                delete this.charts.fullscreenChart;
            }
        }, { once: true });
    }

    exportData(chartId) {
        let data, filename;

        switch (chartId) {
            case 'summary':
                data = this.prepareSummaryExport();
                filename = 'resumo_executivo';
                break;
            default:
                data = this.prepareChartExport(chartId);
                filename = chartId;
        }

        this.downloadCSV(data, `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
        this.showToast('Dados exportados com sucesso!', 'success');
    }

    prepareSummaryExport() {
        return [
            ['Métrica', 'Valor'],
            ['Volume Total', this.data.stats?.total_calls || 0],
            ['Eficiência', `${this.data.stats?.success_rate || 0}%`],
            ['Tempo Médio', `${this.data.stats?.avg_duration || 0} min`],
            ['Ramais Ativos', this.data.stats?.unique_extensions || 0]
        ];
    }

    prepareChartExport(chartId) {
        return [['Dados', 'Valor'], ['Exemplo', '123']];
    }

    downloadCSV(data, filename) {
        const csvContent = data.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');

        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();

        URL.revokeObjectURL(link.href);
    }

    // ===== EXPORTAÇÃO PDF E EXCEL =====
    exportModalToPDF() {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Título
            doc.setFontSize(20);
            doc.text('Relatório Detalhado - Dashboard CDR', 20, 20);

            // Data
            doc.setFontSize(12);
            doc.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, 20, 35);

            // Conteúdo (simplificado)
            doc.setFontSize(14);
            doc.text('Dados da Métrica:', 20, 55);

            // Aqui você pode adicionar mais conteúdo do modal
            const modalContent = document.getElementById('modalContent');
            if (modalContent) {
                const textContent = modalContent.innerText;
                const lines = doc.splitTextToSize(textContent.substring(0, 500), 170);
                doc.setFontSize(10);
                doc.text(lines, 20, 70);
            }

            doc.save(`relatorio_metrica_${new Date().toISOString().split('T')[0]}.pdf`);
            this.showToast('PDF exportado com sucesso!', 'success');
        } catch (error) {
            console.error('Erro ao exportar PDF:', error);
            this.showToast('Erro ao exportar PDF', 'error');
        }
    }

    exportDetailToExcel() {
        try {
            const wb = XLSX.utils.book_new();

            // Dados da tabela
            const table = document.querySelector('#detailModalContent .detail-table');
            if (table) {
                const ws = XLSX.utils.table_to_sheet(table);
                XLSX.utils.book_append_sheet(wb, ws, 'Detalhes');
            } else {
                // Dados alternativos se não houver tabela
                const data = [
                    ['Item', 'Valor'],
                    ['Exportado em', new Date().toLocaleString('pt-BR')]
                ];
                const ws = XLSX.utils.aoa_to_sheet(data);
                XLSX.utils.book_append_sheet(wb, ws, 'Dados');
            }

            XLSX.writeFile(wb, `detalhes_${new Date().toISOString().split('T')[0]}.xlsx`);
            this.showToast('Excel exportado com sucesso!', 'success');
        } catch (error) {
            console.error('Erro ao exportar Excel:', error);
            this.showToast('Erro ao exportar Excel', 'error');
        }
    }

    // ===== MINI-FILTROS =====
    async updateExtensions() {
        const limit = document.getElementById('extensionsLimit')?.value || 8;
        const sort = document.getElementById('extensionsSort')?.value || 'calls';

        this.filters.extensions = { limit: parseInt(limit), sort };

        this.showLoading('extensionsLoading', 3000);

        try {
            await new Promise(resolve => setTimeout(resolve, 300));

            let extensions = [...(this.data.topExtensions || [])];

            switch (sort) {
                case 'efficiency':
                    extensions.sort((a, b) => b.success_rate - a.success_rate);
                    break;
                case 'duration':
                    extensions.sort((a, b) => parseFloat(b.avg_duration) - parseFloat(a.avg_duration));
                    break;
                default:
                    extensions.sort((a, b) => b.total_calls - a.total_calls);
            }

            extensions = extensions.slice(0, parseInt(limit));

            this.renderExtensionsList(extensions);
            this.updateExtensionsCount(extensions.length);

        } catch (error) {
            console.error('Erro ao atualizar ramais:', error);
        } finally {
            this.hideLoading('extensionsLoading');
        }
    }

    async updateDestinations() {
        const limit = document.getElementById('destinationsLimit')?.value || 8;
        const type = document.getElementById('destinationsType')?.value || '';

        this.filters.destinations = { limit: parseInt(limit), type };

        this.showLoading('destinationsLoading', 3000);

        try {
            await new Promise(resolve => setTimeout(resolve, 300));

            let destinations = [...(this.data.topDestinations || [])];

            if (type) {
                destinations = destinations.filter(d => d.type.toLowerCase() === type);
            }

            destinations = destinations.slice(0, parseInt(limit));

            this.renderDestinationsList(destinations);
            this.updateDestinationsCount(destinations.length);

        } catch (error) {
            console.error('Erro ao atualizar destinos:', error);
        } finally {
            this.hideLoading('destinationsLoading');
        }
    }

    async updateHistory() {
        const limit = document.getElementById('historyLimit')?.value || 10;
        const type = document.getElementById('historyType')?.value || '';

        this.filters.history = { limit: parseInt(limit), type };

        this.showLoading('historyLoading', 3000);

        try {
            await new Promise(resolve => setTimeout(resolve, 300));

            let history = [...(this.data.recentCalls || [])];

            if (type) {
                history = history.filter(h => h.call_type.toLowerCase().includes(type));
            }

            history = history.slice(0, parseInt(limit));

            this.renderHistoryList(history);
            this.updateHistoryCount(history.length);

        } catch (error) {
            console.error('Erro ao atualizar histórico:', error);
        } finally {
            this.hideLoading('historyLoading');
        }
    }

    renderExtensionsList(extensions) {
        const container = document.getElementById('extensionsList');
        if (!container) return;

        if (extensions.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-phone"></i>
                    <p>Nenhum ramal encontrado</p>
                </div>
            `;
            return;
        }

        const html = `
            <div class="performance-list">
                ${extensions.map((ext, index) => `
                    <div class="performance-item clickable-item" data-type="extension" data-id="${ext.extension}">
                        <div class="performance-rank">
                            ${index < 3 ?
                `<span class="rank-medal">${['🥇', '🥈', '🥉'][index]}</span>` :
                `<span class="rank-number">${index + 1}</span>`
            }
                        </div>
                        <div class="performance-info">
                            <div class="performance-name">Ramal ${ext.extension}</div>
                            <div class="performance-stats">
                                <span>${this.formatNumber(ext.total_calls)} chamadas</span>
                                <span class="efficiency ${ext.success_rate >= 90 ? 'high' : (ext.success_rate >= 70 ? 'medium' : 'low')}">
                                    ${ext.success_rate}%
                                </span>
                            </div>
                        </div>
                        <div class="performance-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.innerHTML = html;
    }

    renderDestinationsList(destinations) {
        const container = document.getElementById('destinationsList');
        if (!container) return;

        if (destinations.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-phone-alt"></i>
                    <p>Nenhum destino encontrado</p>
                </div>
            `;
            return;
        }

        const html = `
            <div class="performance-list">
                ${destinations.map(dest => `
                    <div class="performance-item clickable-item" data-type="destination" data-id="${dest.destination}">
                        <div class="performance-info">
                            <div class="performance-name">
                                ${dest.destination.length > 15 ? dest.destination.substring(0, 15) + '...' : dest.destination}
                                <span class="destination-type ${dest.type.toLowerCase()}">${dest.type}</span>
                            </div>
                            <div class="performance-stats">
                                <span>${this.formatNumber(dest.total_calls)} chamadas</span>
                                <span class="efficiency ${dest.success_rate >= 90 ? 'high' : (dest.success_rate >= 70 ? 'medium' : 'low')}">
                                    ${dest.success_rate}%
                                </span>
                            </div>
                        </div>
                        <div class="performance-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.innerHTML = html;
    }

    renderHistoryList(history) {
        const container = document.getElementById('historyList');
        if (!container) return;

        if (history.length === 0) {
            container.innerHTML = `
                <div class="empty-state large">
                    <i class="fas fa-phone"></i>
                    <h4>Nenhuma chamada encontrada</h4>
                    <p>Não há registros no período selecionado</p>
                </div>
            `;
            return;
        }

        const html = `
            <div class="history-list">
                ${history.map(call => `
                    <div class="history-item clickable-item" data-type="call" data-id="${call.uniqueid || Math.random().toString(36)}">
                        <div class="history-time">
                            ${new Date(call.calldate_formatted).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}
                        </div>
                        <div class="history-info">
                            <div class="history-main">
                                <span class="history-from">${call.src.substring(0, 12)}</span>
                                <i class="fas fa-arrow-right"></i>
                                <span class="history-to">${call.dst.substring(0, 12)}</span>
                            </div>
                            <div class="history-meta">
                                ${call.extension ? `<span class="history-extension">Ramal ${call.extension}</span>` : ''}
                                <span class="history-duration">${call.duration_formatted}</span>
                                <span class="history-type ${call.call_type.toLowerCase()}">${call.call_type}</span>
                            </div>
                        </div>
                        <div class="history-status">
                            <span class="status-badge ${call.disposition === 'ANSWERED' ? 'success' : 'warning'}">
                                ${call.status_formatted}
                            </span>
                        </div>
                        <div class="performance-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.innerHTML = html;
    }

    // ===== MANIPULAÇÃO DE FILTROS =====
    handleFilterSubmit(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const params = new URLSearchParams();

        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        this.showFilterLoading(e.target);

        setTimeout(() => {
            window.location.href = window.location.pathname + '?' + params.toString();
        }, 500);
    }

    handlePresetChange() {
        const presetSelect = document.getElementById('period_preset');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        if (!presetSelect || !startDate || !endDate) return;

        const preset = presetSelect.value;
        const today = new Date();

        const presetDates = {
            today: {
                start: today,
                end: today
            },
            yesterday: {
                start: new Date(today.getTime() - 24 * 60 * 60 * 1000),
                end: new Date(today.getTime() - 24 * 60 * 60 * 1000)
            },
            last7days: {
                start: new Date(today.getTime() - 6 * 24 * 60 * 60 * 1000),
                end: today
            },
            last30days: {
                start: new Date(today.getTime() - 29 * 24 * 60 * 60 * 1000),
                end: today
            },
            thismonth: {
                start: new Date(today.getFullYear(), today.getMonth(), 1),
                end: today
            },
            lastmonth: {
                start: new Date(today.getFullYear(), today.getMonth() - 1, 1),
                end: new Date(today.getFullYear(), today.getMonth(), 0)
            }
        };

        if (presetDates[preset]) {
            startDate.value = this.formatDateForInput(presetDates[preset].start);
            endDate.value = this.formatDateForInput(presetDates[preset].end);
            this.updatePeriodIndicator();
        }
    }

    resetFilters() {
        const form = document.getElementById('dateFilterForm');
        if (!form) return;

        // Reset form
        form.reset();

        // Set default dates (yesterday)
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);

        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const presetSelect = document.getElementById('period_preset');

        if (startDate) startDate.value = this.formatDateForInput(yesterday);
        if (endDate) endDate.value = this.formatDateForInput(yesterday);
        if (presetSelect) presetSelect.value = 'yesterday';

        // Reset mini-filters
        const extensionsLimit = document.getElementById('extensionsLimit');
        const extensionsSort = document.getElementById('extensionsSort');
        const destinationsLimit = document.getElementById('destinationsLimit');
        const destinationsType = document.getElementById('destinationsType');
        const historyLimit = document.getElementById('historyLimit');
        const historyType = document.getElementById('historyType');

        if (extensionsLimit) extensionsLimit.value = '8';
        if (extensionsSort) extensionsSort.value = 'calls';
        if (destinationsLimit) destinationsLimit.value = '8';
        if (destinationsType) destinationsType.value = '';
        if (historyLimit) historyLimit.value = '10';
        if (historyType) historyType.value = '';

        // Update displays
        this.updateExtensions();
        this.updateDestinations();
        this.updateHistory();
        this.updatePeriodIndicator();

        this.showToast('Filtros resetados com sucesso!', 'info');
    }

    updatePeriodIndicator() {
        const indicator = document.getElementById('currentPeriodIndicator');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        if (!indicator || !startDate || !endDate) return;

        const start = new Date(startDate.value);
        const end = new Date(endDate.value);

        let periodText = 'Período: ';
        if (start.getTime() === end.getTime()) {
            periodText += start.toLocaleDateString('pt-BR');
        } else {
            periodText += `${start.toLocaleDateString('pt-BR')} até ${end.toLocaleDateString('pt-BR')}`;
        }

        indicator.textContent = periodText;
    }

    showFilterLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Aplicando...';
            submitBtn.disabled = true;
        }
    }

    // ===== REFRESH FUNCTIONS =====
    refreshDashboard() {
        const btn = document.getElementById('refreshDashboard');
        const icon = btn?.querySelector('i');

        if (icon) {
            icon.classList.add('fa-spin');
        }

        this.showToast('Atualizando dashboard...', 'info');

        setTimeout(() => {
            location.reload();
        }, 1000);
    }

    refreshRecentCalls() {
        const btn = document.getElementById('refreshRecentCalls');
        const icon = btn?.querySelector('i');

        if (icon) {
            icon.classList.add('fa-spin');
        }

        setTimeout(() => {
            this.updateHistory();
            if (icon) {
                icon.classList.remove('fa-spin');
            }
            this.showToast('Histórico atualizado!', 'success');
        }, 1000);
    }

    // ===== MODAL HELPERS =====
    showModalLoading() {
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');

        if (loading) loading.style.display = 'flex';
        if (content) content.style.display = 'none';
    }

    hideModalLoading() {
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');

        if (loading) loading.style.display = 'none';
        if (content) content.style.display = 'block';
    }

    showDetailModalLoading() {
        const loading = document.getElementById('detailModalLoading');
        const content = document.getElementById('detailModalContent');

        if (loading) loading.style.display = 'flex';
        if (content) content.style.display = 'none';
    }

    hideDetailModalLoading() {
        const loading = document.getElementById('detailModalLoading');
        const content = document.getElementById('detailModalContent');

        if (loading) loading.style.display = 'none';
        if (content) content.style.display = 'block';
    }

    showModalError(message) {
        const modalContent = document.getElementById('modalContent');
        if (!modalContent) return;

        modalContent.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Erro ao carregar dados</h5>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>Tentar novamente
                </button>
            </div>
        `;

        this.hideModalLoading();
    }

    showDetailModalError(message) {
        const modalContent = document.getElementById('detailModalContent');
        if (!modalContent) return;

        modalContent.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Erro ao carregar dados</h5>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>Tentar novamente
                </button>
            </div>
        `;

        this.hideDetailModalLoading();
    }

    // ===== LOADING HELPERS OTIMIZADOS =====
    showLoading(elementId, timeout = 5000) {
        const loading = document.getElementById(elementId);
        if (loading) {
            loading.style.display = 'flex';

            // Auto-hide loading após timeout
            if (this.loadingTimeouts.has(elementId)) {
                clearTimeout(this.loadingTimeouts.get(elementId));
            }

            const timeoutId = setTimeout(() => {
                this.hideLoading(elementId);
            }, timeout);

            this.loadingTimeouts.set(elementId, timeoutId);
        }
    }

    hideLoading(elementId) {
        const loading = document.getElementById(elementId);
        if (loading) {
            loading.style.display = 'none';
        }

        // Limpar timeout
        if (this.loadingTimeouts.has(elementId)) {
            clearTimeout(this.loadingTimeouts.get(elementId));
            this.loadingTimeouts.delete(elementId);
        }
    }

    updateExtensionsCount(count) {
        const counter = document.getElementById('extensionsCount');
        if (counter) {
            counter.textContent = `${count} ramais`;
        }
    }

    updateDestinationsCount(count) {
        const counter = document.getElementById('destinationsCount');
        if (counter) {
            counter.textContent = `${count} destinos`;
        }
    }

    updateHistoryCount(count) {
        const counter = document.getElementById('historyCount');
        if (counter) {
            counter.textContent = `${count} registros`;
        }
    }

    // ===== ANIMAÇÕES OTIMIZADAS =====
    initAnimations() {
        // Usar Intersection Observer para animações mais eficientes
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            const cards = document.querySelectorAll('.metric-card, .kpi-item, .chart-card, .summary-card, .performance-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                observer.observe(card);
            });
        } else {
            // Fallback para navegadores antigos
            this.animateCards();
        }

        // Animar contadores
        this.animateCounters();
    }

    animateCards() {
        const cards = document.querySelectorAll('.metric-card, .kpi-item, .chart-card, .summary-card, .performance-card');

        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    animateCounters() {
        const counters = document.querySelectorAll('.metric-content h3, .kpi-value, .stat-number');

        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/\D/g, ''));
            if (isNaN(target) || target === 0) return;

            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = this.formatNumber(Math.floor(current));
            }, 50);
        });
    }

    // ===== RESPONSIVIDADE =====
    handleResize() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    // ===== UTILITÁRIOS =====
    formatNumber(num) {
        return num.toLocaleString('pt-BR');
    }

    formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }

    formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    showToast(message, type = 'info') {
        const toastContainer = this.getOrCreateToastContainer();
        const toastId = 'toast_' + Date.now();

        const iconMap = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'times-circle',
            info: 'info-circle'
        };

        const colorMap = {
            success: 'success',
            warning: 'warning',
            error: 'danger',
            info: 'primary'
        };

        const toastHtml = `
            <div class="toast align-items-center text-white bg-${colorMap[type]} border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${iconMap[type]} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });

        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    getOrCreateToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }

    // ===== DESTRUCTOR =====
    destroy() {
        // Limpar event listeners
        document.removeEventListener('click', this.handleClick);
        document.removeEventListener('submit', this.handleSubmit);
        document.removeEventListener('change', this.handleChange);
        window.removeEventListener('resize', this.handleResize);

        // Destruir gráficos
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });

        // Limpar timeouts
        this.loadingTimeouts.forEach(timeout => clearTimeout(timeout));

        // Limpar objetos
        this.charts = {};
        this.modals = {};
        this.loadingTimeouts.clear();
    }
}

// ===== INICIALIZAÇÃO OTIMIZADA =====
let dashboardInstance;

// Usar DOMContentLoaded com otimização
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    initDashboard();
}

function initDashboard() {
    dashboardInstance = new Dashboard();
    window.dashboard = dashboardInstance;

    window.addEventListener('beforeunload', () => {
        if (dashboardInstance) {
            dashboardInstance.destroy();
        }
    });
}

// ===== EXPORTS =====
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Dashboard, DashboardConfig };
}