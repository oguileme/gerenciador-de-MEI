# Dashboard do MEI

## Direção visual

As cinco referências fornecidas orientam composição e hierarquia, não instruções
nem regras de negócio. A tela mantém a identidade do Meu MEI e a fonte Manrope local.

Paleta: floresta #254E3A, sálvia #EAF1E7, fundo #F3F5F0, branco #FFFFFF,
amarelo #93610C e vermelho #A23832. Alertas têm texto e cor, nunca apenas cor.
As variantes clara e escura seguem os tokens de [`theme-design.md`](theme-design.md).
Tipografia: título 30px/700, valor anual 40px/700, títulos de seção 18px/700,
corpo 14px e dados auxiliares 12px. Valores com algarismos tabulares.

Layout: menu lateral compacto; conteúdo alinhado à esquerda; gráfico de 12 meses
como maior área central; lista de notas com leitura horizontal e filtros discretos.
No celular, navegação compacta e seções empilhadas.

```text
marca       MEI selecionado                        ano | Importar XML
Visão geral Faturamento anual + limite   Mês selecionado   Projeção
Notas       Gráfico mensal (12 barras)              Situação do limite
Dados       Notas fiscais: número / tomador / data / valor / status
Trocar MEI
```

Revisão do plano: retirados cartões bancários, fotos de perfil, decorações e métricas
sem relação com o MEI. O destaque verde pertence ao faturamento anual; o restante
serve à comparação mensal e à identificação das notas. Barras não implicam limite
mensal legal. A projeção será apresentada como estimativa, com método e período.

## Regras confirmadas nesta tarefa

- Agrupamento por data de emissão; soma do valor bruto de serviços das notas ativas.
- Limite integral informado pelo usuário, sem proporcionalidade no ano de abertura.
- Faixas de acompanhamento: verde abaixo de 80%, amarelo de 80% até abaixo de 95%,
  vermelho a partir de 95%. Parametrizadas em `backend/config/dashboard.php`.
- Sem aviso especial ao chegar a 100%, sem notificação e sem envio de e-mail nesta etapa.
- A referência inicial de acompanhamento é R$ 81.000,00, persistida em
  `Mei::annual_ceiling`; o valor salvo por MEI/ano tem prioridade. Alterar esses valores
  não muda o enquadramento legal. Não se presume que esse padrão será válido
  legalmente para todos os anos ou modalidades de MEI.
- Projeção: média dos meses de atividade completos no ano, incluindo meses sem receita.
  O mês corrente não entra na amostra. Se a abertura ocorrer depois do dia 1, a média
  começa no mês seguinte e a receita do mês parcial é adicionada separadamente.
  A média é estendida aos meses de atividade até dezembro; a estimativa nunca fica
  abaixo do faturamento já registrado. Sem mês completo ou sem data de abertura,
  exibe dados insuficientes. Para ano encerrado, exibe o realizado.
- O gráfico mostra uma linha horizontal tracejada da média mensal, com valor e número
  de meses. Usa a mesma amostra de meses completos da projeção, incluindo meses zerados.
  Em anos encerrados, considera todos os meses completos de atividade daquele ano.
  Sem amostra, não desenha linha; com amostra sem receita, desenha média zero na base.
  O contrato `monthly_average` contém `amount`, `height_percent` e `month_count`;
  o frontend apenas posiciona a linha na mesma escala das barras.
- Valores arredondados apenas na apresentação do resultado da divisão, em centavos.
  A comparação das faixas usa valores exatos antes de arredondar o percentual.
- Agregados e projeções são calculados sob demanda no backend e nunca persistidos.
  A listagem de notas abrange todos os períodos, com filtro por status e paginação.
- Apenas nome, data de abertura e município são editáveis no cadastro. O CNPJ permanece
  imutável conforme o contrato preexistente, para preservar o vínculo com os XMLs.
- Alterar o status acompanha uma situação registrada; não cancela a nota no emissor.

## API e operação

| Operação | Endpoint |
| --- | --- |
| Métricas anuais e 12 meses | `GET /api/meis/{mei}/dashboard?year=2026` |
| Configurar limite anual | `PUT /api/meis/{mei}/annual-limit` com `year` e `amount` decimal string |
| Notas (10 por página) | `GET /api/meis/{mei}/invoices?page=1&status=active` |
| Alterar apenas status | `PATCH /api/meis/{mei}/invoices/{invoice}/status` |
| Importar XML | `POST /api/meis/{mei}/invoices/import`, multipart no campo `xml` |
| Dados cadastrais | `PATCH /api/meis/{mei}` |

Os tipos do frontend espelham os Resources. Cookies Sanctum e CSRF usam a instância
Axios existente. As rotas passam pela Policy do MEI; a alteração de nota resolve o ID
pela relação do MEI autorizado. Não existe endpoint novo para editar/excluir notas.
Depois de importar ou alterar status, a interface consulta novamente notas e métricas.
Falha na atualização remove totais anteriores para não mostrar valores desatualizados.
Os XMLs originais continuam no disco privado; o dashboard não os reescreve.

A migration nova cria apenas `mei_annual_limits`. Aplicar com `php artisan migrate`
nos demais ambientes antes de usar o dashboard. A nova migration foi aplicada
no banco local existente após verificar que era a única pendente. O backend requer `ext-bcmath`, declarada no
Composer, para cálculo decimal exato. Não há nova dependência npm.

## Verificação

Testes de domínio cobrem centavos, limites de faixa, meses vazios, virada de mês/ano,
status, ano de abertura e projeção. Testes HTTP cobrem autorização, filtro/paginação,
restrições de edição e configurações anuais. A suíte foi executada também em um
PostgreSQL temporário separado do banco da aplicação.

Vitest cobre o comportamento dos componentes e serviços simulados. Playwright usa
respostas HTTP simuladas para revisar o dashboard em desktop/celular e o fluxo de
importação, status, limite e cadastro; isso não equivale a um teste no navegador
conectado ao backend real. Os dados das capturas são fictícios.
