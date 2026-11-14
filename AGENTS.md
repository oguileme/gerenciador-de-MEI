# AGENTS.md — Gerenciador de MEI

Guia para agentes de IA e pessoas que trabalham neste repositório. Leia este arquivo inteiro antes de qualquer tarefa.

**Hierarquia:** regras específicas ficam em [`backend/AGENTS.md`](backend/AGENTS.md) e [`frontend/AGENTS.md`](frontend/AGENTS.md). Ao trabalhar numa pasta, leia também o arquivo dela. Em caso de conflito, o arquivo mais próximo do código vence.

## 1. Sobre o projeto

Sistema web para um MEI (Microempreendedor Individual) acompanhar o faturamento a partir de NFS-e (XML) e monitorar o **teto anual de faturamento**: valor e percentual utilizados, com alertas de proximidade.

- Cliente real; desenvolvedor solo; possível refatoração futura para SaaS.
- Uma conta pode gerenciar **vários MEIs** (o próprio empreendedor, familiares, contador).
- O sistema **lê e rastreia** dados fiscais. Ele **não emite** documentos fiscais (fora de escopo: traria responsabilidade legal e criptográfica).
- NFS-e é o tipo principal; NF-e é expansão futura (modele o tipo da nota de forma extensível).
## 2. Arquitetura

```text
Navegador → nginx (reverse proxy) → SPA Vue (arquivos estáticos)
                                  → API Laravel → PostgreSQL
                                                → disco local (XMLs das notas)
Agendador (cron + schedule:run) → serviço de e-mail (alertas do teto)
```

- Monorepo: `/frontend` (Vue 3 + TypeScript, SPA) e `/backend` (Laravel, **somente API**).
- Front e API no **mesmo domínio** (via nginx). Autenticação: **Sanctum no modo SPA** (cookie + CSRF).
- A API é um **monólito modular**, não microsserviços. Módulos: Autenticação, Gestão de MEIs, Orçamentos, Importação de NFS-e, Parser de NFS-e, Notas fiscais, Faturamento e teto, Dashboard.
- **Importação:** upload do XML → Importação → Parser (devolve os dados da nota) → grava o XML bruto em disco e a nota no banco.
- **Dashboard:** Dashboard → Faturamento e teto → lê notas **ativas** → calcula na hora.
- Banco: PostgreSQL. Hospedagem ainda não definida.
## 3. Princípios de domínio (não negociáveis)

1. **XML é o documento com validade legal.** Preserve-o sempre; nunca descarte depois de extrair os dados. PDF serve só para visualização. Extração por IA é apenas fallback quando não há XML.
2. **Dado bruto ≠ agregado.** As notas são a fonte da verdade e nunca são resumidas nem apagadas. Totais (mensal, anual, % do teto) são **calculados sob demanda**, nunca gravados como valor fixo.
3. **Status da nota desde o início:** `active`, `cancelled`, `substituted`. Só `active` entra no faturamento. Na v1, cancelamento/substituição é marcado manualmente.
4. **Dinheiro nunca é `float`.** Use decimal exato (detalhes em cada área).
5. **Requisito não confirmado fica parametrizado**, não fixo no código. Ex.: agrupamento mensal por data de emissão *ou* de competência; valor do teto por ano (inclui a regra proporcional do ano de abertura — confirmar com o contador).
6. **Ingestão é componente substituível** (hoje: upload de XML), isolada da lógica de cálculo.
7. **Proporcionalidade:** antes de aceitar escopo novo (SEFAZ, emissão, integrações), avalie se é proporcional ao problema atual.
## 4. Glossário (domínio → nome no código)

| Domínio | Código |
|---|---|
| MEI | `Mei` |
| Nota fiscal (NFS-e) | `Invoice` (tipo `nfse`) |
| Ativa / cancelada / substituída | `active` / `cancelled` / `substituted` |
| Teto de faturamento | `ceiling` |
| Data de emissão / de competência | `issue_date` / `competence_date` |
| Tomador do serviço | `customer` |
| Orçamento (proposta de serviço) | `Quote` |

## 5. Segurança e privacidade

- **Autorização por MEI:** todo dado pertence a um MEI; toda leitura/escrita confere se o usuário tem vínculo com ele. Nunca confie em IDs vindos do cliente sem checar (IDOR).
- XML recebido é **entrada não confiável** (XXE, tamanho, tipo de arquivo).
- Segredos só em `.env`. Nunca commitar `.env`, XMLs reais, dumps de banco ou credenciais. Produção: `APP_DEBUG=false`.
- Dados fiscais e pessoais (CNPJ/CPF, valores, conteúdo de XML) não vão para logs nem mensagens de erro. Fixtures de teste usam dados fictícios ou anonimizados.
- Dependências: adicione só com necessidade clara e avise. Mantenha `ignore-scripts=true` e auditoria do npm habilitados.
## 6. Qualidade de código (todo o repositório)

