# Miniworld

Aplicação fullstack de gerenciamento de projetos e tarefas, desenvolvida como teste técnico.

---

## 📑 Índice

1. [Stack](#stack)
2. [Por que Sanctum?](#por-que-sanctum)
3. [Entidades e regras de negócio](#entidades-e-regras-de-negócio)
4. [Pré-requisitos](#pré-requisitos)
5. [Instalação](#instalação)
6. [Execução com imagem publicada](#execução-com-imagem-publicada)
7. [Execução local para desenvolvimento](#execução-local-para-desenvolvimento)
8. [Credenciais padrão](#credenciais-padrão)
9. [Primeiro acesso e ativação de usuário](#primeiro-acesso-e-ativação-de-usuário)
10. [Permissões](#permissões)
11. [Migrations e seeders](#migrations-e-seeders)
12. [Testes](#testes)
13. [Filas e Horizon](#filas-e-horizon)
14. [Convenção de branches](#convenção-de-branches)
15. [Convenção de commits](#convenção-de-commits)
16. [Padrões de código](#padrões-de-código)
17. [Build Docker](#build-docker)
18. [CI/CD](#cicd)
19. [Trade-offs](#trade-offs)
20. [Melhorias futuras](#melhorias-futuras)

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

- Nome obrigatório e único por usuário.
- Status: `active` | `inactive`.
- Orçamento (`budget`) opcional.
- Não pode ser excluído se possuir tarefas vinculadas.

### Tarefa

- Descrição obrigatória.
- `project_id` obrigatório (FK para projetos).
- `predecessor_task_id` opcional (auto-FK para tarefas).
- Status: `completed` | `not_completed`.
- `end_date` deve ser maior ou igual a `start_date`.
- Não pode ser excluída se for predecessora de outra tarefa.

---

## Pré-requisitos

- Docker >= 24.
- Docker Compose >= 2.20.
- Git.

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

---

## Execução com imagem publicada

A imagem publicada no Docker Hub representa o serviço `app` da aplicação. A validação funcional continua sendo feita com `docker compose`, mantendo Nginx, PostgreSQL, Redis e Mailpit como serviços auxiliares do ambiente.

A decisão foi manter o fluxo simples e funcional para avaliação técnica: o avaliador baixa a imagem publicada, sobe o stack com Compose e inicializa o frontend dentro do container da aplicação. Isso já é suficiente para validar a entrega, sem exigir um compose separado de release.

### 1. Baixe a imagem publicada

```bash
docker pull yanbpenalva/miniworld-app:latest
```

### 2. Ajuste o `docker-compose.yml`

No serviço `app`, use a imagem publicada no lugar do `build` local:

```yaml
services:
  app:
    image: yanbpenalva/miniworld-app:latest
    container_name: miniworld-app
    restart: always
    ports:
      - "3000:3000"
      - "5173:5173"
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
      CHOKIDAR_USEPOLLING: "true"
      PHP_ENABLE_XDEBUG: "1"
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
      mailpit:
        condition: service_started
    networks:
      - miniworld-app-network
```

Os demais serviços podem permanecer como estão no `docker-compose.yml`.

### 3. Suba o ambiente

```bash
docker compose down -v --remove-orphans
docker compose up -d
```

### 4. Inicialize a aplicação

Entre no container e prepare o ambiente:

```bash
docker exec -it miniworld-app sh
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

Se o script `dev` do projeto não estiver configurado para escutar fora do loopback, use:

```bash
npm run dev -- --host 0.0.0.0
```

### 5. Acesse os serviços

| Serviço            | URL                           |
| ------------------ | ----------------------------- |
| App (frontend/API) | http://localhost:8001         |
| Vite               | http://localhost:5173         |
| Mailpit (e-mail)   | http://localhost:8025         |
| Horizon (filas)    | http://localhost:8001/horizon |

### Observação sobre a estratégia

- A imagem publicada contém a aplicação.
- O Nginx permanece como camada HTTP do stack.
- O banco, Redis e Mailpit seguem como serviços auxiliares do ambiente.
- O frontend é iniciado manualmente apenas para validação local da interface em modo desenvolvimento.
- Não é necessário manter um `docker-compose.release.yml` para esta entrega; o `docker-compose.yml` atual, ajustado para usar a imagem publicada no serviço `app`, já atende ao objetivo da avaliação.

---

## Execução local para desenvolvimento

O fluxo abaixo é para trabalhar no código-fonte local. Ele mantém bind mount, build local e hot reload.

### 1. Suba os containers de desenvolvimento

```bash
docker compose up -d --build
```

### 2. Instale dependências e gere a key

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

### 3. Execute migrations e seeders

```bash
docker compose exec app php artisan migrate --seed
```

### 4. Corrija permissões (Linux/Mac)

```bash
chmod +x permissions.sh
./permissions.sh
```

### 5. Inicie o frontend

```bash
docker compose exec app npm run dev
```

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

| Parâmetro | Valor   |
| --------- | ------- |
| Host      | `redis` |
| Porta     | `6379`  |
| Senha     | nenhuma |

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

- Jobs são despachados via filas Redis com `queue:work`.
- **Supervisor** gerencia o ciclo de vida dos workers (restart, retry, timeout).
- **Horizon** provê dashboard de rastreamento, métricas e falhas.

### Exemplo de dispatch

```php
dispatch(new ProcessTaskJob())->onQueue('tasks');
dispatch(new SendNotificationJob())->onQueue('notifications');
```

### Acesso ao Horizon

```text
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

```bash
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

- `declare(strict_types=1)` obrigatório em todos os arquivos PHP.
- Nomes de métodos com no máximo 5 palavras, verbos no imperativo.
- Variáveis em `camelCase`.
- Rotas seguem `{resource}.{action}`.
- Versionamento de API: `/api/v1/...`.
- Cobertura mínima de testes: 80% de linhas.
- Controllers finos — lógica em Actions.

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
docker pull yanbpenalva/miniworld-app:latest
```

Repositório: [https://hub.docker.com/r/yanpenalva/miniworld-app](https://hub.docker.com/repository/docker/yanbpenalva/miniworld-app/tags)

---

## CI/CD

O pipeline é acionado ao criar uma tag no padrão `vX.Y.Z` na branch `main`.

```bash
git tag v1.0.0
git push origin v1.0.0
```

O GitHub Actions irá:

1. Fazer checkout do código.
2. Buildar a imagem usando `Dockerfile.prod`.
3. Publicar no Docker Hub com a tag `v1.0.0` e `latest`.

### Secrets necessários no GitHub

| Secret               | Valor                                                               |
| -------------------- | ------------------------------------------------------------------- |
| `DOCKERHUB_USERNAME` | `yanpenalva`                                                        |
| `DOCKERHUB_TOKEN`    | Access Token gerado em hub.docker.com → Account Settings → Security |

Para gerar o token: https://hub.docker.com/settings/security → **New Access Token**.

---

## Trade-offs

- A imagem publicada no Docker Hub representa apenas o serviço `app`.
- A validação completa continua dependendo do `docker compose` para subir Nginx, PostgreSQL, Redis e Mailpit.
- O frontend em modo desenvolvimento é iniciado manualmente dentro do container da aplicação.
- A solução prioriza simplicidade operacional da entrega em vez de empacotar todo o stack em uma estratégia de release isolada.

---

## Melhorias futuras

- Automatizar a inicialização do frontend para eliminar o passo manual de `npm run dev`.
- Publicar uma estratégia de release totalmente autossuficiente para o stack HTTP + aplicação.
- Adicionar smoke tests automáticos no pipeline antes do push para o Docker Hub.
- Separar ainda mais claramente os fluxos de desenvolvimento local e validação da entrega.
