# Telas estáticas de autenticação

As rotas `/login` e `/register` usam os arquivos existentes em `src/features/auth/`.
A rota raiz apresenta a landing page. Os componentes ficam em `components/`, os
campos em `types/auth.ts` e o estado local em `composables/useAuthForm.ts`.
As pastas de serviços permanecem reservadas para a futura integração.
A marca compartilhada em `src/components/AppBrand.vue` leva à página inicial.

## Comportamento

- Nenhuma chamada à API, criação de conta, sessão ou persistência de credenciais.
- O envio válido informa que a tela é uma demonstração.
- Cadastro: nome, e-mail, senha e confirmação de senha.
- Login: e-mail e senha.
- Validação local de campos obrigatórios, formato do e-mail e confirmação de senha.
- Os atributos `name`, `email` e `password` vêm de `backend/app/Models/User.php` e da migration de usuários.
- A confirmação é um campo de interface. Não existe FormRequest de cadastro no backend;
  nenhuma política de complexidade ou tamanho mínimo foi presumida.
- Os limites de 255 caracteres para nome e e-mail refletem as colunas string da migration.

## Direção visual

Referências fornecidas: composição bipartida e formas suaves das telas Sprout, Tuga,
Ninth e Clean Login. A ilustração de uma empreendedora organizando notas aproxima a
composição do domínio do MEI. Campos e textos alinhados à esquerda; ilustração como
único destaque visual. No celular, o formulário tem prioridade e a ilustração é ocultada.

Paleta: floresta #254E3A, sálvia #EAF1E7, folha #AFC69A, branco #FFFFFF,
cinza #67716B e ocre #D6B475 na ilustração.
As variantes clara e escura seguem os tokens de [`theme-design.md`](theme-design.md).
Tipografia: Manrope variável, servida localmente, com licença em
`src/assets/fonts/OFL.txt`. Fonte: https://github.com/google/fonts/tree/main/ofl/manrope.

## Ilustração

Gerada pela ferramenta integrada `image_gen`, sem CLI ou API externa no aplicativo.
Arquivo final: `src/assets/auth/entrepreneur-cutout.png` (PNG com transparência).

Prompt final utilizado:

```text
Use case: illustration-story
Asset type: editorial illustration for the left panel of a Brazilian small-business manager login page.
Primary request: a quiet, charming illustration of a woman small-business owner sitting at her desk organizing her business, with an open laptop, a small stack of invoices, a notebook and one small potted plant.
Style/medium: contemporary hand-drawn editorial illustration, confident fine dark forest-green outlines, flat matte color fills with very subtle paper grain, expressive but restrained shapes, sophisticated friendly proportions, no 3D.
Composition/framing: landscape 4:3, complete subject and desk visible, woman in center with laptop on left and plant on right; generous empty margin around composition, background a perfectly flat very pale sage #EAF1E7. Desktop is a simple pale wood slab, not a room. A single organic deeper sage shape behind the woman, no extra decorative objects. It should feel composed and peaceful, not corporate clipart.
Color palette: pale sage #EAF1E7 background; muted leaf green #AFC69A, deep forest #254E3A, warm off-white #FAFBF7, a restrained golden ochre accent #D6B475. Woman has warm brown skin and dark curly hair, a forest green top.
Lighting/mood: calm, grounded, optimistic, a comfortable moment of getting organized.
Text: none.
Constraints: no letters, numbers, logos, watermark, interface, graphs, coins, money symbols, floating UI elements, sparkle icons or gradients. Crisp illustration suitable at 500 pixels wide.
```

Refinamento final pela mesma ferramenta: remoção apenas do fundo externo,
preservando a composição e a forma verde atrás da personagem.

```text
Use case: background-extraction. Edit this illustration for a Vue login page. Remove ONLY the pale, nearly flat sage outer background and make those outer pixels genuinely transparent. Preserve the entire woman, laptop, notebook, stack of papers, plant, table and the darker organic green shape directly behind the woman. Preserve their exact shapes, proportions, colors, fine linework, paper texture and composition. Do not change, add or redraw anything else. Output PNG with real alpha transparency, no checkerboard pattern, no text or watermark. Retain all desk edges and visible legs.
```
