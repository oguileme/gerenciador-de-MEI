const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

/**
 * Formata decimal exato sem converter o valor monetário para ponto flutuante.
 *
 * `BigInt` preserva a parte inteira, inclusive acima do limite seguro de `Number`; os
 * centavos recebidos da API são inseridos nas partes produzidas pelo `Intl`.
 */
export function formatMoney(value: string): string {
  const match = /^(-?)(\d+)\.(\d{2})$/.exec(value)
  if (!match) return '—'
  const [, sign, integer = '0', fraction = '00'] = match
  const parts = currency.formatToParts(BigInt(integer))
  const formatted = parts.map((part) => part.type === 'fraction' ? fraction : part.value).join('')
  return sign === '-' ? `-${formatted}` : formatted
}

/**
 * Formata a data fiscal pelas próprias partes para não interpretá-la como instante UTC.
 *
 * Criar `Date` diretamente poderia exibir o dia anterior em fusos negativos, como São Paulo.
 */
export function formatDate(value: string | null): string {
  if (!value) return '—'
  const [year, month, day] = value.split('-')
  return year && month && day ? `${day}/${month}/${year}` : '—'
}
