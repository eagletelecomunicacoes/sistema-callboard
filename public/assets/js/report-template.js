// ===== VARIÁVEIS GLOBAIS =====
let currentPage = 1;
let itemsPerPage = 50;
let filteredRows = [];
let allRows = [];
let sortDirection = {};

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Iniciando Report Template...');

    // Verificar se estamos na página do relatório
    if (!document.getElementById('callsTableBody')) {
        console.log('❌ Não é a página do relatório. Saindo...');
        return;
    }

    setTimeout(() => {
        try {
            initializeTable();
            initializeFilters();
            updatePagination();
            console.log('✅ Report Template carregado!');
        } catch (error) {
            console.error('❌ Erro na inicialização:', error);
        }
    }, 200);
});

// ===== FUNÇÕES DE INICIALIZAÇÃO =====
function initializeTable() {
    const tableBody = document.getElementById('callsTableBody');
    if (tableBody) {
        allRows = Array.from(tableBody.querySelectorAll('.call-row'));
        filteredRows = [...allRows];
        updateTableDisplay();
        console.log(`📞 Total de chamadas carregadas: ${allRows.length}`);
    } else {
        console.warn('⚠️ Tabela não encontrada');
        allRows = [];
        filteredRows = [];
    }
}

function initializeFilters() {
    const dateFilter = document.getElementById('dateFilter');
    const typeFilter = document.getElementById('typeFilter');
    const durationFilter = document.getElementById('durationFilter');

    if (dateFilter) {
        dateFilter.addEventListener('change', applyFilters);
        console.log('✅ Filtro de data configurado');
    }
    if (typeFilter) {
        typeFilter.addEventListener('change', applyFilters);
        console.log('✅ Filtro de tipo configurado');
    }
    if (durationFilter) {
        durationFilter.addEventListener('change', applyFilters);
        console.log('✅ Filtro de duração configurado');
    }
}

// ===== FUNÇÕES DE FILTRO =====
function filterByDate() {
    applyFilters();
}

function filterByType() {
    applyFilters();
}

function filterByDuration() {
    applyFilters();
}

function applyFilters() {
    try {
        const dateFilter = document.getElementById('dateFilter');
        const typeFilter = document.getElementById('typeFilter');
        const durationFilter = document.getElementById('durationFilter');

        const dateValue = dateFilter ? dateFilter.value : 'all';
        const typeValue = typeFilter ? typeFilter.value : 'all';
        const durationValue = durationFilter ? durationFilter.value : 'all';

        filteredRows = allRows.filter(row => {
            // Filtro por data
            if (dateValue !== 'all') {
                const rowDate = row.dataset.date;
                const today = new Date().toISOString().split('T')[0];
                const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                const weekAgo = new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0];
                const monthAgo = new Date(Date.now() - 30 * 86400000).toISOString().split('T')[0];

                switch (dateValue) {
                    case 'today':
                        if (rowDate !== today) return false;
                        break;
                    case 'yesterday':
                        if (rowDate !== yesterday) return false;
                        break;
                    case 'week':
                        if (rowDate < weekAgo) return false;
                        break;
                    case 'month':
                        if (rowDate < monthAgo) return false;
                        break;
                }
            }

            // Filtro por tipo
            if (typeValue !== 'all') {
                const rowType = row.dataset.type;
                if (rowType !== typeValue) return false;
            }

            // Filtro por duração
            if (durationValue !== 'all') {
                const duration = parseInt(row.dataset.duration) || 0;
                switch (durationValue) {
                    case 'short':
                        if (duration >= 60) return false;
                        break;
                    case 'medium':
                        if (duration < 60 || duration > 300) return false;
                        break;
                    case 'long':
                        if (duration <= 300) return false;
                        break;
                }
            }

            return true;
        });

        currentPage = 1;
        updateTableDisplay();
        updatePagination();
        updateFilteredCount();

        console.log(`🔍 Filtros aplicados: ${filteredRows.length}/${allRows.length}`);
    } catch (error) {
        console.error('❌ Erro ao aplicar filtros:', error);
    }
}

function resetFilters() {
    try {
        const dateFilter = document.getElementById('dateFilter');
        const typeFilter = document.getElementById('typeFilter');
        const durationFilter = document.getElementById('durationFilter');

        if (dateFilter) dateFilter.value = 'all';
        if (typeFilter) typeFilter.value = 'all';
        if (durationFilter) durationFilter.value = 'all';

        filteredRows = [...allRows];
        currentPage = 1;
        updateTableDisplay();
        updatePagination();
        updateFilteredCount();

        console.log('🔄 Filtros resetados');
    } catch (error) {
        console.error('❌ Erro ao resetar filtros:', error);
    }
}

function updateFilteredCount() {
    try {
        const filteredCountElement = document.getElementById('filteredCount');
        const totalCallsCountElement = document.getElementById('totalCallsCount');

        if (filteredCountElement) {
            filteredCountElement.textContent = filteredRows.length;
        }

        if (totalCallsCountElement) {
            totalCallsCountElement.textContent = allRows.length;
        }
    } catch (error) {
        console.error('❌ Erro ao atualizar contadores:', error);
    }
}

