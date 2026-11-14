# Gerenciador de MEI

Aplicação para acompanhar o faturamento de MEIs a partir de NFS-e, composta por uma
SPA Vue, uma API Laravel e PostgreSQL.

## Ambiente Docker de desenvolvimento

### Pré-requisitos

- Docker Engine com Docker Compose.
- Portas `5173` e `8000` livres no host.

O ambiente contém exatamente três serviços: `frontend`, `backend` e `db`. Na primeira
inicialização, as dependências são sincronizadas, o `backend/.env` é criado quando
necessário, a chave da aplicação é gerada e as migrations são aplicadas automaticamente.

```bash
docker compose up --build
```

Depois que os healthchecks estiverem saudáveis:

- Frontend: <http://localhost:5173>
- Backend: <http://localhost:8000>
- Healthcheck da API: <http://localhost:8000/up>

O frontend encaminha `/api` e `/sanctum` para o backend dentro da rede Docker. O banco
não publica uma porta no host.

Para encerrar os contêineres sem apagar o banco:

```bash
docker compose down
```

> **Atenção:** `docker compose down -v` apaga o volume do PostgreSQL. Não use esse
> comando como parte da rotina de desenvolvimento.

Os XMLs importados ficam em `backend/storage/app/private`, no host, e não são incluídos
na imagem nem versionados pelo Git. Esse armazenamento local não substitui uma política
de backup e retenção dos documentos fiscais.

## Comandos usuais

```bash
# Artisan e Composer
docker compose exec backend php artisan migrate:status
docker compose exec backend composer test

# Frontend
docker compose exec frontend npm run type-check
docker compose exec frontend npm run test:unit -- --run

# Processos opcionais, executados no contêiner existente
docker compose exec backend php artisan queue:work
docker compose exec backend php artisan schedule:work
```

Após alterar `composer.lock`, `package-lock.json`, um Dockerfile ou as extensões PHP,
reconstrua as imagens:

```bash
docker compose up --build
```

Os entrypoints sincronizam as dependências com os lockfiles durante a inicialização.

## Configuração local

As variáveis abaixo podem ser definidas no shell ou em um arquivo `.env` na raiz:

| Variável | Padrão | Finalidade |
|---|---|---|
| `FRONTEND_PORT` | `5173` | Porta da SPA no host |
| `BACKEND_PORT` | `8000` | Porta da API no host |
| `POSTGRES_DB` | `gerenciador_mei` | Banco de desenvolvimento |
| `POSTGRES_USER` | `gerenciador_mei` | Usuário local do PostgreSQL |
| `POSTGRES_PASSWORD` | `gerenciador_mei_local` | Senha exclusivamente local |
| `BACKEND_URL` | `http://backend:8000` | Destino interno do proxy do Vite |
| `HOST_UID` / `HOST_GID` | `1000` | Identidade usada pela imagem do backend |

Se as portas forem alteradas, o Compose também ajusta `APP_URL`, `FRONTEND_URL` e os
domínios stateful do Sanctum.

## Verificações

```bash
docker compose config --quiet

docker compose exec backend vendor/bin/pint --test
docker compose exec backend composer test

docker compose exec frontend npm run lint
docker compose exec frontend npm run type-check
docker compose exec frontend npm run test:unit -- --run
docker compose exec frontend npm run build
```

Este setup é destinado somente ao desenvolvimento. Ele não configura HTTPS, backup,
retenção definitiva dos XMLs ou implantação de produção.

Detalhes de cada aplicação estão em [backend/README.md](backend/README.md) e
[frontend/README.md](frontend/README.md).
