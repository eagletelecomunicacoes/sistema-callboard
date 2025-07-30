<?php
$pageTitle = 'Configuração Inicial';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistema CDR</title>

    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/toastr-custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="system-title">
                <h1>Configuração Inicial</h1>
                <p>Criar Primeiro Administrador</p>
            </div>
        </div>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    Nome de Usuário
                </label>
                <input type="text"
                    id="username"
                    name="username"
                    required
                    placeholder="admin"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="admin@miriandayrell.com.br"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@miriandayrell.com.br'); ?>">
            </div>

            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-id-card"></i>
                    Nome Completo
                </label>
                <input type="text"
                    id="full_name"
                    name="full_name"
                    required
                    placeholder="Administrador do Sistema"
                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Administrador do Sistema'); ?>">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Senha
                </label>
                <input type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Mínimo 6 caracteres"
                    value="admin">
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-rocket"></i>
                Criar Administrador
            </button>
        </form>

        <div class="login-footer">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Eagle Telecom. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "5000"
                };
            }
        });
    </script>

    <?php
    require_once '../app/Helpers/Toastr.php';
    echo Toastr::render();
    ?>
</body>

</html>