// ===== FUNÇÕES DE ORDENAÇÃO =====
function sortTable(columnIndex) {
    try {
        const column = ['caller', 'duration', 'date', 'frequency'][columnIndex];
        const isAscending = sortDirection[column] !== 'asc';
        sortDirection[column] = isAscending ? 'asc' : 'desc';

        filteredRows.sort((a, b) => {
            let aValue, bValue;

            switch (column) {
                case 'caller':
                    const aCallerEl = a.querySelector('.caller-main');
                    const bCallerEl = b.querySelector('.caller-main');
                    aValue = aCallerEl ? aCallerEl.textContent.trim() : '';
                    bValue = bCallerEl ? bCallerEl.textContent.trim() : '';
                    break;
                case 'duration':
                    aValue = parseInt(a.dataset.duration) || 0;
                    bValue = parseInt(b.dataset.duration) || 0;
                    break;
                case 'date':
                    aValue = new Date(a.dataset.date || '1970-01-01');
                    bValue = new Date(b.dataset.date || '1970-01-01');
                    break;
                case 'frequency':
                    const aFreqEl = a.querySelector('.frequency-badge');
                    const bFreqEl = b.querySelector('.frequency-badge');
                    aValue = aFreqEl ? parseInt(aFreqEl.textContent.replace(/[^\d]/g, '')) || 0 : 0;
                    bValue = bFreqEl ? parseInt(bFreqEl.textContent.replace(/[^\d]/g, '')) || 0 : 0;
                    break;
                default:
                    return 0;
            }

            if (aValue < bValue) return isAscending ? -1 : 1;
            if (aValue > bValue) return isAscending ? 1 : -1;
            return 0;
        });

        updateTableDisplay();
        updateSortIcons(columnIndex, isAscending);

        console.log(`📊 Ordenado por ${column}`);
    } catch (error) {
        console.error('❌ Erro na ordenação:', error);
    }
}

function updateSortIcons(activeColumn, isAscending) {
    try {
        // Remover ícones de ordenação existentes
        document.querySelectorAll('.unified-table th i').forEach(icon => {
            if (icon) icon.className = 'fas fa-sort';
        });

        // Adicionar ícone na coluna ativa
        const activeHeader = document.querySelectorAll('.unified-table th')[activeColumn];
        if (activeHeader) {
            const icon = activeHeader.querySelector('i');
            if (icon) {
                icon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }
        }
    } catch (error) {
        console.error('❌ Erro ao atualizar ícones:', error);
    }
}

// ===== FUNÇÕES DE PAGINAÇÃO =====
function updateTableDisplay() {
    try {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        // Esconder todas as linhas
        allRows.forEach(row => {
            if (row && row.style) {
                row.style.display = 'none';
            }
        });

        // Mostrar apenas as linhas filtradas da página atual
        filteredRows.slice(startIndex, endIndex).forEach(row => {
            if (row && row.style) {
                row.style.display = '';
            }
        });
    } catch (error) {
        console.error('❌ Erro ao atualizar tabela:', error);
    }
}

function updatePagination() {
    try {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);

        // *** VERIFICAÇÃO SEGURA DOS ELEMENTOS ***
        const currentPageEl = document.getElementById('currentPage');
        const totalPagesEl = document.getElementById('totalPages');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (currentPageEl) {
            currentPageEl.textContent = currentPage;
        } else {
            console.warn('⚠️ Elemento currentPage não encontrado');
        }

        if (totalPagesEl) {
            totalPagesEl.textContent = totalPages;
        } else {
            console.warn('⚠️ Elemento totalPages não encontrado');
        }

        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        } else {
            console.warn('⚠️ Elemento prevBtn não encontrado');
        }

        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages || totalPages === 0;
        } else {
            console.warn('⚠️ Elemento nextBtn não encontrado');
        }
    } catch (error) {
        console.error('❌ Erro na paginação:', error);
    }
}

function previousPage() {
    try {
        if (currentPage > 1) {
            currentPage--;
            updateTableDisplay();
            updatePagination();
            console.log(`⬅️ Página ${currentPage}`);
        }
    } catch (error) {
        console.error('❌ Erro ao ir para página anterior:', error);
    }
}

function nextPage() {
    try {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTableDisplay();
            updatePagination();
            console.log(`➡️ Página ${currentPage}`);
        }
    } catch (error) {
        console.error('❌ Erro ao ir para próxima página:', error);
    }
}

// ===== FUNÇÕES DE AÇÃO =====
function closePreview() {
    try {
        if (window.opener) {
            window.close();
        } else {
            history.back();
        }
    } catch (error) {
        console.error('❌ Erro ao fechar:', error);
        try {
            history.back();
        } catch (fallbackError) {
            console.error('❌ Erro no fallback:', fallbackError);
        }
    }
}

function printReport() {
    try {
        // Mostrar todas as linhas filtradas para impressão
        filteredRows.forEach(row => {
            if (row && row.style) {
                row.style.display = '';
            }
        });

        window.print();

        // Restaurar paginação
        setTimeout(() => {
            updateTableDisplay();
        }, 1000);

        console.log('🖨️ Relatório enviado para impressão');
    } catch (error) {
        console.error('❌ Erro ao imprimir:', error);
        alert('Erro ao imprimir relatório');
    }
}

function exportReport() {
    try {
        alert('Funcionalidade de exportação será implementada em breve!');
        console.log('📥 Exportação solicitada');
    } catch (error) {
        console.error('❌ Erro ao exportar:', error);
    }
}

// ===== FUNÇÕES UTILITÁRIAS =====
function formatDuration(seconds) {
    try {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (hours > 0) {
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    } catch (error) {
        console.error('❌ Erro ao formatar duração:', error);
        return '00:00';
    }
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    } catch (error) {
        console.error('❌ Erro ao formatar data:', error);
        return dateString;
    }
}

// ===== LOG DE DEBUG =====
console.log('�� Report Template JS carregado com sucesso!');