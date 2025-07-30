# 🎯 Sistema CDR Multi-Instância

Sistema completo de gerenciamento de CDR (Call Detail Record) desenvolvido em PHP, com suporte a múltiplas empresas, relatórios automáticos e sistema de notificações.

## 🚀 Funcionalidades

- ✅ **Multi-instância** - Suporte a múltiplas empresas
- ✅ **Dashboard** - Estatísticas e métricas em tempo real
- ✅ **Gestão de Usuários** - CRUD completo com controle de acesso
- ✅ **Sistema de Email** - SMTP + templates + relatórios automáticos
- ✅ **Relatórios** - Diários automáticos via cron job
- ✅ **Interface Moderna** - Bootstrap 5 + design responsivo

## ��️ Tecnologias

- **Backend**: PHP 8+
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: Bootstrap 5.3, Font Awesome 6
- **Dependências**: Composer
- **Notificações**: Toastr
- **Automação**: Cron Jobs

## �� Estrutura do Projeto

🏗️ ESTRUTURA ATUAL COMPLETA                                            
sistema-callboard/
│
├── app/
│ ├── Config/
│ │ ├── app.php
│ │ ├── database.php
│ │ └── email.php
│ │
│ ├── Controllers/
│ │ ├── AuthController.php
│ │ ├── DashboardController.php
│ │ ├── EmailController.php
│ │ ├── InstanceController.php
│ │ ├── ReportController.php
│ │ ├── SetupController.php
│ │ └── UserController.php
│ │
│ ├── Helpers/
│ │ ├── Auth.php
│ │ ├── Email.php
│ │ ├── Toastr.php
│ │ └── Utils.php
│ │
│ ├── Models/
│ │ ├── CDR.php
│ │ ├── Database.php
│ │ ├── EmailReport.php
│ │ ├── EmailSettings.php
│ │ ├── Instance.php
│ │ └── User.php
│ │
│ └── Views/
│ ├── auth/
│ │ ├── login.php
│ │ └── register.php
│ │
│ ├── dashboard/
│ │ ├── index.php
│ │ └── reports.php
│ │
│ ├── email/
│ │ ├── templates/
│ │ │ └── report-template.php
│ │ ├── admin-config.php
│ │ ├── config.php
│ │ └── preview.php
│ │
│ ├── layouts/
│ │ ├── footer.php
│ │ ├── header.php
│ │ └── sidebar.php
│ │
│ ├── setup/
│ │ └── first-admin.php
│ │
│ └── users/
│ ├── create.php
│ ├── edit.php
│ └── index.php
│
├── cron/
│ └── daily_report.php
│
├── database/
│ ├── instance_template.sql
│ ├── schema.sql
│ └── setup_mirian_dayrell.sql
│
├── public/
│ ├── assets/
│ │ ├── css/
│ │ │ ├── dashboard.css
│ │ │ ├── email-config.css
│ │ │ ├── login-modern.css
│ │ │ ├── style.css
│ │ │ ├── toastr-custom.css
│ │ │ ├── user-form.css
│ │ │ └── users.css
│ │ │
│ │ ├── images/
│ │ │ ├── eagle-telecom-logo.png
│ │ │ └── mirian-dayrell-logo.png
│ │ │
│ │ └── js/
│ │ └── main.js
│ │
│ ├── .htaccess
│ └── index.php
│
├── storage/
│ └── logs/
│
├── vendor/
│ └── .htaccess
│
├── composer.json
└── composer.lock

## ⚡ Instalação

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/sistema-cdr.git
cd sistema-cdr

# Instale as dependências
composer install

# Configure o banco de dados
# Edite app/Config/database.php

# Execute os scripts SQL
mysql -u root -p < database/schema.sql

# Configure o cron job
0 8 * * * /usr/bin/php /caminho/para/cron/daily_report.php
�� Autenticação
user: Acesso básico
admin: Gestão de usuários
super_admin: Gestão de instâncias
�� Sistema de Email
Configurações SMTP
Templates personalizáveis
Relatórios automáticos
Preview de emails
📊 Relatórios
Dashboard com métricas
Relatórios diários automáticos
Exportação de dados
Filtros personalizáveis
🎨 Interface
Bootstrap 5.3
Design responsivo
Notificações Toastr
CSS modular

## 🗄️ Banco de Dados

-- Banco Master
instances (id, name, subdomain, database_name, company_name, status)

-- Bancos por Instância
users (id, first_name, last_name, email, username, password, role, status)
cdr (calldate, src, dst, duration, billsec, disposition, callid)
email_settings (smtp_host, smtp_port, smtp_username, smtp_password)
email_settings (smtp_host, smtp_port, smtp_username, smtp_password)


## 📝 Contribuição
Fork o projeto
Crie uma branch (git checkout -b feature/nova-feature)
Commit (git commit -m 'Adiciona nova feature')
Push (git push origin feature/nova-feature)
Pull Request


## 📄 Licença
MIT License
```
