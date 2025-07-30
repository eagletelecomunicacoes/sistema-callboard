<?php
$currentPage = 'users';
$pageTitle = 'Criar Usuário';

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
                    <i class="fas fa-user-plus me-2"></i>
                    Criar Novo Usuário
                </h1>
                <p class="page-header-subtitle">Adicione um novo usuário ao sistema</p>
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
                                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
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
                                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
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
                                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                                                value="<?php echo htmlspecialchars($_POST['extension_number'] ?? ''); ?>"
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
                                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="role" class="form-label">Função</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="user" <?php echo ($_POST['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>
                                                    <i class="fas fa-user"></i> Usuário
                                                </option>
                                                <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                                    <i class="fas fa-shield-alt"></i> Administrador
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label">Senha *</label>
                                            <div class="password-input">
                                                <input type="password"
                                                    class="form-control"
                                                    id="password"
                                                    name="password"
                                                    required>
                                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Mínimo de 6 caracteres</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirmar Senha *</label>
                                            <div class="password-input">
                                                <input type="password"
                                                    class="form-control"
                                                    id="confirm_password"
                                                    name="confirm_password"
                                                    required>
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
                                    <i class="fas fa-save me-1"></i>Criar Usuário
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
                            <i class="fas fa-info-circle me-2"></i>Informações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="info-section">
                            <h6 class="info-title">Campos Obrigatórios</h6>
                            <ul class="info-list">
                                <li><i class="fas fa-check text-success"></i> Nome e Sobrenome</li>
                                <li><i class="fas fa-check text-success"></i> Email válido</li>
                                <li><i class="fas fa-check text-success"></i> Nome de usuário único</li>
                                <li><i class="fas fa-check text-success"></i> Senha (mín. 6 caracteres)</li>
                            </ul>
                        </div>

                        <div class="info-section">
                            <h6 class="info-title">Campo Opcional</h6>
                            <div class="phone-info">
                                <div class="phone-item">
                                    <span class="phone-badge">
                                        <i class="fas fa-phone"></i> Ramal
                                    </span>
                                    <p>Digite apenas os números do ramal (Ex: 4554) para ver estatísticas personalizadas de suas chamadas no sistema CDR</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-section">
                            <h6 class="info-title">Funções Disponíveis</h6>
                            <div class="role-info">
                                <div class="role-item">
                                    <span class="role-badge role-user">
                                        <i class="fas fa-user"></i> Usuário
                                    </span>
                                    <p>Acesso básico ao sistema</p>
                                </div>
                                <div class="role-item">
                                    <span class="role-badge role-admin">
                                        <i class="fas fa-shield-alt"></i> Admin
                                    </span>
                                    <p>Acesso total ao sistema</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <small><strong>Dica:</strong> O ramal deve conter apenas números (ex: 4554) para funcionar corretamente com o sistema CDR. O sistema buscará automaticamente em todos os canais (E4554, V4554, etc.).</small>
                        </div>
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

        if (password !== confirmPassword) {
            this.setCustomValidity('As senhas não coincidem');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });

    // Auto-generate username from email
    document.getElementById('email').addEventListener('input', function() {
        const email = this.value;
        const username = email.split('@')[0];
        const usernameField = document.getElementById('username');

        if (username && !usernameField.value) {
            usernameField.value = username;
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