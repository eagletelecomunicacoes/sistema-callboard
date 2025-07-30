<?php
// Script para ser executado pelo CRON diariamente
// Adicione no crontab: 0 8 * * * /usr/bin/php /caminho/para/cron/daily_report.php

// Configuração de timezone
date_default_timezone_set('America/Sao_Paulo');

// Inclui dependências
require_once '../app/Config/app.php';
require_once '../app/Controllers/EmailController.php';

// Log de execução
$logFile = '../storage/logs/cron_' . date('Y-m-d') . '.log';

function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

writeLog("Iniciando execução do relatório diário");

try {
    // Verifica se é o horário correto
    $currentTime = date('H:i');
    $scheduledTime = EmailConfig::DAILY_REPORT_TIME;

    if (!EmailConfig::DAILY_REPORT_ENABLED) {
        writeLog("Relatório diário está desabilitado");
        exit;
    }

    // Permite uma margem de 5 minutos
    $currentMinutes = (int)date('H') * 60 + (int)date('i');
    $scheduledMinutes = (int)substr($scheduledTime, 0, 2) * 60 + (int)substr($scheduledTime, 3, 2);

    if (abs($currentMinutes - $scheduledMinutes) > 5) {
        writeLog("Fora do horário programado. Atual: $currentTime, Programado: $scheduledTime");
        exit;
    }

    // Verifica se já foi enviado hoje
    $sentToday = file_exists('../storage/logs/sent_' . date('Y-m-d') . '.flag');
    if ($sentToday) {
        writeLog("Relatório já foi enviado hoje");
        exit;
    }

    writeLog("Iniciando envio do relatório");

    // Executa o envio
    $emailController = new EmailController();
    ob_start();
    $emailController->sendDaily();
    $output = ob_get_clean();

    writeLog("Resultado: $output");

    // Marca como enviado
    file_put_contents('../storage/logs/sent_' . date('Y-m-d') . '.flag', date('Y-m-d H:i:s'));

    writeLog("Relatório enviado com sucesso");
} catch (Exception $e) {
    writeLog("Erro: " . $e->getMessage());

    // Envia email de erro para admin (opcional)
    try {
        $adminEmail = 'admin@eagletelecom.com.br';
        $subject = 'Erro no Relatório CDR Automático';
        $message = "Erro ao executar relatório diário:\n\n" . $e->getMessage() . "\n\nHorário: " . date('Y-m-d H:i:s');

        mail($adminEmail, $subject, $message);
        writeLog("Email de erro enviado para admin");
    } catch (Exception $emailError) {
        writeLog("Erro ao enviar email de erro: " . $emailError->getMessage());
    }
}

writeLog("Execução finalizada");
