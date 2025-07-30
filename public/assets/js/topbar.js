/**
 * Topbar Management System
 * Handles header interactions, search, notifications, and user actions
 */

class TopbarManager {
    constructor() {
        this.topbar = document.getElementById('topbar');
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        this.globalSearch = document.getElementById('globalSearch');
        this.currentTimeElement = document.getElementById('currentTime');
        this.onlineUsersElement = document.getElementById('onlineUsers');

        this.init();
    }

    init() {
        if (!this.topbar) {
            console.warn('Topbar: Elemento não encontrado');
            return;
        }

        this.setupEventListeners();
        this.startClock();
        this.updateOnlineUsers();

        console.log('✅ Topbar inicializada');
    }

    setupEventListeners() {
        // Mobile menu toggle
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
        }

        // Global search
        if (this.globalSearch) {
            this.globalSearch.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(e.target.value);
                }
            });

            // Search suggestions
            this.globalSearch.addEventListener('input', (e) => {
                this.showSearchSuggestions(e.target.value);
            });
        }

        // Notification interactions
        this.setupNotifications();

        // User profile interactions
        this.setupUserProfile();
    }

    toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebar && overlay) {
            sidebar.classList.toggle('mobile-visible');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        }
    }

    performSearch(query) {
        if (!query.trim()) return;

        console.log('Realizando busca:', query);

        // Aqui você implementaria a lógica de busca
        // Por exemplo, redirecionar para página de resultados
        window.location.href = `/search?q=${encodeURIComponent(query)}`;
    }

    showSearchSuggestions(query) {
        if (query.length < 2) return;

        // Implementar sugestões de busca
        console.log('Mostrando sugestões para:', query);
    }

    setupNotifications() {
        const notificationBtns = document.querySelectorAll('.notification-btn');

        notificationBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.markNotificationsAsRead();
            });
        });
    }

    setupUserProfile() {
        // Implementar ações do perfil do usuário
        const profileActions = document.querySelectorAll('.user-dropdown .dropdown-item');

        profileActions.forEach(action => {
            action.addEventListener('click', (e) => {
                const href = action.getAttribute('href');
                if (href && href !== '#') {
                    // Ação específica baseada no href
                    console.log('Ação do perfil:', href);
                }
            });
        });
    }

    markNotificationsAsRead() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            // Implementar lógica para marcar notificações como lidas
            setTimeout(() => {
                badge.style.display = 'none';
            }, 1000);
        }
    }

    startClock() {
        if (!this.currentTimeElement) return;

        const updateTime = () => {
            const now = new Date();
            const timeString = now.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            this.currentTimeElement.textContent = timeString;
        };

        updateTime();
        setInterval(updateTime, 1000);
    }

    updateOnlineUsers() {
        if (!this.onlineUsersElement) return;

        // Implementar lógica para buscar usuários online
        // Por enquanto, simular com um número aleatório
        const updateUsers = () => {
            const onlineCount = Math.floor(Math.random() * 5) + 1;
            this.onlineUsersElement.textContent = onlineCount;
        };

        updateUsers();
        setInterval(updateUsers, 30000); // Atualizar a cada 30 segundos
    }

    // Public API
    showNotification(message, type = 'info') {
        // Implementar sistema de notificações toast
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        }
    }

    updatePageTitle(title) {
        const titleElement = document.querySelector('.page-title');
        if (titleElement) {
            titleElement.textContent = title;
        }
        document.title = `${title} - CDR System`;
    }

    updateBreadcrumb(breadcrumbs) {
        const breadcrumbNav = document.querySelector('.breadcrumb');
        if (!breadcrumbNav || !Array.isArray(breadcrumbs)) return;

        // Manter o item "Dashboard"
        const dashboardItem = breadcrumbNav.querySelector('li:first-child');
        breadcrumbNav.innerHTML = '';

        if (dashboardItem) {
            breadcrumbNav.appendChild(dashboardItem);
        }

        // Adicionar novos breadcrumbs
        breadcrumbs.forEach((breadcrumb, index) => {
            const li = document.createElement('li');
            li.className = 'breadcrumb-item';

            if (breadcrumb.url && index < breadcrumbs.length - 1) {
                const a = document.createElement('a');
                a.href = breadcrumb.url;
                a.textContent = breadcrumb.title;
                li.appendChild(a);
            } else {
                li.className += ' active';
                li.textContent = breadcrumb.title;
            }

            breadcrumbNav.appendChild(li);
        });
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    window.topbarManager = new TopbarManager();
});

// Export para módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TopbarManager;
}