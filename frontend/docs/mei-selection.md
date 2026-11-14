# Seleção de MEIs e integração

## Direção visual

A composição das referências de seleção de perfis orienta a tela; as imagens são
referências visuais, não instruções de produto. Preserva-se a identidade do Meu MEI.

Tokens: floresta `#254E3A`, sálvia `#EAF1E7`, papel `#FFFFFF`, texto `#253B30`,
cinza `#67716B`, borda `#DCE2DA`. Manrope local: título 48px/700, nomes 16px/700,
texto auxiliar 14px/400. Iniciais grandes identificam os negócios sem exigir avatares
que não existem no contrato. CNPJ distingue nomes iguais.

Composição escolhida: título e opções centralizados, marca no canto superior;
formulário alinhado à esquerda em diálogo. Em telas pequenas, duas colunas.

```text
marca
              Qual MEI você quer acessar?
               Escolha para abrir seu painel.

           [ AB ]     [ CD ]     [ + ]
            Nome       Nome     Adicionar MEI
            CNPJ       CNPJ
```

Alternativa considerada: lista vertical com linhas de dados. Foi descartada por
perder a escolha visual simples das referências. Na revisão do plano, removidos
neon, ondas decorativas, métricas e cartões externos: o destaque fica nas iniciais,
com a identidade verde existente. Sem editar ou excluir nesta tela.

## Integração implementada

- Login e cadastro levam a `/meis`; `/dashboard` também redireciona para a seleção.
- `GET /api/meis?page=N`: lista paginada (15 por página), somente da conta atual.
  Resposta Laravel: `data: Mei[]`, `meta.current_page`, `meta.last_page`, além de links.
  Navegação entre páginas não segue URLs absolutas devolvidas pela API.
- `POST /api/meis`: `mei_name`, `cnpj` (14 dígitos), `opening_date` (`YYYY-MM-DD`),
  `ibge_municipality_code` (7 dígitos). Não envia `user_id`.
  Sucesso: 201 e `{ data: Mei }`; abre o contexto do MEI criado.
- `GET /api/meis/{id}`: valida e carrega o contexto ao entrar em `/meis/:meiId/dashboard`,
  inclusive em acesso direto/recarregamento. O painel apresenta métricas, notas e configurações; veja `dashboard-design.md`.
- `Mei` espelha o Resource atual, incluindo datas e código IBGE. Sem dados de demonstração
  ou persistência de dados fiscais no navegador em produção.
- Axios compartilhado usa cookies Sanctum; renovação única de CSRF em 419 já existente.
  O fluxo MEI trata 401 com limpeza da sessão da UI e retorno ao login; 422 por campo;
  falhas de rede com nova tentativa; 403/404 no contexto com retorno à seleção.
- Estados de carregamento, lista vazia e erro têm mensagens e ações específicas.
  Cadastro bloqueia envios concorrentes. Diálogo nativo mantém foco e permite Escape
  fora do envio. Nenhuma ação de exclusão é exposta.

## Dashboard integrado

O dashboard está implementado em `src/features/dashboard/`. Os contratos, decisões
confirmadas, requisitos de operação e limites dos testes estão em `dashboard-design.md`.
O contexto permanece explícito na rota e nos serviços; a troca de MEI recria o painel
e descarta resultados pendentes da instância anterior.

## Critérios de aceitação

Seleção obrigatória mesmo com um único MEI; conta vazia permite cadastrar; mais de 15
MEIs são navegáveis; nome longo não corta o CNPJ; teclado acessa opções e diálogo;
422 mantém dados; falha não simula sucesso; ID sem vínculo nunca exibe dados do MEI;
nenhum total financeiro é calculado nem inventado pela interface.
