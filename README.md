# 📋 TodoList Backend

Backend da aplicação TodoList desenvolvido com Laravel 12, PostgreSQL 16 e Docker.

## 🚀 Como Rodar o Projeto

### Pré-requisitos
- Docker
- Docker Compose

### Configuração e Execução

```bash
# Clone o repositório
git clone git@github.com:HitaloDev/todolist-backend.git
cd todolist-backend

# Crie o arquivo .env
cp .env.example .env

# Suba os containers
docker compose up -d --build
```

Pronto! O backend estará disponível em `http://localhost:9000` 🎉

## 🏗️ Arquitetura

O projeto segue os princípios de **Clean Architecture** com as seguintes camadas:

- **Controllers**: Recebem requests e retornam responses
- **Services**: Contêm a lógica de negócio
- **Repositories**: Abstraem o acesso aos dados
- **Models**: Representam as entidades do domínio
- **Requests**: Validam os dados de entrada

### Padrões Utilizados

- Repository Pattern
- Dependency Injection
- Service Layer
- Form Request Validation
- Soft Deletes

## 🛠️ Tecnologias

- PHP 8.2+
- Laravel 12
- PostgreSQL 16
- Docker & Docker Compose

## 📦 Estrutura do Banco de Dados

### Tabela: tasks

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | integer | Identificador único |
| title | string | Título da task |
| description | text | Descrição detalhada |
| status | enum | pending, in_progress, completed |
| priority | enum | low, medium, high |
| due_date | date | Data de vencimento |
| completed_at | timestamp | Data de conclusão |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |
| deleted_at | timestamp | Soft delete |

## 📝 Decisões Técnicas

1. **Repository Pattern**: Abstração da camada de dados para facilitar testes e manutenção
2. **Service Layer**: Centraliza a lógica de negócio, mantendo controllers magros
3. **Soft Deletes**: Permite recuperação de dados deletados acidentalmente
4. **Custom Exceptions**: Melhor tratamento e comunicação de erros
5. **Form Requests**: Validações desacopladas e reutilizáveis
6. **Docker**: Garante ambiente consistente em qualquer máquina

## 📄 Licença

Este projeto foi desenvolvido como parte de um desafio técnico.