**Clean code**
- Nomes que revelam intenção; funções curtas com uma responsabilidade; sem duplicação; sem números/strings mágicos; retorno antecipado em vez de aninhamento profundo.
- Sem código morto ou comentado.
- Dependências explícitas (injeção) e baixo acoplamento; regra de negócio fora de controllers e componentes.
- Mudança mínima: resolva o que foi pedido, sem refatorações ou features extras (YAGNI). Achou algo fora do escopo? **Aponte**, não altere.
**Comentários** explicam o **porquê**, não o óbvio.
- **Mantenha o código documentado em toda criação ou alteração:** contratos públicos recebem
  PHPDoc/TSDoc curto; regras de domínio, segurança, decisões, algoritmos e efeitos colaterais
  não evidentes recebem comentários próximos ao código relevante.
- Siga Clean Code: nomes e funções devem explicar **o que** e **como**; comentários registram
  intenção, contexto e restrições que o código sozinho não comunica com clareza. Nunca use
  comentários para compensar nomes ruins, funções extensas ou responsabilidades misturadas.
- Ao alterar um comportamento, revise os comentários relacionados no mesmo diff. Atualize ou
  remova qualquer comentário impreciso, redundante ou desatualizado.
- Comente: regra fiscal/de negócio não óbvia (cite a fonte da regra quando houver), decisões e trade-offs, workarounds, armadilhas.
- Documente contratos públicos (interfaces, serviços de domínio, composables, API Resources) com PHPDoc/TSDoc curto.
- Comentário que só repete o código deve virar um nome melhor.
**Idioma:** identificadores em inglês (conforme o glossário); comentários, commits e textos de interface em pt-BR.

**Testes:** código novo vem com teste; bug corrigido ganha teste de regressão. Cálculo de faturamento/teto e parser de NFS-e exigem casos-limite: virada de mês/ano, nota cancelada ou substituída, XML inválido, XML duplicado, valores com centavos.

## 7. Fluxo de trabalho do agente

1. **Entenda antes de mudar:** leia os AGENTS.md relevantes e o código vizinho; siga os padrões existentes. Em dúvida de requisito (principalmente fiscal), pergunte; não presuma.
2. **Discorde com fundamento:** se o pedido conflitar com estes princípios ou houver risco fiscal, de segurança ou de dados, diga e proponha alternativa **antes** de implementar. Não valide uma ideia só para agradar.
3. **Passos pequenos:** uma mudança coesa por vez.
4. **Review a cada modificação (autorrevisão):** antes de dar a tarefa por concluída, releia o seu próprio diff com o checklist abaixo e rode os comandos de verificação da área alterada (veja o AGENTS.md da pasta).
5. **Relate com honestidade:** o que mudou e por quê, riscos, pendências e o que **não** foi executado. Nunca diga que testou sem ter rodado.
6. Não inicie processos longos (servidores de dev, watchers) sem necessidade.
**Checklist de review**

- [ ] Faz só o que foi pedido, sem efeitos colaterais?
- [ ] Nomes claros, funções curtas, sem duplicação nem código morto?
- [ ] Comentários explicam o porquê (nenhum óbvio ou desatualizado)?
- [ ] Dinheiro sem `float`; datas fiscais corretas; status da nota considerado?
- [ ] Autorização por MEI checada; entrada validada; nada sensível em log?
- [ ] Testes novos/atualizados e passando; lint, formatação e tipos limpos?
- [ ] Migrations não destroem dados existentes?
- [ ] Documentação (README, AGENTS.md, glossário) atualizada, se algo mudou?
> A autorrevisão do agente **não substitui** a revisão humana. O autor revisa o diff antes de qualquer merge; em código fiscal/financeiro isso é obrigatório.

## 8. Git

- Branches: `main` (estável), `dev` (integração) e uma branch por tarefa (`feat/…`, `fix/…`, `chore/…`, `docs/…`) criada a partir da `dev`. A tarefa é mesclada na `dev`; a `dev` vai para a `main` só quando estiver funcional.
- Commits pequenos e atômicos em [Conventional Commits](https://www.conventionalcommits.org/), com a área no escopo: `feat(backend): importa XML de NFS-e`.
- O agente **não** faz push, merge, rebase, force-push nem apaga branches sem pedido explícito.
## 9. Fora de escopo (v1)

Emissão de notas; integração com SEFAZ/portais emissores; cancelamento/substituição automático; NF-e; PDF de orçamento; refatoração para SaaS. O módulo de orçamentos (CRUD) **está** na v1.

## 10. Decisões em aberto (não decida sozinho — pergunte)

- Agrupamento mensal: data de emissão ou de competência.
- Layout dos XMLs do cliente (padrão nacional ou municipal).
- Hospedagem, backup e retenção dos XMLs.
- Limiares dos alertas de proximidade do teto.
