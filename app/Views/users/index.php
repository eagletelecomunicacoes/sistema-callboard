<?php
$currentPage = 'users';
$pageTitle = 'Gerenciar Usuários';

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
                    <i class="fas fa-users me-2"></i>
                    Gerenciar Usuários
                </h1>
                <p class="page-header-subtitle">Gerencie os usuários do sistema</p>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo APP_URL; ?>/users/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Novo Usuário
                </a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-content">
                    <h6 class="card-title">
                        <i class="fas fa-list me-2"></i>Lista de Usuários
                    </h6>
                    <div class="card-header-actions">
                        <span class="badge bg-primary"><?= count($users) ?> usuários</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Ramal</th>
                                <th>Username</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th>Último Login</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                                <?php if ($user['is_online']): ?>
                                                    <div class="user-status online">
                                                        <i class="fas fa-circle"></i> Online
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if (!empty($user['extension_number'])): ?>
                                            <span class="extension-badge">
                                                <i class="fas fa-phone text-success"></i>
                                                <?php echo htmlspecialchars($user['extension_number']); ?>
                                            </span>
                                            <div class="extension-info">
                                                <small class="text-muted">
                                                    Busca: E<?= $user['extension_number'] ?>, V<?= $user['extension_number'] ?>, etc.
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-phone-slash"></i> Não informado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($user['username']); ?></code>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?= $user['role'] ?>">
                                            <?php
                                            switch ($user['role']) {
                                                case 'super_admin':
                                                    echo '<i class="fas fa-crown me-1"></i>Super Admin';
                                                    break;
                                                case 'admin':
                                                    echo '<i class="fas fa-shield-alt me-1"></i>Admin';
                                                    break;
                                                default:
                                                    echo '<i class="fas fa-user me-1"></i>Usuário';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $user['status'] ?>">
                                            <?php echo $user['status'] === 'active' ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <div class="last-login">
                                                <div class="login-date"><?php echo date('d/m/Y', strtotime($user['last_login'])); ?></div>
                                                <div class="login-time"><?php echo date('H:i', strtotime($user['last_login'])); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo APP_URL; ?>/users/edit?id=<?php echo $user['id']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <?php if (!empty($user['extension_number'])): ?>
                                                <a href="<?php echo APP_URL; ?>/email-config/preview?type=daily&user_id=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Ver estatísticas do ramal <?= $user['extension_number'] ?>">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($user['id'] != Auth::user()['id']): ?>
                                                <form method="POST" action="<?php echo APP_URL; ?>/users/change-status" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?>"
                                                        title="<?php echo $user['status'] === 'active' ? 'Desativar' : 'Ativar'; ?>"
                                                        onclick="return confirm('Tem certeza?')">
                                                        <i class="fas fa-<?php echo $user['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>

                                                <form method="POST" action="<?php echo APP_URL; ?>/users/delete" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Excluir"
                                                        onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .extension-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
    }

    .extension-info {
        margin-top: 2px;
    }

    .extension-info small {
        font-size: 0.75rem;
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>