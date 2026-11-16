const MONEY_PATTERN = /^\d{1,8}(?:[.,]\d{1,2})?$/
const QUANTITY_PATTERN = /^\d{1,3}(?:[.,]\d{1,3})?$/
const CALCULATED_MONEY_PATTERN = /^\d{1,13}\.\d{2}$/

function parseScaled(value: string, scale: number, pattern: RegExp): bigint | null {
  const normalized = value.trim().replace(',', '.')
  if (!pattern.test(normalized)) return null
  const [integer = '0', fraction = ''] = normalized.split('.')
  const scaled = BigInt(integer) * 10n ** BigInt(scale)
    + BigInt(fraction.padEnd(scale, '0'))
  return scaled > 0n ? scaled : null
}

function formatScaled(value: bigint, scale: number): string {
  const divisor = 10n ** BigInt(scale)
  return `${value / divisor}.${(value % divisor).toString().padStart(scale, '0')}`
}

/** Normaliza dinheiro digitado em pt-BR para o contrato decimal da API. */
export function normalizeQuoteMoney(value: string): string | null {
  const scaled = parseScaled(value, 2, MONEY_PATTERN)
  return scaled === null ? null : formatScaled(scaled, 2)
}

/** Normaliza quantidade com até três casas para o contrato decimal da API. */
export function normalizeQuoteQuantity(value: string): string | null {
  const scaled = parseScaled(value, 3, QUANTITY_PATTERN)
  return scaled === null ? null : formatScaled(scaled, 3)
}

/** Prévia comercial exata; o backend ainda recalcula e persiste a fonte de verdade. */
export function calculateQuoteSubtotal(unitPrice: string, quantity: string): string | null {
  const cents = parseScaled(unitPrice, 2, MONEY_PATTERN)
  const thousandths = parseScaled(quantity, 3, QUANTITY_PATTERN)
  if (cents === null || thousandths === null) return null
  // O produto tem escala 5; somar metade de 10³ aplica HALF_UP ao retornar a centavos.
  return formatScaled((cents * thousandths + 500n) / 1000n, 2)
}

export function sumQuoteSubtotals(values: Array<string | null>): string | null {
  if (values.some((value) => value === null)) return null
  const total = values.reduce((sum, value) => {
    const parsed = value ? parseScaled(value, 2, CALCULATED_MONEY_PATTERN) : null
    return parsed === null ? sum : sum + parsed
  }, 0n)
  return formatScaled(total, 2)
}
