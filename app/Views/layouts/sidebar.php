<?php
// Verificar se o usuário está logado
if (!Auth::isLoggedIn()) {
    return;
}

$user = Auth::user();
$instance = Auth::instance();
$isAdmin = Auth::isAdmin();
$isSuperAdmin = Auth::isSuperAdmin();

// Definir página atual se não estiver definida
if (!isset($currentPage)) {
    $currentPage = '';
}
?>

<nav class="sidebar" id="sidebar">
    <!-- Header da Sidebar -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-phone-alt brand-icon"></i>
            <span class="brand-text">CDR System</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" type="button">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Informações do Usuário -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="user-role">
                <?php
                switch ($user['role']) {
                    case 'super_admin':
                        echo '<i class="fas fa-crown"></i> Super Admin';
                        break;
                    case 'admin':
                        echo '<i class="fas fa-shield-alt"></i> Admin';
                        break;
                    default:
                        echo '<i class="fas fa-user"></i> Usuário';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Menu de Navegação -->
    <div class="sidebar-menu">
        <ul class="nav-list">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="<?= APP_URL ?>/dashboard" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Relatórios -->
            <li class="nav-item">
                <a href="<?= APP_URL ?>/reports" class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <span class="nav-text">Relatórios</span>
                </a>
            </li>

            <!-- Configurações de Email (TODOS os usuários) -->
            <li class="nav-item">
                <a href="<?= APP_URL ?>/email-config" class="nav-link <?= $currentPage === 'email-config' ? 'active' : '' ?>">
                    <i class="fas fa-envelope-open-text nav-icon"></i>
                    <span class="nav-text">Config. Email</span>
                </a>
            </li>

            <?php if ($isAdmin || $isSuperAdmin): ?>
                <!-- Divisor Admin -->
                <li class="nav-divider">
                    <span class="divider-text">Administração</span>
                </li>

                <!-- Usuários -->
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/users" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Usuários</span>
                    </a>
                </li>

                <!-- Config Email Admin -->
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/email/admin-config" class="nav-link <?= $currentPage === 'email-admin' ? 'active' : '' ?>">
                        <i class="fas fa-server nav-icon"></i>
                        <span class="nav-text">Config. SMTP</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($isSuperAdmin): ?>
                <!-- Divisor Super Admin -->
                <li class="nav-divider">
                    <span class="divider-text">Super Admin</span>
                </li>

                <!-- Instâncias -->
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/instances" class="nav-link <?= $currentPage === 'instances' ? 'active' : '' ?>">
                        <i class="fas fa-building nav-icon"></i>
                        <span class="nav-text">Instâncias</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="footer-actions">
            <a href="<?= APP_URL ?>/logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span class="logout-text">Sair</span>
            </a>
        </div>
        <div class="footer-info">
            <small>CDR System v1.0</small>
        </div>
    </div>
</nav>

<!-- Overlay Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>