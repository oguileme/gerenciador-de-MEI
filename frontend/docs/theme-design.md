# Temas do Meu MEI

O tema claro preserva a identidade original de papel, sálvia e verde floresta. O tema
escuro segue a ideia de um caderno contábil botânico: superfícies verde-grafite e verde
claro reservado para marca, links, foco e ações.

Os componentes usam apenas os tokens semânticos de `src/assets/base.css`. Cores de
status mantêm texto e cor para não depender apenas da percepção cromática. Manrope
continua como fonte única e a logo muda pelo mesmo sistema de tokens, sem outra imagem.

Na primeira visita, o tema segue `prefers-color-scheme`. Uma escolha manual é salva em
`localStorage` na chave `meu-mei-theme` e passa a prevalecer. O seletor aparece nos
cabeçalhos públicos, na seleção de MEI e na barra lateral do dashboard.

Paleta clara principal: canvas `#F3F5F0`, papel `#FFFFFF`, superfície suave `#F7F9F6`,
floresta `#254E3A`, texto `#253B30` e texto secundário `#67716B`.

Paleta escura principal: canvas `#101713`, papel `#18211C`, superfície suave `#202B24`,
destaque `#A8CFAD`, texto `#EDF3EC` e texto secundário `#AAB7AD`.
