<?php
$currentPage = 'email-config';
$pageTitle = 'Configurações Admin - Email';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid">

        <!-- Page Header Melhorado -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center">
                        <div class="page-icon me-3">
                            <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-cog text-white fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="page-title mb-1">Configurações Admin - Email</h1>
                            <p class="text-muted mb-0">Configure o servidor SMTP e gerencie todos os usuários do sistema</p>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <a href="<?= APP_URL ?>/email-config" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-1"></i>Minhas Config.
                        </a>
                        <button class="btn btn-primary" onclick="sendGlobalTest()" id="globalTestBtn">
                            <i class="fas fa-paper-plane me-1"></i>Teste Global
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Dashboard -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <h3 class="stat-number mb-1"><?= $emailStats['users_with_email'] ?></h3>
                        <p class="stat-label text-muted mb-0">Usuários com Email</p>
                        <div class="stat-progress mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-calendar-day fa-2x text-success"></i>
                        </div>
                        <h3 class="stat-number mb-1"><?= $emailStats['emails_today'] ?></h3>
                        <p class="stat-label text-muted mb-0">Emails Hoje</p>
                        <div class="stat-progress mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-calendar-week fa-2x text-warning"></i>
                        </div>
                        <h3 class="stat-number mb-1"><?= $emailStats['emails_week'] ?></h3>
                        <p class="stat-label text-muted mb-0">Emails esta Semana</p>
                        <div class="stat-progress mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-check-circle fa-2x text-info"></i>
                        </div>
                        <h3 class="stat-number mb-1"><?= $emailStats['success_rate'] ?>%</h3>
                        <p class="stat-label text-muted mb-0">Taxa de Sucesso</p>
                        <div class="stat-progress mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: <?= $emailStats['success_rate'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas Melhorados -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon me-3">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Erro na Configuração</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon me-3">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Configuração Salva!</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Configurações SMTP -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-server me-2 text-primary"></i>
                                Configurações do Servidor SMTP
                            </h6>
                            <span class="badge bg-<?= $smtpConfigured ? 'success' : 'warning' ?> px-3 py-2">
                                <?= $smtpConfigured ? 'Configurado' : 'Pendente' ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="smtpForm">
                            <input type="hidden" name="action" value="update_smtp">

                            <!-- Configurações do Servidor -->
                            <div class="mb-4">
                                <h6 class="text-dark mb-3">
                                    <i class="fas fa-network-wired me-2 text-muted"></i>
                                    Configurações do Servidor
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="smtp_host" class="form-label fw-semibold">
                                            <i class="fas fa-server me-1 text-muted"></i>
                                            Servidor SMTP *
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="smtp_host" name="smtp_host"
                                            placeholder="smtp.gmail.com" required
                                            value="<?= htmlspecialchars($smtpConfig['smtp_host'] ?? '') ?>">
                                        <div class="form-text">Endereço do servidor SMTP do seu provedor de email</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="smtp_port" class="form-label fw-semibold">
                                            <i class="fas fa-plug me-1 text-muted"></i>
                                            Porta *
                                        </label>
                                        <select class="form-select form-select-lg" id="smtp_port" name="smtp_port" required>
                                            <option value="587" <?= ($smtpConfig['smtp_port'] ?? 587) == 587 ? 'selected' : '' ?>>587 (TLS)</option>
                                            <option value="465" <?= ($smtpConfig['smtp_port'] ?? 587) == 465 ? 'selected' : '' ?>>465 (SSL)</option>
                                            <option value="25" <?= ($smtpConfig['smtp_port'] ?? 587) == 25 ? 'selected' : '' ?>>25 (Sem criptografia)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Criptografia -->
                            <div class="mb-4">
                                <label for="smtp_encryption" class="form-label fw-semibold">
                                    <i class="fas fa-shield-alt me-1 text-muted"></i>
                                    Tipo de Criptografia *
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check form-check-card">
                                            <input class="form-check-input" type="radio" name="smtp_encryption" value="tls" id="enc_tls"
                                                <?= ($smtpConfig['smtp_encryption'] ?? 'tls') === 'tls' ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100" for="enc_tls">
                                                <div class="card h-100 text-center">
                                                    <div class="card-body p-3">
                                                        <i class="fas fa-lock text-success mb-2"></i>
                                                        <div class="fw-bold">TLS</div>
                                                        <small class="text-muted">Recomendado</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-card">
                                            <input class="form-check-input" type="radio" name="smtp_encryption" value="ssl" id="enc_ssl"
                                                <?= ($smtpConfig['smtp_encryption'] ?? 'tls') === 'ssl' ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100" for="enc_ssl">
                                                <div class="card h-100 text-center">
                                                    <div class="card-body p-3">
                                                        <i class="fas fa-shield-alt text-warning mb-2"></i>
                                                        <div class="fw-bold">SSL</div>
                                                        <small class="text-muted">Alternativo</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-card">
                                            <input class="form-check-input" type="radio" name="smtp_encryption" value="none" id="enc_none"
                                                <?= ($smtpConfig['smtp_encryption'] ?? 'tls') === 'none' ? 'checked' : '' ?>>
                                            <label class="form-check-label w-100" for="enc_none">
                                                <div class="card h-100 text-center">
                                                    <div class="card-body p-3">
                                                        <i class="fas fa-unlock text-danger mb-2"></i>
                                                        <div class="fw-bold">Nenhuma</div>
                                                        <small class="text-muted">Não recomendado</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Credenciais -->
                            <div class="mb-4">
                                <h6 class="text-dark mb-3">
                                    <i class="fas fa-key me-2 text-muted"></i>
                                    Credenciais de Acesso
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="smtp_username" class="form-label fw-semibold">
                                            <i class="fas fa-user me-1 text-muted"></i>
                                            Usuário/Email *
                                        </label>
                                        <input type="email" class="form-control form-control-lg" id="smtp_username" name="smtp_username"
                                            placeholder="sistema@empresa.com" required
                                            value="<?= htmlspecialchars($smtpConfig['smtp_username'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="smtp_password" class="form-label fw-semibold">
                                            <i class="fas fa-lock me-1 text-muted"></i>
                                            Senha *
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control form-control-lg" id="smtp_password" name="smtp_password"
                                                placeholder="Digite a senha" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('smtp_password')">
                                                <i class="fas fa-eye" id="smtp_password_icon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Para Gmail, use uma senha de aplicativo
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Envio -->
                            <div class="mb-4">
                                <h6 class="text-dark mb-3">
                                    <i class="fas fa-envelope me-2 text-muted"></i>
                                    Configurações de Envio
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="from_email" class="form-label fw-semibold">
                                            <i class="fas fa-at me-1 text-muted"></i>
                                            Email Remetente *
                                        </label>
                                        <input type="email" class="form-control form-control-lg" id="from_email" name="from_email"
                                            placeholder="noreply@empresa.com" required
                                            value="<?= htmlspecialchars($smtpConfig['from_email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="from_name" class="form-label fw-semibold">
                                            <i class="fas fa-signature me-1 text-muted"></i>
                                            Nome Remetente *
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="from_name" name="from_name"
                                            placeholder="Sistema CDR" required
                                            value="<?= htmlspecialchars($smtpConfig['from_name'] ?? ($instance['company_name'] ?? 'Sistema CDR')) ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-outline-primary px-4" onclick="testSmtpConfig()" id="testBtn">
                                    <i class="fas fa-vial me-1"></i>Testar Configuração
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="saveSmtpBtn">
                                    <i class="fas fa-save me-1"></i>Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar de Status e Ajuda -->
            <div class="col-lg-4">
                <!-- Status SMTP -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-server me-2 text-primary"></i>
                            Status SMTP
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="status-item d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-circle me-2 text-<?= $smtpConfigured ? 'success' : 'warning' ?>" style="font-size: 8px;"></i>
                                <span>Status</span>
                            </div>
                            <span class="badge bg-<?= $smtpConfigured ? 'success' : 'warning' ?> px-3 py-2">
                                <?= $smtpConfigured ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>

                        <?php if ($smtpConfigured): ?>
                            <div class="status-item d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-server me-2 text-muted"></i>
                                    <span>Servidor</span>
                                </div>
                                <span class="text-muted small">
                                    <?= htmlspecialchars($smtpConfig['smtp_host']) ?>:<?= $smtpConfig['smtp_port'] ?>
                                </span>
                            </div>

                            <div class="status-item d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user me-2 text-muted"></i>
                                    <span>Usuário</span>
                                </div>
                                <span class="text-muted small">
                                    <?= htmlspecialchars(substr($smtpConfig['smtp_username'], 0, 20)) ?>...
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="status-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                <span>Último Teste</span>
                            </div>
                            <span class="text-muted small">
                                <?= $lastSmtpTest ? date('d/m H:i', strtotime($lastSmtpTest)) : 'Nunca' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Guia de Configuração -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-question-circle me-2 text-primary"></i>
                            Guia de Configuração
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="provider-guide mb-4">
                            <h6 class="text-success">
                                <i class="fab fa-google me-2"></i>Gmail
                            </h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="fas fa-check me-1 text-success"></i> Servidor: smtp.gmail.com</li>
                                <li><i class="fas fa-check me-1 text-success"></i> Porta: 587 (TLS)</li>
                                <li><i class="fas fa-check me-1 text-success"></i> Use senha de aplicativo</li>
                                <li><i class="fas fa-check me-1 text-success"></i> Ative autenticação 2FA</li>
                            </ul>
                        </div>

                        <div class="provider-guide mb-4">
                            <h6 class="text-info">
                                <i class="fab fa-microsoft me-2"></i>Outlook
                            </h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="fas fa-check me-1 text-info"></i> Servidor: smtp-mail.outlook.com</li>
                                <li><i class="fas fa-check me-1 text-info"></i> Porta: 587 (TLS)</li>
                                <li><i class="fas fa-check me-1 text-info"></i> Use sua conta Microsoft</li>
                                <li><i class="fas fa-check me-1 text-info"></i> Autenticação moderna</li>
                            </ul>
                        </div>

                        <div class="provider-guide">
                            <h6 class="text-warning">
                                <i class="fas fa-server me-2"></i>Servidor Próprio
                            </h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="fas fa-check me-1 text-warning"></i> Configure conforme seu provedor</li>
                                <li><i class="fas fa-check me-1 text-warning"></i> Verifique portas disponíveis</li>
                                <li><i class="fas fa-check me-1 text-warning"></i> Teste sempre antes de usar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Dicas de Segurança -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>
                            Dicas de Segurança
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="security-tip mb-3">
                            <div class="d-flex">
                                <i class="fas fa-key text-warning me-2 mt-1"></i>
                                <div>
                                    <strong>Senhas de Aplicativo</strong>
                                    <p class="text-muted small mb-0">Use sempre senhas específicas para aplicativos, nunca sua senha principal.</p>
                                </div>
                            </div>
                        </div>

                        <div class="security-tip mb-3">
                            <div class="d-flex">
                                <i class="fas fa-lock text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Criptografia</strong>
                                    <p class="text-muted small mb-0">Sempre use TLS ou SSL para proteger suas credenciais.</p>
                                </div>
                            </div>
                        </div>

                        <div class="security-tip">
                            <div class="d-flex">
                                <i class="fas fa-eye text-info me-2 mt-1"></i>
                                <div>
                                    <strong>Monitoramento</strong>
                                    <p class="text-muted small mb-0">Monitore regularmente os logs de envio para detectar problemas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Customizado -->
