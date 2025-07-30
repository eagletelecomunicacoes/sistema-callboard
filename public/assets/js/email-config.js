console.log('🚀 NOVO EMAIL CONFIG CARREGANDO...');

// FUNÇÕES GLOBAIS
window.previewReport = function () {
    console.log('🔍 PREVIEW CHAMADO!');

    const userId = document.querySelector('meta[name="user-id"]')?.content;
    console.log('User ID:', userId);

    if (!userId) {
        alert('❌ User ID não encontrado!');
        return;
    }

    const url = window.location.origin + '/teste-mongodb/email/preview?type=daily&user_id=' + userId;
    console.log('🌐 Abrindo:', url);

    const popup = window.open(url, '_blank', 'width=1200,height=800');

    if (!popup) {
        alert('🚫 Popup bloqueado!');
    } else {
        console.log('✅ Preview aberto!');
    }
};

window.sendTestEmail = function () {
    console.log('📧 TESTE EMAIL CHAMADO!');

    const userEmail = document.querySelector('meta[name="user-email"]')?.content;

    if (confirm('Enviar teste para: ' + userEmail + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="send_test_email">';
        document.body.appendChild(form);
        form.submit();
    }
};

window.sendGlobalTest = function () {
    console.log('🌍 GLOBAL TEST CHAMADO!');

    const email = prompt('Digite o email:');
    if (email) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="send_global_test">
            <input type="hidden" name="test_email" value="${email}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
};

window.testSmtpConfig = function () {
    console.log('⚙️ SMTP TEST CHAMADO!');

    const email = prompt('Digite o email para teste:');
    if (email) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="test_smtp">
            <input type="hidden" name="test_email" value="${email}">
            <input type="hidden" name="smtp_host" value="${document.getElementById('smtp_host')?.value || ''}">
            <input type="hidden" name="smtp_port" value="${document.getElementById('smtp_port')?.value || ''}">
            <input type="hidden" name="smtp_username" value="${document.getElementById('smtp_username')?.value || ''}">
            <input type="hidden" name="smtp_password" value="${document.getElementById('smtp_password')?.value || ''}">
            <input type="hidden" name="from_email" value="${document.getElementById('from_email')?.value || ''}">
            <input type="hidden" name="from_name" value="${document.getElementById('from_name')?.value || ''}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
};

window.togglePassword = function (fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');

    if (field && icon) {
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
};

// INICIALIZAÇÃO SIMPLES
document.addEventListener('DOMContentLoaded', function () {
    console.log('📋 DOM CARREGADO - INICIALIZANDO...');

    // Só inicializar se não for página de preview
    if (!window.location.href.includes('/email/preview')) {
        setTimeout(function () {
            console.log('✅ EMAIL CONFIG INICIALIZADO!');

            // Configurar cards de relatório
            document.querySelectorAll('.report-card').forEach(card => {
                card.addEventListener('click', function (e) {
                    if (e.target.type !== 'checkbox') {
                        const checkbox = card.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            updateReportCards();
                        }
                    }
                });
            });

            // Atualizar visual dos cards
            updateReportCards();

        }, 100);
    }
});

function updateReportCards() {
    document.querySelectorAll('.report-card').forEach(card => {
        const type = card.dataset.type;
        const checkbox = document.getElementById(type + '_reports');

        if (checkbox && checkbox.checked) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });
}

console.log('🎉 EMAIL CONFIG CARREGADO!');