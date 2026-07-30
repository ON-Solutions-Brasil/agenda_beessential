# Agenda Beessential

Sistema de agendamento de reuniões com geração automática de links do Google Meet.

## Funcionalidades

- **Autenticação**: Login com controle de sessão
- **Superadmin**: Painel administrativo com permissões granulares
- **Agendamento de Reuniões**: Criação, edição e cancelamento de reuniões
- **Calendário**: Visualização por dia, semana e mês
- **Google Meet**: Geração automática de links para videoconferência
- **Gestão de Usuários**: Cadastro, edição e controle de permissões
- **Configurações do Sistema**: Painel para configurar parâmetros gerais

## Requisitos

- PHP 8.1+
- MySQL 8.0+ ou MariaDB 10.6+
- Apache com mod_rewrite habilitado
- Extensões PHP: pdo, pdo_mysql, json, mbstring, openssl

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/agenda_beessential.git
```

2. Configure o banco de dados em `config/database.php`

3. Execute as migrations SQL na pasta `database/migrations/` em ordem numérica

4. Acesse o sistema no navegador

## Estrutura do Projeto

```
agenda_beessential/
├── app/
│   ├── Controllers/     # Controladores MVC
│   ├── Core/            # Classes base do framework
│   ├── Middleware/       # Middlewares de autenticação/permissão
│   ├── Models/          # Models de acesso ao banco
│   ├── Services/        # Serviços (Google Meet, etc.)
│   └── Views/           # Templates de visualização
├── config/              # Arquivos de configuração
├── database/
│   └── migrations/      # Scripts SQL para criação/alteração do banco
├── public/              # Front controller e assets públicos
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── index.php
├── storage/
│   ├── logs/
│   └── cache/
├── .htaccess
├── index.php
└── README.md
```

## Acesso Inicial

Após executar as migrations, o sistema cria um superadmin padrão.


> ⚠️ Altere a senha padrão imediatamente após o primeiro acesso.

## Licença

Projeto proprietário - Beessential. Todos os direitos reservados.
