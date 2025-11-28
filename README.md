# 📋 TodoList Backend

Backend da aplicação TodoList desenvolvido com Laravel 12, PostgreSQL 16 e Docker.

## 🚀 Como Rodar

```bash
# Clone o repositório
git clone git@github.com:HitaloDev/todolist-backend.git
cd todolist-backend

# Crie o arquivo .env
cp .env.example .env

# Suba os containers
docker compose up -d --build
```

Pronto! API disponível em `http://localhost:9000` 🎉

## 🧪 Testes

```bash
docker compose exec app php artisan test
```

## 🛠️ Tecnologias

- PHP 8.2+
- Laravel 12
- PostgreSQL 16
- Docker & Docker Compose

## 🏗️ Arquitetura

Projeto desenvolvido seguindo princípios de Clean Architecture:

- **Repository Pattern** - Abstração da camada de dados
- **Service Layer** - Lógica de negócio centralizada
- **Dependency Injection** - Baixo acoplamento
- **Form Request Validation** - Validações organizadas
- **Soft Deletes** - Segurança dos dados
