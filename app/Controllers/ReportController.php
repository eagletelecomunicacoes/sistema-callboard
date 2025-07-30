<?php
require_once '../app/Helpers/Auth.php';
require_once '../app/Helpers/Toastr.php';

class ReportController
{

    public function index()
    {
        if (!Auth::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $pageTitle = 'Relatórios';
        $currentPage = 'reports';

        require_once '../app/Views/layouts/header.php';
?>

        <div class="page-content">
            <div class="content-header">
                <h2>📊 Relatórios</h2>
                <p>Gere relatórios personalizados das chamadas</p>
            </div>

            <div class="reports-grid">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="report-content">
                        <h3>Relatório Diário</h3>
                        <p>Chamadas do dia atual</p>
                        <button class="btn btn-primary">Gerar</button>
                    </div>
                </div>

                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="report-content">
                        <h3>Relatório Semanal</h3>
                        <p>Chamadas da semana</p>
                        <button class="btn btn-primary">Gerar</button>
                    </div>
                </div>

                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="report-content">
                        <h3>Relatório Mensal</h3>
                        <p>Chamadas do mês</p>
                        <button class="btn btn-primary">Gerar</button>
                    </div>
                </div>

                <div class="report-card">
                    <div class="report-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div class="report-content">
                        <h3>Relatório Personalizado</h3>
                        <p>Filtros customizados</p>
                        <button class="btn btn-primary">Configurar</button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .page-content {
                padding: 2rem;
            }

            .content-header {
                margin-bottom: 2rem;
            }

            .content-header h2 {
                font-size: 1.5rem;
                color: #111827;
                margin-bottom: 0.5rem;
            }

            .content-header p {
                color: #6b7280;
            }

            .reports-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
            }

            .report-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border: 1px solid #e5e7eb;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .report-icon {
                width: 60px;
                height: 60px;
                background: #3b82f6;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
            }

            .report-content h3 {
                font-size: 1.125rem;
                color: #111827;
                margin-bottom: 0.5rem;
            }

            .report-content p {
                color: #6b7280;
                margin-bottom: 1rem;
                font-size: 0.875rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .btn-primary {
                background: #3b82f6;
                color: white;
            }

            .btn-primary:hover {
                background: #2563eb;
            }
        </style>

<?php
        require_once '../app/Views/layouts/footer.php';
    }
}
?>