// Inicialização principal
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Sistema CDR iniciado');

    // Configurar Toastr
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            timeOut: 5000,
            positionClass: 'toast-top-right',
            preventDuplicates: true,
            closeButton: true,
            progressBar: true
        };
    }

    // Inicializar sidebar
    initSidebar();

    console.log('✅ Sistema inicializado');
});

// Função da sidebar
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (!sidebar) {
        console.error('❌ Sidebar não encontrada');
        return;
    }

    console.log('✅ Sidebar encontrada, inicializando...');

    // Toggle mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('🔄 Toggle clicado');

            sidebar.classList.toggle('mobile-visible');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('active');
            }
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Fechar com overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('mobile-visible');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Fechar ao clicar nos links (mobile)
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                setTimeout(function () {
                    sidebar.classList.remove('mobile-visible');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                    document.body.classList.remove('sidebar-open');
                }, 150);
            }
        });
    });

    // Resize handler
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-visible');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
            document.body.classList.remove('sidebar-open');
        }
    });

    console.log('✅ Sidebar inicializada com sucesso');
}