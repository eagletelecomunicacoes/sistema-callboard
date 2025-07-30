<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'CDR System' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- CARREGAMENTO DIRETO DOS CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/topbar.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/email-config.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login-modern.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/toastr-custom.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/user-form.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/users.css">

    <!-- Page specific CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>