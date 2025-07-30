<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Relatório <?= $reportType ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .preview-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .preview-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .preview-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .preview-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .content {
            padding: 40px;
        }

        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }

        .user-info-card {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 2px solid #c3e6cb;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .user-details h3 {
            color: #155724;
            margin-bottom: 8px;
            font-size: 1.3rem;
        }

        .user-details p {
            color: #155724;
            margin: 5px 0;
            font-size: 0.95rem;
        }

        .no-extension-alert {
            background: #fff3cd;
            border: 2px solid #ffeaa7;
            color: #856404;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fc, #ffffff);
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-controls {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border: 1px solid #e9ecef;
        }

        .filter-header h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .filter-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            background: white;
        }

        .filter-btn {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .main-table-section {
            margin: 30px 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.4rem;
            margin: 0;
        }

        .section-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .unified-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 800px;
        }

        .unified-table thead {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .unified-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
        }

        .unified-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: top;
        }

        .unified-table tbody tr:hover {
            background: rgba(52, 152, 219, 0.05);
        }

        .caller-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .caller-main {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .caller-type {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .type-internal {
            background: #d4edda;
            color: #155724;
        }

        .type-external {
            background: #f8d7da;
            color: #721c24;
        }

        .duration-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            text-align: center;
        }

        .duration-time {
            font-weight: 600;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .duration-category {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 8px;
            font-weight: 500;
        }

        .duration-long {
            background: #d4edda;
            color: #155724;
        }

        .duration-medium {
            background: #fff3cd;
            color: #856404;
        }

        .duration-short {
            background: #f8d7da;
            color: #721c24;
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .date-main {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .date-time {
            font-size: 0.8rem;
            color: #666;
        }

        .frequency-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
        }

        .frequency-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            min-width: 50px;
            justify-content: center;
        }

        .freq-very-high {
            background: #e74c3c;
            color: white;
        }

        .freq-high {
            background: #f39c12;
            color: white;
        }

        .freq-medium {
            background: #3498db;
            color: white;
        }

        .freq-low {
            background: #95a5a6;
            color: white;
        }

        .frequency-info small {
            font-size: 0.7rem;
            color: #666;
        }

        .callid-info {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: #666;
            text-align: center;
        }

        .pagination-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .pagination-btn {
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .pagination-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        .pagination-info {
            font-weight: 600;
            color: #2c3e50;
        }

        .summary-section {
            background: linear-gradient(135deg, #f8f9fc, #ffffff);
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-section h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            display: block;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #666;
        }

        .technical-info {
            background: #f8f9fc;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            border-left: 4px solid #3498db;
        }

        .technical-info h4 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .tech-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .tech-item:last-child {
            border-bottom: none;
        }

        .tech-item strong {
            color: #2c3e50;
        }

        .tech-item span {
            color: #666;
            text-align: right;
            max-width: 60%;
        }

        .cta-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
        }

        .cta-section h3 {
            margin-bottom: 15px;
        }

        .cta-section p {
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #667eea;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            text-decoration: none;
            color: #667eea;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 8px;
            opacity: 0.8;
        }

        .footer small {
            opacity: 0.6;
        }

        .preview-actions {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .preview-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .preview-btn:hover {
            background: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .empty-data {
            text-align: center;
            padding: 60px 40px;
            color: #666;
        }

        .empty-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-data h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .content {
                padding: 20px;
            }

            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-options {
                grid-template-columns: 1fr;
            }

            .unified-table {
                font-size: 0.85rem;
            }

            .unified-table th,
            .unified-table td {
                padding: 8px 6px;
            }

            .user-info-card {
                flex-direction: column;
                text-align: center;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .tech-grid {
                grid-template-columns: 1fr;
            }

            .tech-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .tech-item span {
                text-align: left;
                max-width: 100%;
            }

            .preview-actions {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                justify-content: center;
            }
        }

        @media print {

            .preview-actions,
            .filter-controls,
            .pagination-controls,
            .cta-section {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .preview-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="preview-actions">
        <button class="preview-btn" onclick="closePreview()">
            <i class="fas fa-times"></i> Fechar
        </button>
        <button class="preview-btn" onclick="printReport()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button class="preview-btn" onclick="exportReport()">
            <i class="fas fa-download"></i> Exportar
        </button>
    </div>

    <div class="preview-container">
        <div class="preview-header">
            <div class="preview-badge">📧 Preview</div>
            <h1>📊 Relatório <?= $reportType ?></h1>
            <p><?= $companyName ?> • <?= $reportDate ?></p>
        </div>

        <div class="content">
            <div class="welcome-section">
                <h2>Olá, <?= $userName ?>! 👋</h2>
                <?php if (!empty($userExtension)): ?>
                    <p>Aqui está o seu relatório detalhado de chamadas recebidas pelo seu ramal.</p>
                    <div class="user-info-card">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <?php if (!empty($data['user_name'])): ?>
                                <h3><i class="fas fa-id-badge"></i> <?= htmlspecialchars($data['user_name']) ?></h3>
                                <p><i class="fas fa-phone"></i> <strong>Ramal AVAYA:</strong> <?= htmlspecialchars($userExtension) ?></p>
                                <p><i class="fas fa-database"></i> <strong>Filtro MongoDB:</strong> p2device = *<?= htmlspecialchars($userExtension) ?> (chamadas RECEBIDAS)</p>
                            <?php else: ?>
                                <h3><i class="fas fa-user-circle"></i> Usuário do Ramal <?= htmlspecialchars($userExtension) ?></h3>
                                <p><i class="fas fa-phone"></i> <strong>Ramal:</strong> <?= htmlspecialchars($userExtension) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-extension-alert">
                        <h3>⚠️ Ramal não cadastrado</h3>
                        <p>Para ver estatísticas personalizadas, cadastre seu ramal no perfil.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($userExtension) && ($data['calls_count'] > 0 || $data['total_records'] > 0)): ?>
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($data['calls_count'] ?? 0) ?></span>
                        <div class="stat-label">Chamadas Únicas</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($data['total_records'] ?? 0) ?></span>
                        <div class="stat-label">Total Registros</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($data['avg_duration'] ?? 0, 1) ?>min</span>
                        <div class="stat-label">Duração Média</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($data['total_duration'] ?? 0, 1) ?>min</span>
                        <div class="stat-label">Tempo Total</div>
                    </div>
                </div>

                <div class="filter-controls">
                    <div class="filter-header">
                        <h3><i class="fas fa-filter"></i> Filtros de Visualização</h3>
                    </div>
                    <div class="filter-options">
                        <div class="filter-group">
                            <label for="dateFilter">Período:</label>
                            <select id="dateFilter">
                                <option value="all">Todas as chamadas</option>
                                <option value="today">Hoje</option>
                                <option value="yesterday">Ontem</option>
                                <option value="week">Última semana</option>
                                <option value="month">Último mês</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="typeFilter">Tipo:</label>
                            <select id="typeFilter">
                                <option value="all">Todas</option>
                                <option value="internal">Internas</option>
                                <option value="external">Externas</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="durationFilter">Duração:</label>
                            <select id="durationFilter">
                                <option value="all">Todas</option>
                                <option value="short">Curtas (< 1min)</option>
                                <option value="medium">Médias (1-5min)</option>
                                <option value="long">Longas (> 5min)</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button class="filter-btn" onclick="resetFilters()">
                                <i class="fas fa-undo"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($data['recent_calls'])): ?>
                    <div class="main-table-section">
                        <div class="section-header">
                            <h3 class="section-title">📋 Chamadas Recebidas pelo seu Ramal</h3>
                            <div class="section-subtitle">
                                <span id="totalCallsCount"><?= count($data['recent_calls']) ?></span> chamadas encontradas •
                                <span id="filteredCount"><?= count($data['recent_calls']) ?></span> exibidas
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="unified-table" id="callsTable">
                                <thead>
                                    <tr>
                                        <th onclick="sortTable(0)">
                                            <i class="fas fa-sort"></i> Quem Ligou para Você
                                        </th>
                                        <th onclick="sortTable(1)">
                                            <i class="fas fa-sort"></i> Duração da Chamada
                                        </th>
                                        <th onclick="sortTable(2)">
                                            <i class="fas fa-sort"></i> Data & Hora
                                        </th>
                                        <th onclick="sortTable(3)">
                                            <i class="fas fa-sort"></i> Frequência
                                        </th>
                                        <th>Call ID</th>
                                    </tr>
                                </thead>
                                <tbody id="callsTableBody">
                                    <?php
                                    $callerFrequency = [];
                                    foreach ($data['recent_calls'] as $call) {
                                        $caller = $call['src_number'] ?? $call['src'];
                                        $callerFrequency[$caller] = ($callerFrequency[$caller] ?? 0) + 1;
                                    }

                                    foreach ($data['recent_calls'] as $index => $call):
                                        $caller = $call['src_number'] ?? $call['src'];
                                        $frequency = $callerFrequency[$caller] ?? 1;
                                        $duration = $call['duration'] ?? 0;
                                        $isInternal = !empty($call['src_name']);

                                        $callDateForFilter = '';
                                        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $call['calldate'], $matches)) {
                                            $callDateForFilter = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                                        }
                                    ?>
                                        <tr class="call-row"
                                            data-type="<?= $isInternal ? 'internal' : 'external' ?>"
                                            data-duration="<?= $duration ?>"
                                            data-date="<?= $callDateForFilter ?>"
                                            data-index="<?= $index ?>">

                                            <td>
                                                <div class="caller-info">
                                                    <div class="caller-main">
                                                        <i class="fas fa-<?= $isInternal ? 'user' : 'phone' ?>"></i>
                                                        <?= htmlspecialchars($call['src']) ?>
                                                    </div>
                                                    <?php if (!empty($call['src_name'])): ?>
                                                        <span class="caller-type type-internal">
                                                            👤 <?= htmlspecialchars($call['src_name']) ?> (Ramal Interno)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="caller-type type-external">
                                                            📞 Número Externo
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="duration-info">
                                                    <div class="duration-time">
                                                        <i class="fas fa-clock"></i>
                                                        <?= gmdate('H:i:s', $duration) ?>
                                                    </div>
                                                    <?php if ($duration > 300): ?>
                                                        <span class="duration-category duration-long">Longa (5min+)</span>
                                                    <?php elseif ($duration > 60): ?>
                                                        <span class="duration-category duration-medium">Média (1-5min)</span>
                                                    <?php else: ?>
                                                        <span class="duration-category duration-short">Curta (&lt;1min)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="date-info">
                                                    <?php if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2}):(\d{2})/', $call['calldate'], $matches)): ?>
                                                        <div class="date-main">
                                                            <i class="fas fa-calendar"></i>
                                                            <?= $matches[1] ?>/<?= $matches[2] ?>/<?= $matches[3] ?>
                                                        </div>
                                                        <div class="date-time">
                                                            <i class="fas fa-clock"></i>
                                                            <?= $matches[4] ?>:<?= $matches[5] ?>:<?= $matches[6] ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="date-main">
                                                            <i class="fas fa-calendar"></i>
                                                            <?= htmlspecialchars($call['calldate']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="frequency-info">
                                                    <?php if ($frequency >= 10): ?>
                                                        <span class="frequency-badge freq-very-high">
                                                            <i class="fas fa-fire"></i> <?= $frequency ?>x
                                                        </span>
                                                        <small>Muito frequente</small>
                                                    <?php elseif ($frequency >= 5): ?>
                                                        <span class="frequency-badge freq-high">
                                                            <i class="fas fa-arrow-up"></i> <?= $frequency ?>x
                                                        </span>
                                                        <small>Frequente</small>
                                                    <?php elseif ($frequency >= 2): ?>
                                                        <span class="frequency-badge freq-medium">
                                                            <i class="fas fa-minus"></i> <?= $frequency ?>x
                                                        </span>
                                                        <small>Ocasional</small>
                                                    <?php else: ?>
                                                        <span class="frequency-badge freq-low">
                                                            <i class="fas fa-arrow-down"></i> <?= $frequency ?>x
                                                        </span>
                                                        <small>Única</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="callid-info">
                                                    <i class="fas fa-hashtag"></i>
                                                    <?= htmlspecialchars($call['callid'] ?? 'N/A') ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-controls">
                            <button class="pagination-btn" onclick="previousPage()" id="prevBtn">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </button>
                            <span class="pagination-info">
                                Página <span id="currentPage">1</span> de <span id="totalPages">1</span>
                            </span>
                            <button class="pagination-btn" onclick="nextPage()" id="nextBtn">
                                Próxima <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="summary-section">
                    <h3><i class="fas fa-chart-pie"></i> Resumo Analítico do Período</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-number"><?= count(array_unique(array_column($data['recent_calls'] ?? [], 'src_number'))) ?></span>
                            <div class="summary-label">Origens Únicas</div>
                        </div>
                        <div class="summary-item">
                            <span class="summary-number">
                                <?= count(array_filter($data['recent_calls'] ?? [], function ($call) {
                                    return !empty($call['src_name']);
                                })) ?>
                            </span>
                            <div class="summary-label">Chamadas Internas</div>
                        </div>
                        <div class="summary-item">
                            <span class="summary-number">
                                <?= count(array_filter($data['recent_calls'] ?? [], function ($call) {
                                    return ($call['duration'] ?? 0) > 60;
                                })) ?>
                            </span>
                            <div class="summary-label">Chamadas > 1min</div>
                        </div>
                        <div class="summary-item">
                            <span class="summary-number">
                                <?= round((($data['total_duration'] ?? 0) / max(($data['calls_count'] ?? 1), 1)), 1) ?>min
                            </span>
                            <div class="summary-label">Média Real</div>
                        </div>
                    </div>
                </div>

                <div class="cta-section">
                    <h3><i class="fas fa-rocket"></i> Acesse o Dashboard Completo</h3>
                    <p>Veja relatórios detalhados, gráficos interativos e muito mais!</p>
                    <a href="<?= $appUrl ?>/dashboard" class="cta-button">
                        <i class="fas fa-tachometer-alt"></i> Acessar Dashboard
                    </a>
                </div>

            <?php else: ?>
                <div class="empty-data">
                    <i class="fas fa-chart-line"></i>
                    <h3>Nenhum dado encontrado</h3>
                    <?php if (empty($userExtension)): ?>
                        <p>Cadastre seu ramal no perfil para ver estatísticas personalizadas.</p>
                    <?php else: ?>
                        <p>Não foram encontradas chamadas recebidas pelo ramal <?= htmlspecialchars($userExtension) ?>.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="technical-info">
                <h4><i class="fas fa-info-circle"></i> Informações Técnicas do Relatório</h4>
                <div class="tech-grid">
                    <div class="tech-item">
                        <strong><i class="fas fa-calendar-alt"></i> Período Analisado:</strong>
                        <span><?= htmlspecialchars($data['period'] ?? 'Todos os dados disponíveis') ?></span>
                    </div>
                    <div class="tech-item">
                        <strong><i class="fas fa-database"></i> Fonte dos Dados:</strong>
                        <span>MongoDB Atlas - Coleção: cdrs.mdi</span>
                    </div>
                    <div class="tech-item">
                        <strong><i class="fas fa-user-tag"></i> Usuário AVAYA:</strong>
                        <span><?= !empty($data['user_name']) ? htmlspecialchars($data['user_name']) . ' (Ramal: ' . htmlspecialchars($userExtension) . ')' : 'Ramal: ' . htmlspecialchars($userExtension) ?></span>
                    </div>
                    <div class="tech-item">
                        <strong><i class="fas fa-filter"></i> Filtro MongoDB:</strong>
                        <span>p2device = /^[A-Z]<?= htmlspecialchars($userExtension) ?>$/ (chamadas RECEBIDAS)</span>
                    </div>
                    <div class="tech-item">
                        <strong><i class="fas fa-clock"></i> Gerado em:</strong>
                        <span><?= date('d/m/Y H:i:s') ?> (Horário de Brasília)</span>
                    </div>
                    <div class="tech-item">
                        <strong><i class="fas fa-chart-bar"></i> Total de Registros:</strong>
                        <span><?= number_format($data['total_records'] ?? 0) ?> registros processados</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong><?= $companyName ?></strong></p>
            <p>Relatório gerado automaticamente pelo Sistema CDR</p>
            <small>Este é um preview do email que será enviado.</small>
        </div>
    </div>

    <script>
        // ===== JAVASCRIPT INLINE COMPLETO - SEM DEPENDÊNCIAS EXTERNAS =====
        console.log('🚀 Iniciando Report Template...');

        // Variáveis globais
        let currentPage = 1;
        let itemsPerPage = 50;
        let filteredRows = [];
        let allRows = [];
        let sortDirection = {};

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                try {
                    initializeTable();
                    initializeFilters();
                    updatePagination();
                    console.log('✅ Relatório carregado!');
                } catch (error) {
                    console.error('❌ Erro:', error);
                }
            }, 200);
        });

        function initializeTable() {
            const tableBody = document.getElementById('callsTableBody');
            if (tableBody) {
                allRows = Array.from(tableBody.querySelectorAll('.call-row'));
                filteredRows = [...allRows];
                updateTableDisplay();
                console.log(`📞 ${allRows.length} chamadas carregadas`);
            } else {
                allRows = [];
                filteredRows = [];
            }
        }

        function initializeFilters() {
            const dateFilter = document.getElementById('dateFilter');
            const typeFilter = document.getElementById('typeFilter');
            const durationFilter = document.getElementById('durationFilter');

            if (dateFilter) dateFilter.addEventListener('change', applyFilters);
            if (typeFilter) typeFilter.addEventListener('change', applyFilters);
            if (durationFilter) durationFilter.addEventListener('change', applyFilters);
        }

        function applyFilters() {
            try {
                const dateFilter = document.getElementById('dateFilter');
                const typeFilter = document.getElementById('typeFilter');
                const durationFilter = document.getElementById('durationFilter');

                const dateValue = dateFilter ? dateFilter.value : 'all';
                const typeValue = typeFilter ? typeFilter.value : 'all';
                const durationValue = durationFilter ? durationFilter.value : 'all';

                filteredRows = allRows.filter(row => {
                    if (dateValue !== 'all') {
                        const rowDate = row.dataset.date;
                        const today = new Date().toISOString().split('T')[0];
                        const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                        const weekAgo = new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0];
                        const monthAgo = new Date(Date.now() - 30 * 86400000).toISOString().split('T')[0];

                        switch (dateValue) {
                            case 'today':
                                if (rowDate !== today) return false;
                                break;
                            case 'yesterday':
                                if (rowDate !== yesterday) return false;
                                break;
                            case 'week':
                                if (rowDate < weekAgo) return false;
                                break;
                            case 'month':
                                if (rowDate < monthAgo) return false;
                                break;
                        }
                    }

                    if (typeValue !== 'all') {
                        const rowType = row.dataset.type;
                        if (rowType !== typeValue) return false;
                    }

                    if (durationValue !== 'all') {
                        const duration = parseInt(row.dataset.duration) || 0;
                        switch (durationValue) {
                            case 'short':
                                if (duration >= 60) return false;
                                break;
                            case 'medium':
                                if (duration < 60 || duration > 300) return false;
                                break;
                            case 'long':
                                if (duration <= 300) return false;
                                break;
                        }
                    }

                    return true;
                });

                currentPage = 1;
                updateTableDisplay();
                updatePagination();
                updateFilteredCount();
            } catch (error) {
                console.error('❌ Erro nos filtros:', error);
            }
        }

        function resetFilters() {
            try {
                const dateFilter = document.getElementById('dateFilter');
                const typeFilter = document.getElementById('typeFilter');
                const durationFilter = document.getElementById('durationFilter');

                if (dateFilter) dateFilter.value = 'all';
                if (typeFilter) typeFilter.value = 'all';
                if (durationFilter) durationFilter.value = 'all';

                filteredRows = [...allRows];
                currentPage = 1;
                updateTableDisplay();
                updatePagination();
                updateFilteredCount();
            } catch (error) {
                console.error('❌ Erro ao resetar:', error);
            }
        }

        function updateFilteredCount() {
            try {
                const filteredCountElement = document.getElementById('filteredCount');
                const totalCallsCountElement = document.getElementById('totalCallsCount');

                if (filteredCountElement) {
                    filteredCountElement.textContent = filteredRows.length;
                }

                if (totalCallsCountElement) {
                    totalCallsCountElement.textContent = allRows.length;
                }
            } catch (error) {
                console.error('❌ Erro ao atualizar contadores:', error);
            }
        }

        function sortTable(columnIndex) {
            try {
                const column = ['caller', 'duration', 'date', 'frequency'][columnIndex];
                const isAscending = sortDirection[column] !== 'asc';
                sortDirection[column] = isAscending ? 'asc' : 'desc';

                filteredRows.sort((a, b) => {
                    let aValue, bValue;

                    switch (column) {
                        case 'caller':
                            const aCallerEl = a.querySelector('.caller-main');
                            const bCallerEl = b.querySelector('.caller-main');
                            aValue = aCallerEl ? aCallerEl.textContent.trim() : '';
                            bValue = bCallerEl ? bCallerEl.textContent.trim() : '';
                            break;
                        case 'duration':
                            aValue = parseInt(a.dataset.duration) || 0;
                            bValue = parseInt(b.dataset.duration) || 0;
                            break;
                        case 'date':
                            aValue = new Date(a.dataset.date || '1970-01-01');
                            bValue = new Date(b.dataset.date || '1970-01-01');
                            break;
                        case 'frequency':
                            const aFreqEl = a.querySelector('.frequency-badge');
                            const bFreqEl = b.querySelector('.frequency-badge');
                            aValue = aFreqEl ? parseInt(aFreqEl.textContent.replace(/[^\d]/g, '')) || 0 : 0;
                            bValue = bFreqEl ? parseInt(bFreqEl.textContent.replace(/[^\d]/g, '')) || 0 : 0;
                            break;
                        default:
                            return 0;
                    }

                    if (aValue < bValue) return isAscending ? -1 : 1;
                    if (aValue > bValue) return isAscending ? 1 : -1;
                    return 0;
                });

                updateTableDisplay();
                updateSortIcons(columnIndex, isAscending);
            } catch (error) {
                console.error('❌ Erro na ordenação:', error);
            }
        }

        function updateSortIcons(activeColumn, isAscending) {
            try {
                document.querySelectorAll('.unified-table th i').forEach(icon => {
                    if (icon) icon.className = 'fas fa-sort';
                });

                const activeHeader = document.querySelectorAll('.unified-table th')[activeColumn];
                if (activeHeader) {
                    const icon = activeHeader.querySelector('i');
                    if (icon) {
                        icon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    }
                }
            } catch (error) {
                console.error('❌ Erro nos ícones:', error);
            }
        }

        function updateTableDisplay() {
            try {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                allRows.forEach(row => {
                    if (row && row.style) {
                        row.style.display = 'none';
                    }
                });

                filteredRows.slice(startIndex, endIndex).forEach(row => {
                    if (row && row.style) {
                        row.style.display = '';
                    }
                });
            } catch (error) {
                console.error('❌ Erro na tabela:', error);
            }
        }

        function updatePagination() {
            try {
                const totalPages = Math.ceil(filteredRows.length / itemsPerPage);

                const currentPageEl = document.getElementById('currentPage');
                const totalPagesEl = document.getElementById('totalPages');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                if (currentPageEl) {
                    currentPageEl.textContent = currentPage;
                } else {
                    console.warn('⚠️ currentPage não encontrado');
                }

                if (totalPagesEl) {
                    totalPagesEl.textContent = totalPages;
                } else {
                    console.warn('⚠️ totalPages não encontrado');
                }

                if (prevBtn) {
                    prevBtn.disabled = currentPage <= 1;
                } else {
                    console.warn('⚠️ prevBtn não encontrado');
                }

                if (nextBtn) {
                    nextBtn.disabled = currentPage >= totalPages || totalPages === 0;
                } else {
                    console.warn('⚠️ nextBtn não encontrado');
                }
            } catch (error) {
                console.error('❌ Erro na paginação:', error);
            }
        }

        function previousPage() {
            try {
                if (currentPage > 1) {
                    currentPage--;
                    updateTableDisplay();
                    updatePagination();
                }
            } catch (error) {
                console.error('❌ Erro página anterior:', error);
            }
        }

        function nextPage() {
            try {
                const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTableDisplay();
                    updatePagination();
                }
            } catch (error) {
                console.error('❌ Erro próxima página:', error);
            }
        }

        function closePreview() {
            try {
                if (window.opener) {
                    window.close();
                } else {
                    history.back();
                }
            } catch (error) {
                console.error('❌ Erro ao fechar:', error);
                history.back();
            }
        }

        function printReport() {
            try {
                filteredRows.forEach(row => {
                    if (row && row.style) {
                        row.style.display = '';
                    }
                });

                window.print();

                setTimeout(() => {
                    updateTableDisplay();
                }, 1000);
            } catch (error) {
                console.error('❌ Erro ao imprimir:', error);
            }
        }

        function exportReport() {
            try {
                alert('Funcionalidade de exportação será implementada em breve!');
            } catch (error) {
                console.error('❌ Erro ao exportar:', error);
            }
        }

        // Função global para preview (compatibilidade)
        function previewReport() {
            try {
                const userId = document.querySelector('meta[name="user-id"]')?.content || '';
                const baseUrl = window.location.origin + '/teste-mongodb';
                const url = `${baseUrl}/email/preview?type=daily&user_id=${userId}`;

                console.log('🔗 Abrindo preview:', url);
                window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            } catch (error) {
                console.error('❌ Erro ao abrir preview:', error);
                alert('Erro ao abrir preview do relatório');
            }
        }

        console.log('📊 Relatório carregado com sucesso!');
    </script>
</body>

</html>