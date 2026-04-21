# Miniworld

Aplicação fullstack de gerenciamento de projetos e tarefas, desenvolvida como teste técnico.

---

## 📑 Índice

1. [Stack](#stack)
2. [Por que Sanctum?](#por-que-sanctum)
3. [Entidades e regras de negócio](#entidades-e-regras-de-negócio)
4. [Pré-requisitos](#pré-requisitos)
5. [Instalação](#instalação)
6. [Credenciais padrão](#credenciais-padrão)
7. [Primeiro acesso e ativação de usuário](#primeiro-acesso-e-ativação-de-usuário)
8. [Permissões](#permissões)
9. [Migrations e seeders](#migrations-e-seeders)
10. [Testes](#testes)
11. [Filas e Horizon](#filas-e-horizon)
12. [Convenção de branches](#convenção-de-branches)
13. [Convenção de commits](#convenção-de-commits)
14. [Padrões de código](#padrões-de-código)
15. [Build Docker](#build-docker)
16. [CI/CD](#cicd)
17. [Trade-offs e melhorias futuras](#trade-offs-e-melhorias-futuras)

---

## Stack

| Camada          | Tecnologia                    |
| --------------- | ----------------------------- |
| Backend         | PHP 8.4 + Laravel 12          |
| Frontend        | Vue 3 + Quasar Framework      |
| Banco de dados  | PostgreSQL 16                 |
| Cache           | Redis 7                       |
| Auth            | Laravel Sanctum (token-based) |
| Containerização | Docker + Docker Compose       |
| CI/CD           | GitHub Actions                |
| Registry        | Docker Hub                    |

---

## Por que Sanctum?

Sanctum foi escolhido por ser a solução oficial do Laravel para APIs SPA/mobile com autenticação stateless via token Bearer. É leve, sem overhead de OAuth, e adequado ao escopo do teste.

---

## Entidades e regras de negócio

### Projeto

- Nome obrigatório e único por usuário
- Status: `active` | `inactive`
- Orçamento (`budget`) opcional
- Não pode ser excluído se possuir tarefas vinculadas

### Tarefa

- Descrição obrigatória
- `project_id` obrigatório (FK para projetos)
- `predecessor_task_id` opcional (auto-FK para tarefas)
- Status: `completed` | `not_completed`
- `end_date` deve ser maior ou igual a `start_date`
- Não pode ser excluída se for predecessora de outra tarefa

---

## Pré-requisitos

- Docker >= 24
- Docker Compose >= 2.20
- Git

---

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/yanpenalva/miniworld-app.git
cd miniworld-app
```

### 2. Copie o .env

```bash
cp .env.example .env
```

As variáveis relevantes já vêm preenchidas no `.env.example`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=miniworld-app
DB_USERNAME=postgres
DB_PASSWORD=admin

REDIS_HOST=redis
REDIS_PORT=6379

APP_ADMIN_EMAIL="admin@admin.com"
APP_ADMIN_PASSWORD="123"
APP_ADMIN_ROLE="Administrador"
```

### 3. Suba os containers

```bash
docker compose up -d --build
```

### 4. Instale dependências e gere a key

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

### 5. Execute migrations e seeders

```bash
docker compose exec app php artisan migrate --seed
```

### 6. Corrija permissões (Linux/Mac)

```bash
chmod +x permissions.sh
./permissions.sh
```

### 7. Inicie o frontend

```bash
docker compose exec app npm run dev
```

A aplicação estará disponível em:

| Serviço            | URL                           |
| ------------------ | ----------------------------- |
| App (frontend/API) | http://localhost:8001         |
| Mailpit (e-mail)   | http://localhost:8025         |
| Horizon (filas)    | http://localhost:8001/horizon |

---

## Credenciais padrão

As credenciais abaixo estão expostas intencionalmente por ser um projeto de teste, para facilitar a avaliação.

### Banco de dados (PostgreSQL)

| Parâmetro | Valor           |
| --------- | --------------- |
| Host      | `localhost`     |
| Porta     | `5432`          |
| Database  | `miniworld-app` |
| Usuário   | `postgres`      |
| Senha     | `admin`         |

### Usuário administrador (após seeder)

O seeder lê as variáveis `APP_ADMIN_EMAIL`, `APP_ADMIN_PASSWORD` e `APP_ADMIN_ROLE` do `.env` para criar o usuário inicial com perfil `Administrador`.

| Parâmetro | Valor             |
| --------- | ----------------- |
| E-mail    | `admin@admin.com` |
| Senha     | `123`             |
| Perfil    | `Administrador`   |

### Redis

| Parâmetro | Valor       |
| --------- | ----------- |
| Host      | `localhost` |
| Porta     | `6379`      |
| Senha     | nenhuma     |

---

## Primeiro acesso e ativação de usuário

O fluxo de acesso segue duas etapas obrigatórias:

1. **Cadastro e verificação de e-mail** — ao se registrar, o sistema envia um e-mail de verificação. Em ambiente local, esse e-mail é capturado pelo **Mailpit** em `http://localhost:8025`. O usuário deve clicar no link de confirmação para validar o endereço.

2. **Ativação pelo administrador** — mesmo após verificar o e-mail, o usuário **não possui acesso** até que o administrador geral (criado pelo seeder) o ative manualmente no **gerenciamento de usuários**. Acesse com as credenciais do administrador e ative o cadastro na listagem de usuários.

> Esse fluxo garante controle total sobre quem pode acessar o sistema, evitando acesso não autorizado por auto-cadastro.

---

## Permissões

Se um usuário possuir as permissões corretas no banco mas ainda receber erro de acesso negado, o cache do Spatie Permission pode estar desatualizado. Rode dentro do container:

```bash
docker compose exec app php artisan permission:cache-reset
```

Para versões do Spatie Permission < 5.x:

```bash
docker compose exec app php artisan cache:forget spatie.permission.cache
```

Em caso de problemas de permissão de arquivos no host (Linux/Mac), execute o script de correção:

```bash
chmod +x permissions.sh
./permissions.sh
```

---

## Migrations e seeders

```bash
# Rodar migrations
docker compose exec app php artisan migrate

# Reverter tudo e recriar
docker compose exec app php artisan migrate:fresh

# Rodar seeders
docker compose exec app php artisan db:seed

# Tudo junto
docker compose exec app php artisan migrate:fresh --seed
```

---

## Testes

### 1. Banco de testes

Antes de rodar os testes, garanta que o banco de testes foi criado:

```bash
chmod +x docker-entrypoint-initdb.sh
./docker-entrypoint-initdb.sh
```

Esse script cria o banco `miniworld-app_test` necessário para o ambiente de testes isolado.

### 2. Rodar testes

```bash
# Todos os testes
docker compose exec app php artisan test

# Apenas testes de projetos
docker compose exec app php artisan test --group=projects

# Apenas testes de tarefas
docker compose exec app php artisan test --group=tasks

# Com cobertura (requer Xdebug ativo)
docker compose exec app php artisan test --coverage

# Modo paralelo
docker compose exec app env APP_ENV=testing php artisan test --parallel
```

### Análise estática e qualidade

```bash
# PHPStan
docker compose exec app composer run:phpstan

# PHP Insights
docker compose exec app composer run:phpinsights
```

### Documentação da API

```bash
docker compose exec app php artisan scramble:export
```

---

## Filas e Horizon

O projeto usa **Redis** como driver de filas e **Laravel Horizon** para monitoramento em tempo real.

### Como funciona

- Jobs são despachados via filas Redis com `queue:work`
- **Supervisor** gerencia o ciclo de vida dos workers (restart, retry, timeout)
- **Horizon** provê dashboard de rastreamento, métricas e falhas

### Exemplo de dispatch

```php
dispatch(new ProcessTaskJob())->onQueue('tasks');
dispatch(new SendNotificationJob())->onQueue('notifications');
```

### Acesso ao Horizon

```
http://localhost:8001/horizon
```

> Acesso restrito a usuários com perfil **Administrador**.

---

## Convenção de branches

| Branch      | Finalidade                                |
| ----------- | ----------------------------------------- |
| `main`      | Código estável, origem de tags e releases |
| `develop`   | Integração contínua de features           |
| `feature/*` | Novas funcionalidades                     |
| `fix/*`     | Correções de bugs                         |
| `chore/*`   | Tarefas de infraestrutura, dependências   |

---

## Convenção de commits

Segue **Conventional Commits**:

```
<tipo>(escopo opcional): descrição curta

feat(tasks): add predecessor validation
fix(projects): prevent deletion with active tasks
chore(docker): update php base image to 8.4
docs(readme): add deploy instructions
test(tasks): add feature tests for status update
```

Tipos aceitos: `feat`, `fix`, `chore`, `docs`, `test`, `refactor`, `perf`, `ci`.

---

## Padrões de código

- `declare(strict_types=1)` obrigatório em todos os arquivos PHP
- Nomes de métodos com no máximo 5 palavras, verbos no imperativo
- Variáveis em `camelCase`
- Rotas seguem `{resource}.{action}`
- Versionamento de API: `/api/v1/...`
- Cobertura mínima de testes: 80% de linhas
- Controllers finos — lógica em Actions

---

## Build Docker

```bash
# Build da imagem de produção
docker build -f Dockerfile.prod -t yanbpenalva/miniworld:latest .

# Rodar com compose de produção
docker compose -f docker-compose.prod.yml up -d
```

## Docker Hub

```bash
docker pull yanbpenalva/miniworld:latest
```

Repositório: https://hub.docker.com/r/yanbpenalva/miniworld-app

---

## CI/CD

O pipeline é acionado ao criar uma tag no padrão `vX.Y.Z` na branch `main`.

```bash
git tag v1.0.0
git push origin v1.0.0
```

O GitHub Actions irá:

1. Fazer checkout do código
2. Buildar a imagem usando `Dockerfile.prod`
3. Publicar no Docker Hub com a tag `v1.0.0` e `latest`

### Secrets necessários no GitHub

| Secret               | Valor                                                               |
| -------------------- | ------------------------------------------------------------------- |
| `DOCKERHUB_USERNAME` | `yanbpenalva`                                                       |
| `DOCKERHUB_TOKEN`    | Access Token gerado em hub.docker.com → Account Settings → Security |

Para gerar o token: https://hub.docker.com/settings/security → **New Access Token**.

---

## Trade-offs e melhorias futuras

- **Sanctum vs Passport**: Sanctum foi escolhido pela simplicidade. Para OAuth2 completo, Passport seria mais adequado.
- **Filas**: notificações e operações pesadas podem ser movidas para jobs com Redis como driver de fila, já disponível na infra.
- **Testes de frontend**: cobertura E2E com Playwright ou Cypress não foi incluída no escopo.
