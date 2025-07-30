<?php
$currentPage = 'email-config';
$pageTitle = 'Configurações de Email';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/sidebar.php';
include __DIR__ . '/../layouts/topbar.php';
?>

<!-- Meta tags para JavaScript -->
<meta name="user-email" content="<?= htmlspecialchars($user['email']) ?>">
<meta name="user-id" content="<?= $user['id'] ?>">

<!-- CSS específico -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/email-config.css">

<div class="main-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center">
                        <div class="page-icon me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-envelope-open-text text-white fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="page-title mb-1">Configurações de Email</h1>
                            <p class="text-muted mb-0">Configure suas preferências de notificações e relatórios por email</p>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <?php if ($isAdmin): ?>
                            <a href="<?= APP_URL ?>/email/admin-config" class="btn btn-outline-primary">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-secondary" onclick="previewReport()">
                            <i class="fas fa-eye me-1"></i>Preview
                        </button>
                        <button class="btn btn-success" onclick="sendTestEmail()" id="testEmailBtn">
                            <i class="fas fa-paper-plane me-1"></i>Testar Email
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon me-3">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Ops! Algo deu errado</h6>
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
                        <h6 class="alert-heading mb-1">Sucesso!</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Configurações Principais -->
            <div class="col-lg-8">
                <!-- Card do Usuário -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <span class="text-white fw-bold fs-4">
                                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= htmlspecialchars($user['email']) ?>
                                </p>
                                <small class="text-success">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    Conta ativa
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $smtpConfigured ? 'success' : 'warning' ?> fs-6">
                                    <?= $smtpConfigured ? 'Email Configurado' : 'Pendente Configuração' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Configurações -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-sliders-h me-2 text-primary"></i>
                            Suas Preferências de Email
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="userSettingsForm">
                            <input type="hidden" name="action" value="update_user_preferences">

                            <!-- Tipos de Relatório -->
                            <div class="mb-4">
                                <h6 class="mb-3 text-dark">
                                    <i class="fas fa-file-alt me-2 text-primary"></i>
                                    Tipos de Relatório
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card h-100 border-2 report-card" data-type="daily">
                                            <div class="card-body text-center p-3">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-day fa-2x text-primary"></i>
                                                </div>
                                                <div class="form-check form-switch d-flex justify-content-center mb-2">
                                                    <input class="form-check-input" type="checkbox" name="daily_reports" value="1" id="daily_reports"
                                                        <?= ($userSettings['daily_reports'] ?? 0) ? 'checked' : '' ?>>
                                                </div>
                                                <h6 class="card-title mb-1">Relatórios Diários</h6>
                                                <small class="text-muted">Resumo das chamadas do dia enviado toda manhã</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-2 report-card" data-type="weekly">
                                            <div class="card-body text-center p-3">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-week fa-2x text-success"></i>
                                                </div>
                                                <div class="form-check form-switch d-flex justify-content-center mb-2">
                                                    <input class="form-check-input" type="checkbox" name="weekly_reports" value="1" id="weekly_reports"
                                                        <?= ($userSettings['weekly_reports'] ?? 0) ? 'checked' : '' ?>>
                                                </div>
                                                <h6 class="card-title mb-1">Relatórios Semanais</h6>
                                                <small class="text-muted">Resumo semanal enviado toda segunda-feira</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-2 report-card" data-type="monthly">
                                            <div class="card-body text-center p-3">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                                                </div>
                                                <div class="form-check form-switch d-flex justify-content-center mb-2">
                                                    <input class="form-check-input" type="checkbox" name="monthly_reports" value="1" id="monthly_reports"
                                                        <?= ($userSettings['monthly_reports'] ?? 0) ? 'checked' : '' ?>>
                                                </div>
                                                <h6 class="card-title mb-1">Relatórios Mensais</h6>
                                                <small class="text-muted">Resumo mensal enviado no primeiro dia do mês</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Envio -->
                            <div class="mb-4">
                                <h6 class="mb-3 text-dark">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    Configurações de Envio
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="send_time" class="form-label fw-semibold">
                                            <i class="fas fa-clock me-1 text-muted"></i>
                                            Horário de Envio
                                        </label>
                                        <input type="time" class="form-control form-control-lg" id="send_time" name="send_time"
                                            value="<?= substr($userSettings['send_time'] ?? '08:00:00', 0, 5) ?>">
                                        <div class="form-text">Horário em que os relatórios serão enviados</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="timezone" class="form-label fw-semibold">
                                            <i class="fas fa-globe me-1 text-muted"></i>
                                            Fuso Horário
                                        </label>
                                        <select class="form-select form-select-lg" id="timezone" name="timezone">
                                            <option value="America/Sao_Paulo" <?= ($userSettings['timezone'] ?? 'America/Sao_Paulo') === 'America/Sao_Paulo' ? 'selected' : '' ?>>
                                                🇧🇷 Brasília (UTC-3)
                                            </option>
                                            <option value="America/New_York" <?= ($userSettings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>
                                                🇺🇸 Nova York (UTC-5)
                                            </option>
                                            <option value="Europe/London" <?= ($userSettings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>
                                                🇬🇧 Londres (UTC+0)
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-outline-primary" onclick="previewReport()">
                                    <i class="fas fa-eye me-1"></i>Visualizar Preview
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="saveBtn">
                                    <i class="fas fa-save me-1"></i>Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar de Informações -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Status do Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="status-item d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-server me-2 text-muted"></i>
                                <span>Configuração SMTP</span>
                            </div>
                            <span class="badge bg-<?= $smtpConfigured ? 'success' : 'warning' ?> px-3 py-2">
                                <?= $smtpConfigured ? 'Configurado' : 'Não Configurado' ?>
                            </span>
                        </div>

                        <div class="status-item d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-paper-plane me-2 text-muted"></i>
                                <span>Último Envio</span>
                            </div>
                            <span class="text-muted">
                                <?= $lastEmailSent ? date('d/m H:i', strtotime($lastEmailSent)) : 'Nunca' ?>
                            </span>
                        </div>

                        <div class="status-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line me-2 text-muted"></i>
                                <span>Emails (30 dias)</span>
                            </div>
                            <span class="badge bg-info px-3 py-2"><?= $emailsSent30d ?></span>
                        </div>

                        <?php if ($isAdmin && !$smtpConfigured): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>
                                    <strong>Ação necessária:</strong> Configure o SMTP nas
                                    <a href="<?= APP_URL ?>/email/admin-config" class="alert-link">configurações administrativas</a>.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dicas e Ajuda -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>
                            Dicas e Ajuda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="tip-item mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Horário Ideal</strong>
                                    <p class="text-muted small mb-0">Configure o horário ideal para receber seus relatórios, evitando horários de pico.</p>
                                </div>
                            </div>
                        </div>

                        <div class="tip-item mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Frequência</strong>
                                    <p class="text-muted small mb-0">Escolha apenas os tipos de relatório que realmente precisa para evitar spam.</p>
                                </div>
                            </div>
                        </div>

                        <div class="tip-item">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Teste Sempre</strong>
                                    <p class="text-muted small mb-0">Use o botão "Testar Email" para verificar se tudo está funcionando corretamente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript específico -->
<script src="<?= APP_URL ?>/assets/js/email-config.js"></script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>