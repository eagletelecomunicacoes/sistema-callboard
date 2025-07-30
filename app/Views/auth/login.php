<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema CDR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }

        .instance-badge {
            background: #e3f2fd;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid #bbdefb;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <h4 class="mb-1">Sistema CDR</h4>
                        <p class="mb-0 opacity-75">Acesso ao Sistema</p>
                    </div>

                    <div class="login-body">
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

                        <form method="POST" id="loginForm">
                            <!-- SELEÇÃO DE INSTÂNCIA -->
                            <div class="mb-3">
                                <label for="instance" class="form-label">
                                    <i class="fas fa-building me-2"></i>Selecione a Empresa
                                </label>
                                <select class="form-select" id="instance" name="instance" required onchange="updateInstanceInfo()">
                                    <option value="">Selecione uma empresa...</option>
                                    <?php foreach ($instances as $inst): ?>
                                        <option value="<?php echo htmlspecialchars($inst['subdomain']); ?>"
                                            data-company="<?php echo htmlspecialchars($inst['company_name']); ?>"
                                            <?php echo ($_POST['instance'] ?? $selectedInstance ?? '') === $inst['subdomain'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($inst['company_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- INFO DA INSTÂNCIA SELECIONADA -->
                            <div id="instanceInfo" class="instance-badge" style="display: none;">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span id="instanceName"></span>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email ou Usuário</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                        placeholder="Digite seu email ou usuário"
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Digite sua senha"
                                        required>
                                    <button class="btn btn-outline-secondary"
                                        type="button"
                                        onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Lembrar-me
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Entrar
                                </button>
                            </div>
                        </form>

                        <!-- CREDENCIAIS DE DEMO -->
                        <div id="demoCredentials" style="display: none;" class="mt-3 p-3 bg-light rounded">
                            <h6 class="mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                Credenciais de Demonstração
                            </h6>
                            <p class="mb-1"><strong>Usuário:</strong> admin</p>
                            <p class="mb-0"><strong>Senha:</strong> password</p>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Acesso seguro e protegido
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        function updateInstanceInfo() {
            const select = document.getElementById('instance');
            const instanceInfo = document.getElementById('instanceInfo');
            const instanceName = document.getElementById('instanceName');
            const demoCredentials = document.getElementById('demoCredentials');

            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                const companyName = selectedOption.getAttribute('data-company');

                instanceName.textContent = companyName;
                instanceInfo.style.display = 'block';

                // Mostrar credenciais demo para Mirian Dayrell
                if (select.value === 'miriandayrell') {
                    demoCredentials.style.display = 'block';
                } else {
                    demoCredentials.style.display = 'none';
                }
            } else {
                instanceInfo.style.display = 'none';
                demoCredentials.style.display = 'none';
            }
        }

        // Inicializar ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            updateInstanceInfo();
        });

        // Validação do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const instance = document.getElementById('instance').value;
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!instance) {
                e.preventDefault();
                alert('Por favor, selecione uma empresa.');
                return false;
            }

            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }
        });
    </script>
</body>

</html>