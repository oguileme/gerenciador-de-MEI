const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

/** Formata decimal exato sem converter o valor monetário para ponto flutuante. */
export function formatMoney(value: string): string {
  const match = /^(-?)(\d+)\.(\d{2})$/.exec(value)
  if (!match) return '—'
  const [, sign, integer = '0', fraction = '00'] = match
  const parts = currency.formatToParts(BigInt(integer))
  const formatted = parts.map((part) => part.type === 'fraction' ? fraction : part.value).join('')
  return sign === '-' ? `-${formatted}` : formatted
}

/** Datas fiscais são exibidas sem conversão de fuso horário. */
export function formatDate(value: string | null): string {
  if (!value) return '—'
  const [year, month, day] = value.split('-')
  return year && month && day ? `${day}/${month}/${year}` : '—'
}
