# Miniworld

Aplicação fullstack de gerenciamento de projetos e tarefas, desenvolvida como teste técnico.

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

## Por que Sanctum?

Sanctum foi escolhido por ser a solução oficial do Laravel para APIs SPA/mobile com autenticação stateless via token Bearer. É leve, sem overhead de OAuth, e adequado ao escopo do teste.

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

## Pré-requisitos

- Docker >= 24
- Docker Compose >= 2.20
- Git

## Como rodar localmente

```bash
# 1. Clone o repositório
git clone https://github.com/yanbpenalva/miniworld.git
cd miniworld

# 2. Copie o .env
cp .env.example .env

# 3. Suba os containers
docker compose up -d --build

# 4. Instale dependências e gere a key
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 5. Execute as migrations
docker compose exec app php artisan migrate

# 6. (Opcional) Popule com dados de exemplo
docker compose exec app php artisan db:seed
```

A aplicação estará disponível em:

| Serviço            | URL                   |
| ------------------ | --------------------- |
| App (frontend/API) | http://localhost:8001 |
| Mailpit (e-mail)   | http://localhost:8025 |

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

### Usuário demo (após seeder)

| Parâmetro | Valor                 |
| --------- | --------------------- |
| E-mail    | `demo@miniworld.test` |
| Senha     | `password`            |

### Redis

| Parâmetro | Valor       |
| --------- | ----------- |
| Host      | `localhost` |
| Porta     | `6379`      |
| Senha     | nenhuma     |

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

## Executar testes

```bash
# Todos os testes
docker compose exec app php artisan test

# Apenas testes de projetos
docker compose exec app php artisan test --group=projects

# Apenas testes de tarefas
docker compose exec app php artisan test --group=tasks

# Com cobertura (requer Xdebug ativo)
docker compose exec app php artisan test --coverage
```

## Convenção de branches (Gitflow simplificado)

| Branch      | Finalidade                                |
| ----------- | ----------------------------------------- |
| `main`      | Código estável, origem de tags e releases |
| `develop`   | Integração contínua de features           |
| `feature/*` | Novas funcionalidades                     |
| `fix/*`     | Correções de bugs                         |
| `chore/*`   | Tarefas de infraestrutura, dependências   |

## Convenção de commits (Conventional Commits)

```
<tipo>(escopo opcional): descrição curta

feat(tasks): add predecessor validation
fix(projects): prevent deletion with active tasks
chore(docker): update php base image to 8.4
docs(readme): add deploy instructions
test(tasks): add feature tests for status update
```

Tipos aceitos: `feat`, `fix`, `chore`, `docs`, `test`, `refactor`, `perf`, `ci`.

## Build Docker

```bash
# Build da imagem de produção
docker build -f Dockerfile.prod -t yanbpenalva/miniworld:latest .

# Rodar com compose de produção
docker compose -f docker-compose.prod.yml up -d
```

## Docker Hub

Imagem pública disponível em:

```
docker pull yanbpenalva/miniworld:latest
```

Repositório: https://hub.docker.com/r/yanbpenalva/miniworld

## CI/CD

O pipeline é acionado automaticamente ao criar uma tag no padrão `vX.Y.Z` na branch `main`.

```bash
# Criar e publicar uma tag
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

## Trade-offs e melhorias futuras

- **Sanctum vs Passport**: Sanctum foi escolhido pela simplicidade. Para OAuth2 completo, Passport seria mais adequado.
- **Progresso do projeto**: o cálculo de percentual de tarefas concluídas está previsto mas não implementado; pode ser adicionado como campo calculado no `ProjectResource`.
- **Filas**: notificações e operações pesadas podem ser movidas para jobs com Redis como driver de fila, já disponível na infra.
- **Testes de frontend**: cobertura de testes E2E com Playwright ou Cypress não foi incluída no escopo.
