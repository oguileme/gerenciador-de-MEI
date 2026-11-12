# Esquemas da NFS-e nacional

Arquivos extraídos do pacote oficial de produção
`NFSe-ESQUEMAS_XSD-v1.01-20260209`, publicado no Portal da NFS-e:

https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/documentacao-atual

Foram mantidos somente os arquivos necessários para validar documentos `NFSe` nas
versões 1.00 e 1.01. A validação usa apenas estes arquivos locais e não acessa a
rede em tempo de execução.

No tipo `TSSerieDPS` da versão 1.01, os marcadores `^` e `$` foram removidos da
expressão regular. Expressões regulares de XML Schema não usam os marcadores de
PCRE; mantê-los faz o libxml exigir esses caracteres literalmente e rejeitar uma
série válida como `1`. O `maxLength` original continua limitando o campo.
