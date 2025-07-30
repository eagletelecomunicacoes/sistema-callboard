<?php
$currentPage = 'users';
$pageTitle = 'Editar Usuário';

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
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Usuário
                </h1>
                <p class="page-header-subtitle">Edite as informações do usuário</p>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo APP_URL; ?>/users" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Voltar
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

        <div class="row">
            <!-- Form Column -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">
                            <i class="fas fa-user-cog me-2"></i>Dados do Usuário
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" novalidate class="user-form">
                            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">

                            <div class="form-section">
                                <h6 class="form-section-title">Informações Pessoais</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first_name" class="form-label">Nome *</label>
                                            <input type="text"
                                                class="form-control"
                                                id="first_name"
                                                name="first_name"
                                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? $editUser['first_name']); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last_name" class="form-label">Sobrenome *</label>
                                            <input type="text"
                                                class="form-control"
                                                id="last_name"
                                                name="last_name"
                                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? $editUser['last_name']); ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email"
                                                class="form-control"
                                                id="email"
                                                name="email"
                                                value="<?php echo htmlspecialchars($_POST['email'] ?? $editUser['email']); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="extension_number" class="form-label">
                                                Ramal
                                                <i class="fas fa-info-circle text-muted"
                                                    title="Digite apenas os números do ramal (Ex: 4554)"></i>
                                            </label>
                                            <input type="text"
                                                class="form-control"
                                                id="extension_number"
                                                name="extension_number"
                                                value="<?php echo htmlspecialchars($_POST['extension_number'] ?? $editUser['extension_number'] ?? ''); ?>"
                                                placeholder="Ex: 4554"
                                                maxlength="5"
                                                pattern="^[0-9]{3,5}$">
                                            <div class="form-text">
                                                <i class="fas fa-phone text-primary"></i>
                                                Apenas números de 3 a 5 dígitos - Para estatísticas personalizadas de chamadas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h6 class="form-section-title">Credenciais de Acesso</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username" class="form-label">Nome de Usuário *</label>
                                            <input type="text"
                                                class="form-control"
                                                id="username"
                                                name="username"
                                                value="<?php echo htmlspecialchars($_POST['username'] ?? $editUser['username']); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="role" class="form-label">Função</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="user" <?php echo ($_POST['role'] ?? $editUser['role']) === 'user' ? 'selected' : ''; ?>>
                                                    Usuário
                                                </option>
                                                <option value="admin" <?php echo ($_POST['role'] ?? $editUser['role']) === 'admin' ? 'selected' : ''; ?>>
                                                    Administrador
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label">Nova Senha</label>
                                            <div class="password-input">
                                                <input type="password"
                                                    class="form-control"
                                                    id="password"
                                                    name="password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Deixe em branco para manter a senha atual</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                            <div class="password-input">
                                                <input type="password"
                                                    class="form-control"
                                                    id="confirm_password"
                                                    name="confirm_password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="<?php echo APP_URL; ?>/users" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Column -->
            <div class="col-lg-4">
                <div class="card info-card">
                    <div class="card-header">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>Informações do Usuário
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="user-summary">
                            <div class="user-summary-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-summary-info">
                                <h6><?= htmlspecialchars($editUser['first_name'] . ' ' . $editUser['last_name']) ?></h6>
                                <p><?= htmlspecialchars($editUser['email']) ?></p>
                                <?php if (!empty($editUser['extension_number'])): ?>
                                    <p class="text-success">
                                        <i class="fas fa-phone"></i>
                                        Ramal: <?= htmlspecialchars($editUser['extension_number']) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-muted">
                                        <i class="fas fa-phone-slash"></i>
                                        Sem ramal vinculado
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">ID:</span>
                                <span class="info-value"><?php echo $editUser['id']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Criado em:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($editUser['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span class="status-badge status-<?= $editUser['status'] ?>">
                                    <?php echo $editUser['status'] === 'active' ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Último Login:</span>
                                <span class="info-value">
                                    <?php echo $editUser['last_login'] ? date('d/m/Y H:i', strtotime($editUser['last_login'])) : 'Nunca'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small>Deixe os campos de senha em branco para manter a senha atual.</small>
                        </div>

                        <?php if (!empty($editUser['extension_number'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-chart-line me-2"></i>
                                <small><strong>Ramal vinculado!</strong> Este usuário verá estatísticas personalizadas de suas chamadas (busca em todos os canais: E<?= $editUser['extension_number'] ?>, V<?= $editUser['extension_number'] ?>, etc.).</small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <small><strong>Sem ramal:</strong> Usuário verá estatísticas gerais do sistema.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // Password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;

        if (password && password !== confirmPassword) {
            this.setCustomValidity('As senhas não coincidem');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });

    // Format extension number (only numbers)
    document.getElementById('extension_number').addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, ''); // Apenas números

        this.value = value;

        // Validação visual
        if (value.length > 0 && (value.length < 3 || value.length > 5)) {
            this.classList.add('is-invalid');
        } else if (value.length >= 3) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-invalid', 'is-valid');
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>