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
📁 teste-mongodb/                                                              
├── 📁 app/                                                          
│ ├── 📁 Config/                                                            
│ │ ├── 📄 app.php # ✅ Configurações gerais da aplicação
│ │ ├── 📄 database.php # ✅ Configurações de banco de dados
│ │ └── 📄 email.php # ✅ Configurações de email
│ │                                                             
│ ├── 📁 Controllers/                                                      
│ │ ├── 📄 AuthController.php # ✅ Autenticação e login
│ │ ├── 📄 DashboardController.php # ✅ Dashboard principal
│ │ ├── 📄 EmailController.php # ✅ Configurações e envio de email
│ │ ├── 📄 InstanceController.php # ✅ Gestão de instâncias
│ │ ├── 📄 ReportController.php # ✅ Relatórios do sistema
│ │ ├── 📄 SetupController.php # ✅ Configuração inicial
│ │ └── 📄 UserController.php # ✅ Gestão de usuários
│ │                                                                  
│ ├── 📁 Helpers/                                             
│ │ ├── 📄 Auth.php # ✅ Helper de autenticação
│ │ ├── 📄 Email.php # ✅ Helper de email
│ │ ├── 📄 Toastr.php # ✅ Notificações toast
│ │ └── 📄 Utils.php # ✅ Utilitários gerais
│ │                                                                        
│ ├── 📁 Models/                                                     
│ │ ├── 📄 CDR.php # ✅ Modelo de registros CDR
│ │ ├── 📄 Database.php # ✅ Modelo de banco de dados
│ │ ├── 📄 EmailReport.php # ✅ Relatórios por email
│ │ ├── 📄 EmailSettings.php # ✅ Configurações de email
│ │ ├── 📄 Instance.php # ✅ Modelo de instâncias
│ │ └── 📄 User.php # ✅ Modelo de usuário
│ │                                                                  
│ └── 📁 Views/                                                 
│ ├── 📁 auth/                                                   
│ │ ├── 📄 login.php # ✅ Página de login
│ │ └── 📄 register.php # ✅ Página de registro
│ │                                                           
│ ├── 📁 dashboard/
│ │ ├── 📄 index.php # ✅ Dashboard principal
│ │ └── 📄 reports.php # ✅ Página de relatórios
│ │                                                            
│ ├── 📁 email/                                                 
│ │ ├── 📁 templates/                                             
│ │ │ └── �� report-template.php # ✅ Template de relatório
│ │ ├── 📄 admin-config.php # ✅ Config admin de email
│ │ ├── 📄 config.php # ✅ Configurações de email
│ │ └── 📄 preview.php # ✅ Preview de emails
│ │                                                                  
│ ├── 📁 layouts/                                                      
│ │ ├── 📄 footer.php # ✅ Rodapé da aplicação
│ │ ├── 📄 header.php # ✅ Cabeçalho da aplicação
│ │ └── 📄 sidebar.php # ✅ Menu lateral
│ │                                                                          
│ ├── 📁 setup/                                                   
│ │ └── 📄 first-admin.php # ✅ Configuração do primeiro admin
│ │                                                                    
│ └── 📁 users/
│ ├── 📄 create.php # ✅ Criar usuário
│ ├── 📄 edit.php # ✅ Editar usuário
│ └── 📄 index.php # ✅ Lista de usuários
│                                                                           
├── 📁 cron/                                                      
│ └── 📄 daily_report.php # ✅ Relatório diário automatizado
│                                                                         
├── 📁 database/                                                         
│ ├── 📄 instance_template.sql # ✅ Template para novas instâncias
│ ├── 📄 schema.sql # ✅ Schema principal do banco
│ └── 📄 setup_mirian_dayrell.sql # ✅ Setup da instância Mirian Dayrell
│                                                                             
├── 📁 public/                                                                                                                                                     
│ ├── 📁 assets/                                                                                                                   
│ │ ├── 📁 css/                                                                                                                           
│ │ │ ├── 📄 dashboard.css # ✅ Estilos do dashboard
│ │ │ ├── 📄 email-config.css # ✅ Estilos config email
│ │ │ ├── 📄 login-modern.css # ✅ Estilos modernos do login
│ │ │ ├── 📄 style.css # ✅ Estilos principais
│ │ │ ├── 📄 toastr-custom.css # ✅ Estilos personalizados toastr
│ │ │ ├── 📄 user-form.css # ✅ Estilos formulários usuário
│ │ │ └── 📄 users.css # ✅ Estilos página usuários
│ │ │
│ │ ├── 📁 images/                                                                 
│ │ │ ├── 🖼️ eagle-telecom-logo.png # ✅ Logo Eagle Telecom
│ │ │ └── 🖼️ mirian-dayrell-logo.png # ✅ Logo Mirian Dayrell
│ │ │
│ │ └── 📁 js/                                                                      
│ │ └── 📄 main.js # ✅ JavaScript principal
│ │                                                                                 
│ ├── 📄 .htaccess # ✅ Configurações Apache                                
│ └── 📄 index.php # ✅ Ponto de entrada
│                                                                     
├── 📁 storage/                                                            
│ └── 📁 logs/ # ✅ Diretório de logs                                                 
│                                                                        
├── 📁 vendor/ # ✅ Dependências Composer                                                  
│ └── 📄 .htaccess # ✅ Proteção vendor                                               
│                                                         
├── 📄 composer.json # ✅ Dependências PHP                      
└── �� composer.lock # ✅ Lock das versões                                
                                                           
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
