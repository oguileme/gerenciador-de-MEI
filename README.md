# Gerenciador de MEI

Sistema web para organizar dados de um ou mais MEIs, importar NFS-e pelo XML e
acompanhar o faturamento em relação ao limite anual configurado.

O projeto ajuda o empreendedor, familiar ou contador a centralizar as notas e enxergar
quanto já foi faturado, quanto ainda resta do limite e qual é a projeção até o fim do
ano. Ele não emite, cancela ou substitui documentos fiscais nos sistemas oficiais.

## O que o sistema faz

- cadastro e autenticação de usuários com sessão por cookie;
- gerenciamento de vários MEIs na mesma conta;
- importação de NFS-e no padrão nacional, versões 1.00 e 1.01;
- preservação do XML original em armazenamento privado;
- prevenção de importações duplicadas por chave de acesso e hash do arquivo;
- listagem e filtragem de notas por situação;
- alteração manual da situação para ativa, cancelada ou substituída;
- dashboard anual com faturamento total, distribuição mensal, média e projeção;
- configuração de um limite de faturamento diferente para cada MEI e ano;
- alertas visuais conforme o percentual utilizado do limite.

Somente notas ativas entram no faturamento. Os totais são calculados sob demanda a
partir das notas, sem gravar agregados no banco. Valores monetários usam precisão
decimal e o agrupamento atual considera a data de emissão.

> O limite e a projeção são ferramentas de acompanhamento. Eles não garantem o
> enquadramento fiscal do MEI. Regras como proporcionalidade no ano de abertura devem
> ser confirmadas com um profissional contábil.

## Fluxo de uso

1. O usuário cria uma conta ou entra no sistema.
2. Cadastra um MEI ou escolhe um dos MEIs vinculados à conta.
3. Importa os arquivos XML das NFS-e desse MEI.
4. Consulta o painel anual e a lista de notas.
5. Quando necessário, atualiza manualmente a situação de uma nota ou o limite do ano.

O CNPJ contido no XML precisa corresponder ao MEI selecionado. A aplicação rejeita
arquivos inválidos, acima do limite configurado ou já importados.

## Arquitetura

```text
Navegador
   │
   ▼
SPA Vue 3 ── /api e /sanctum ──► API Laravel
                                      ├── PostgreSQL
                                      └── XMLs em disco privado
```

Este é um monorepo com duas aplicações:

- [`frontend/`](frontend/README.md): SPA em Vue 3, TypeScript e Vite;
- [`backend/`](backend/README.md): API em Laravel, autenticação Sanctum e regras de domínio;
- [`compose.yaml`](compose.yaml): ambiente local com frontend, backend e PostgreSQL.

Frontend e API trabalham no mesmo domínio lógico. No desenvolvimento, o Vite encaminha
as rotas `/api` e `/sanctum` ao backend, mantendo a autenticação SPA por cookie e CSRF.
Toda consulta e alteração de dados fiscais é autorizada no backend para o MEI da conta
autenticada.

### Tecnologias principais

| Área | Tecnologias |
| --- | --- |
| Frontend | Vue 3, TypeScript, Vite, Vue Router, Axios |
| Backend | PHP, Laravel, Sanctum, BCMath |
| Banco de dados | PostgreSQL 17 |
| Testes | Pest, Vitest, Vue Test Utils e Playwright |
| Qualidade | Laravel Pint, ESLint, oxlint e vue-tsc |
| Ambiente local | Docker Compose |

## Como rodar com Docker

### Pré-requisitos

- Docker Engine com Docker Compose;
- portas `5173` e `8000` livres no host.

Na raiz do repositório, execute:

```bash
docker compose up --build
```

Na primeira inicialização, o ambiente:

- cria os contêineres `frontend`, `backend` e `db`;
- instala as dependências do Composer e do npm;
- cria `backend/.env` a partir de `.env.example`, se necessário;
- gera a chave da aplicação;
- aguarda o PostgreSQL e aplica as migrations.

Quando os healthchecks estiverem saudáveis, acesse:

- aplicação: <http://localhost:5173>;
- API: <http://localhost:8000>;
- healthcheck da API: <http://localhost:8000/up>.

O PostgreSQL fica disponível apenas na rede interna do Compose e não publica uma porta
no host.

### Encerrar o ambiente

Para parar e remover os contêineres sem apagar o banco:

```bash
docker compose down
```

O volume `postgres_data` preserva o banco entre inicializações. Os XMLs ficam em
`backend/storage/app/private` no próprio host e não são versionados pelo Git.

