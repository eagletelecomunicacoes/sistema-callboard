    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- MAIN JS - CARREGAMENTO DIRETO -->
    <script src="<?= APP_URL ?>/assets/js/main.js"></script>

    <!-- Page specific JavaScript -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Toastr Messages -->
    <?php if (isset($_SESSION['toastr'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php foreach ($_SESSION['toastr'] as $message): ?>
                    setTimeout(() => {
                        if (typeof toastr !== 'undefined') {
                            toastr.<?= $message['type'] ?>('<?= addslashes($message['message']) ?>');
                        }
                    }, 100);
                <?php endforeach; ?>
            });
        </script>
        <?php unset($_SESSION['toastr']); ?>
    <?php endif; ?>

    </body>

    </html>