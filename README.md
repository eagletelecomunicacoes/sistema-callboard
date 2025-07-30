# 🎯 Sistema CDR Multi-Instância

O Sistema CDR Multi-Instância é uma aplicação web para gerenciamento de registros de chamadas telefônicas (CDRs), desenvolvida com foco em ambientes multi-empresa. Com interface moderna e recursos automatizados, oferece uma visão clara e detalhada dos dados de chamadas por instância.

## 🚀 Funcionalidades

- **Multi-instância**: Suporte a múltiplas empresas e bancos de dados isolados
- **Dashboard com métricas em tempo real**: Visão geral por empresa
- **Relatórios automáticos**: Envio diário por email via cron
- **Sistema de notificações**: Alertas e mensagens com Toastr
- **Gestão de usuários e permissões**
- **Templates de email configuráveis**
- **Interface moderna e responsiva**

## 📂 Estrutura do Projeto

    sistema-callboard/
    │
    ├── app/
    │   ├── Config/
    │   │   ├── app.php
    │   │   ├── database.php
    │   │   └── email.php
    │   ├── Controllers/
    │   │   ├── AuthController.php
    │   │   ├── DashboardController.php
    │   │   ├── EmailController.php
    │   │   ├── InstanceController.php
    │   │   ├── ReportController.php
    │   │   ├── SetupController.php
    │   │   └── UserController.php
    │   ├── Helpers/
    │   │   ├── Auth.php
    │   │   ├── Email.php
    │   │   ├── Toastr.php
    │   │   └── Utils.php
    │   ├── Models/
    │   │   ├── CDR.php
    │   │   ├── Database.php
    │   │   ├── EmailReport.php
    │   │   ├── EmailSettings.php
    │   │   ├── Instance.php
    │   │   └── User.php
    │   └── Views/
    │       ├── auth/
    │       ├── dashboard/
    │       ├── email/
    │       │   ├── templates/
    │       ├── layouts/
    │       ├── setup/
    │       └── users/
    ├── cron/
    │   └── daily_report.php
    ├── database/
    │   ├── instance_template.sql
    │   ├── schema.sql
    │   └── setup_mirian_dayrell.sql
    ├── public/
    │   ├── assets/
    │   │   ├── css/
    │   │   ├── images/
    │   │   └── js/
    │   ├── .htaccess
    │   └── index.php
    ├── storage/
    │   └── logs/
    ├── vendor/
    ├── composer.json
    └── composer.lock

## 🛠️ Tecnologias Utilizadas

- **PHP 8+** com arquitetura modular
- **MySQL/MariaDB** como banco de dados principal
- **Composer** para gerenciamento de dependências
- **Bootstrap 5.3** e **Font Awesome 6** para a interface
- **Toastr** para notificações
- **Cron Jobs** para relatórios automáticos

## 🎨 Estilos e Layout

- **Modularização CSS** para facilitar a manutenção
  - `dashboard.css`, `login-modern.css`, `email-config.css`, `user-form.css`, entre outros
- **Design responsivo** para dispositivos desktop e mobile
- **Templates reutilizáveis** com `header.php`, `footer.php`, `sidebar.php`

## ⚙️ Instalação

1. **Clone o repositório**

```bash
git clone https://github.com/seu-usuario/sistema-cdr.git
cd sistema-cdr
```

2. **Instale as dependências**

```bash
composer install
```

3. **Configure o banco de dados**

- Edite o arquivo `app/Config/database.php` com suas credenciais
- Execute o script:

```bash
mysql -u root -p < database/schema.sql
```

4. **Configure o cron job para relatórios**

```bash
0 8 * * * /usr/bin/php /caminho/para/cron/daily_report.php
```

## 🖥️ Módulos do Sistema

### Gerenciamento Multi-instância

- **Cadastro e gestão de empresas**
- **Separação de dados por instância**
- **Subdomínio e banco dedicados por empresa**

### Gestão de Usuários

- Cadastro e controle de acesso por papel:
  - `user`: Acesso básico
  - `admin`: Gestão de usuários
  - `super_admin`: Gestão de instâncias

### Sistema de Email

- Configuração SMTP por instância
- Templates HTML personalizáveis
- Preview de email
- Envio de relatórios automáticos

### Relatórios

- Relatórios diários por email
- Visualização por dashboard
- Filtros personalizáveis
- Exportação de dados

## 🗄️ Banco de Dados

### Banco Master (`schema.sql`)

```sql
instances (
  id,
  name,
  subdomain,
  database_name,
  company_name,
  status
)
```

### Banco da Instância

```sql
users (
  id,
  first_name,
  last_name,
  email,
  username,
  password,
  role,
  status
)

cdr (
  calldate,
  src,
  dst,
  duration,
  billsec,
  disposition,
  callid
)

email_settings (
  smtp_host,
  smtp_port,
  smtp_username,
  smtp_password
)
```

## 🔐 Segurança

- Autenticação de usuários com hash de senha
- Validação de sessão e permissões
- Proteção contra SQL Injection com PDO
- Estrutura MVC separada e organizada

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch:

```bash
git checkout -b minha-feature
```

3. Commit das alterações:

```bash
git commit -m 'feat: Minha nova funcionalidade'
```

4. Push para o repositório:

```bash
git push origin minha-feature
```

5. Abra um Pull Request 🚀

## 🧑‍💻 Guia para Desenvolvedores

- **Controllers**: Fluxo da aplicação e lógica
- **Models**: Regras e persistência de dados
- **Views**: Interface do usuário
- **Helpers**: Funções reutilizáveis
- **Config**: Arquivos de configuração do sistema
- **Cron**: Scripts automatizados
- **Public**: Assets (CSS, JS, imagens)

## 📱 Compatibilidade

- **Navegadores**: Chrome, Firefox, Safari, Edge
- **Dispositivos**: Compatível com desktop, tablet e smartphone
- **Sistemas Operacionais**: Windows, Linux, macOS

## 📄 Licença

Este projeto está licenciado sob a MIT License. Veja o arquivo `LICENSE` para mais informações.

---

Desenvolvido por [Lucas André Fernando]