<style>
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 12px !important;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .stat-icon {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .form-check-card .card {
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-check-card input:checked+label .card {
        border-color: #0d6efd;
        background: linear-gradient(135deg, #f8f9ff, #ffffff);
        transform: scale(1.05);
    }

    .provider-guide {
        padding: 15px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border-left: 4px solid #e9ecef;
    }

    .provider-guide:nth-child(1) {
        border-left-color: #28a745;
    }

    .provider-guide:nth-child(2) {
        border-left-color: #17a2b8;
    }

    .provider-guide:nth-child(3) {
        border-left-color: #ffc107;
    }

    .security-tip {
        padding: 12px;
        border-radius: 8px;
        background: rgba(255, 193, 7, 0.1);
        border-left: 3px solid #ffc107;
    }

    .alert-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
    }

    .page-icon {
        animation: pulse 2s infinite;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        transform: translateY(-1px);
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    #saveSmtpBtn.changed {
        background: linear-gradient(135deg, #ff6b35, #f7931e) !important;
        border-color: #ff6b35 !important;
        animation: glow 1.5s ease-in-out infinite alternate;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 5px #ff6b35;
        }

        to {
            box-shadow: 0 0 20px #ff6b35, 0 0 30px #ff6b35;
        }
    }

    .status-item {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .status-item:last-child {
        border-bottom: none;
    }

    .card {
        border-radius: 12px !important;
    }
</style>

<!-- JavaScript Melhorado -->
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // Test SMTP configuration
    function testSmtpConfig() {
        const requiredFields = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email', 'from_name'];
        let allFilled = true;
        let firstEmptyField = null;

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                allFilled = false;
                if (!firstEmptyField) {
                    firstEmptyField = field;
                }
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!allFilled) {
            firstEmptyField.focus();

            // Mostrar toast de erro
            showToast('Erro', 'Preencha todos os campos obrigatórios antes de testar.', 'error');
            return;
        }

        const testEmail = prompt('Digite o email para teste:', '<?= $user['email'] ?>');
        if (testEmail && validateEmail(testEmail)) {
            const btn = document.getElementById('testBtn');
            const originalText = btn.innerHTML;

            // Mostrar loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testando...';
            btn.disabled = true;
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-warning');

            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="test_smtp">
            <input type="hidden" name="test_email" value="${testEmail}">
            <input type="hidden" name="smtp_host" value="${document.getElementById('smtp_host').value}">
            <input type="hidden" name="smtp_port" value="${document.getElementById('smtp_port').value}">
            <input type="hidden" name="smtp_username" value="${document.getElementById('smtp_username').value}">
            <input type="hidden" name="smtp_password" value="${document.getElementById('smtp_password').value}">
            <input type="hidden" name="smtp_encryption" value="${document.querySelector('input[name="smtp_encryption"]:checked').value}">
            <input type="hidden" name="from_email" value="${document.getElementById('from_email').value}">
            <input type="hidden" name="from_name" value="${document.getElementById('from_name').value}">
        `;
            document.body.appendChild(form);
            form.submit();
        } else if (testEmail) {
            showToast('Erro', 'Email inválido. Digite um email válido.', 'error');
        }
    }

    // Send global test
    function sendGlobalTest() {
        const testEmail = prompt('Digite o email para teste global:', '<?= $user['email'] ?>');
        if (testEmail && validateEmail(testEmail)) {
            const btn = document.getElementById('globalTestBtn');
            const originalText = btn.innerHTML;

            // Mostrar loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enviando...';
            btn.disabled = true;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-warning');

            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="send_global_test">
            <input type="hidden" name="test_email" value="${testEmail}">
        `;
            document.body.appendChild(form);
            form.submit();
        } else if (testEmail) {
            showToast('Erro', 'Email inválido. Digite um email válido.', 'error');
        }
    }

    // Validate email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Show toast notification
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}:</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove after hide
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    // Indicador de mudanças não salvas
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('smtpForm');
        const saveBtn = document.getElementById('saveSmtpBtn');
        const inputs = form.querySelectorAll('input, select');

        // Listener para mudanças
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                saveBtn.classList.add('changed');
                saveBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Salvar Alterações';
            });
        });

        // Reset do botão após salvar
        form.addEventListener('submit', function() {
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvando...';
            saveBtn.disabled = true;
        });

        // Auto-update port based on encryption
        document.querySelectorAll('input[name="smtp_encryption"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const portSelect = document.getElementById('smtp_port');
                if (this.value === 'ssl') {
                    portSelect.value = '465';
                } else if (this.value === 'tls') {
                    portSelect.value = '587';
                } else {
                    portSelect.value = '25';
                }
            });
        });
    });

    // Animação de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });

    // Validação em tempo real
    document.addEventListener('DOMContentLoaded', function() {
        const emailFields = ['smtp_username', 'from_email'];

        emailFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', function() {
                    if (this.value && !validateEmail(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>