> `docker compose down -v` apaga o volume do PostgreSQL. Não use esse comando na rotina
> de desenvolvimento. O armazenamento local também não substitui uma política de
> backup e retenção dos documentos fiscais.

### Configuração opcional

As variáveis abaixo podem ser exportadas no shell ou definidas em um arquivo `.env` na
raiz do repositório:

| Variável | Padrão | Finalidade |
| --- | --- | --- |
| `FRONTEND_PORT` | `5173` | Porta da SPA no host |
| `BACKEND_PORT` | `8000` | Porta da API no host |
| `POSTGRES_DB` | `gerenciador_mei` | Banco de desenvolvimento |
| `POSTGRES_USER` | `gerenciador_mei` | Usuário local do PostgreSQL |
| `POSTGRES_PASSWORD` | `gerenciador_mei_local` | Senha exclusivamente local |
| `BACKEND_URL` | `http://backend:8000` | Destino interno do proxy do Vite |
| `HOST_UID` / `HOST_GID` | `1000` | Usuário e grupo da imagem do backend |

Ao alterar as portas, o Compose também ajusta as URLs da aplicação e os domínios
stateful do Sanctum.

## Comandos úteis

Todos os comandos abaixo são executados a partir da raiz do repositório.

### Ambiente Docker

```bash
# Ver o estado e os healthchecks dos serviços
docker compose ps

# Acompanhar os logs de todos os serviços
docker compose logs -f

# Acompanhar somente os logs do backend
docker compose logs -f backend

# Reiniciar um serviço
docker compose restart backend

# Recriar as imagens após mudar lockfiles, Dockerfiles ou extensões PHP
docker compose up --build

# Validar a configuração do Compose
docker compose config --quiet
```

### Backend

```bash
# Consultar e aplicar migrations
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan migrate

# Listar as rotas da API
docker compose exec backend php artisan route:list --path=api

# Rodar todos os testes ou somente um teste
docker compose exec backend composer test
docker compose exec backend php artisan test --filter=NomeDoTeste

# Verificar ou corrigir o estilo PHP
docker compose exec backend vendor/bin/pint --test
docker compose exec backend vendor/bin/pint --dirty

# Abrir um shell no contêiner
docker compose exec backend sh

# Processos opcionais para fila e agendador local
docker compose exec backend php artisan queue:work
docker compose exec backend php artisan schedule:work
```

Os comandos de fila e agendador permanecem em primeiro plano; use-os em terminais
separados e somente quando estiver trabalhando nessas funcionalidades.

### Frontend

```bash
# Lint, tipos e testes unitários
docker compose exec frontend npm run lint
docker compose exec frontend npm run type-check
docker compose exec frontend npm run test:unit -- --run

# Gerar o build de produção
docker compose exec frontend npm run build

# Abrir um shell no contêiner
docker compose exec frontend sh
```

Para os testes de navegador, instale os browsers do Playwright no ambiente em que eles
serão executados e rode `npm run test:e2e` dentro de `frontend/`.

## Verificação antes de concluir uma mudança

Backend:

```bash
docker compose exec backend vendor/bin/pint --dirty
docker compose exec backend composer test
```

Frontend:

```bash
docker compose exec frontend npm run lint
docker compose exec frontend npm run type-check
docker compose exec frontend npm run test:unit -- --run
docker compose exec frontend npm run build
```

O lint do frontend aplica correções automaticamente; revise o diff depois de executá-lo.

## Regras importantes do domínio

- o XML original é o documento fiscal e deve ser preservado;
- notas não são apagadas: sua situação é atualizada;
- somente notas ativas compõem o faturamento;
- valores financeiros nunca usam ponto flutuante;
- os totais do dashboard são calculados a partir das notas, não persistidos;
- uma conta pode gerenciar vários MEIs, mas todo acesso é autorizado por MEI;
- o sistema acompanha documentos fiscais, mas não os emite nem altera no emissor.

## Limites do ambiente atual

O Compose é destinado apenas ao desenvolvimento. Ele não configura HTTPS, implantação
de produção, backup, retenção definitiva dos XMLs ou monitoramento. O parser atual cobre
o padrão nacional de NFS-e; layouts municipais diferentes ainda exigem implementações
específicas.

Decisões detalhadas de interface e regras do painel estão em
[`frontend/docs/`](frontend/docs/). As convenções para desenvolvimento assistido estão
em [`AGENTS.md`](AGENTS.md).
