# Landing page do Meu MEI

A página inicial (`/`) usa `src/features/landingPage/index.vue` e seus componentes.
A marca é compartilhada com a autenticação em `src/components/AppBrand.vue`.
Todos os convites de cadastro levam a `/register`; os links de entrada levam a
`/login`. A marca permite voltar para `/` a partir das telas de autenticação.
A navegação entre rotas volta ao topo, preservando a posição ao usar voltar/avançar.

## Direção visual

Continuidade das telas aprovadas: Manrope local, floresta #254E3A, sálvia #EAF1E7,
folha #AFC69A, branco #FFFFFF, cinza #67716B e ocre #D6B475 na ilustração.
As variantes clara e escura seguem os tokens de [`theme-design.md`](theme-design.md).
Abertura em duas colunas, títulos alinhados à esquerda, ilustração como destaque,
recursos sem cartões flutuantes e sem gráficos decorativos. A numeração aparece
somente na sequência de uso. No celular, navegação explícita e composição empilhada.

As novas referências orientam a hierarquia e o espaçamento, preservando a identidade
já aprovada. Não há métricas fictícias, avaliações, depoimentos, preços, garantias
ou promessas de resultados. As funcionalidades são apresentadas como a proposta
em desenvolvimento, coerente com o escopo v1 do AGENTS.md. As dúvidas esclarecem
que a emissão de notas está fora do escopo e que a prévia ainda não cria contas.
Não há chamadas à API, formulários novos, rastreamento ou dependências adicionais.

## Imagem

Arquivo final: `src/assets/landing/mei-workspace.png`.
Gerado com a ferramenta integrada `image_gen`, com transparência real e mantendo
a referência de estilo `src/assets/auth/entrepreneur-cutout.png`.
Nenhuma CLI ou API de geração faz parte do aplicativo.

Prompt utilizado:

```text
Use case: illustration-story
Asset type: transparent hero illustration for the landing page of Meu MEI, a Brazilian small-business organizer.
Primary request: create a NEW minimal editorial composition that matches the attached reference illustration's style, linework and palette. The reference is style guidance only, not an edit target.
Subject: a warm brown-skinned woman entrepreneur with dark curly hair, wearing a forest green top, sitting comfortably at a simple light wood desk, reviewing one sheet of paper in her hand beside an open laptop. One neat folder of invoices and a small leafy plant in an ochre pot on the desk. A single large pale leaf-green organic oval behind the woman unifies the composition.
Style/medium: sophisticated hand-drawn editorial illustration with thin forest-green outlines, flat matte fills and very subtle paper texture, friendly natural proportions. Match the existing reference, not 3D or photorealistic.
Composition: landscape 4:3, complete character and desktop, simplified desk legs, centered standalone vignette with generous transparent margins. Few objects, calm posture, no room or background scenery.
Palette: forest #254E3A, sage #EAF1E7, muted leaf #AFC69A, warm white #FAFBF7, small ochre accents #D6B475.
Constraints: genuinely transparent outer background with alpha. Keep the organic green shape opaque. No lettering, numbers, charts, graphs, money symbols, logos, watermark, UI cards, decorative sparkle, floating objects or gradients. Laptop screen is blank. This is an illustration of organizing a business, not a screenshot of an app.
```
