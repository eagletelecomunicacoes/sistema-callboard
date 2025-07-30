<?php
// Verificar se o usuário está logado
if (!Auth::isLoggedIn()) {
    return;
}

$user = Auth::user();
$instance = Auth::instance();
$isAdmin = Auth::isAdmin();
$isSuperAdmin = Auth::isSuperAdmin();
?>

<header class="topbar" id="topbar">
    <div class="topbar-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Title -->
        <div class="page-title-section">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        </div>

        <!-- Right Actions -->
        <div class="topbar-actions">
            <!-- Search -->
            <div class="search-box">
                <input type="text" class="search-input" placeholder="Buscar...">
                <i class="fas fa-search search-icon"></i>
            </div>

            <!-- Notifications -->
            <div class="notification-wrapper">
                <button class="notification-btn" type="button">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </button>
            </div>

            <!-- User Profile -->
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                    <span class="user-role">
                        <?php
                        switch ($user['role']) {
                            case 'super_admin':
                                echo 'Super Admin';
                                break;
                            case 'admin':
                                echo 'Admin';
                                break;
                            default:
                                echo 'Usuário';
                        }
                        ?>
                    </span>
                </div>
                <i class="fas fa-chevron-down user-dropdown-arrow"></i>
            </div>

            <!-- Logout -->
            <a href="<?= APP_URL ?>/logout" class="logout-btn" title="Sair">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>