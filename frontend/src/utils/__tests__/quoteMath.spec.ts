import { describe, expect, it } from 'vitest'
import {
  calculateQuoteSubtotal,
  normalizeQuoteMoney,
  normalizeQuoteQuantity,
  sumQuoteSubtotals,
} from '../quoteMath'

describe('prévia decimal do orçamento', () => {
  it('normaliza vírgula sem converter dinheiro para Number', () => {
    expect(normalizeQuoteMoney('1200,5')).toBe('1200.50')
    expect(normalizeQuoteQuantity('1,25')).toBe('1.250')
    expect(normalizeQuoteMoney('0,00')).toBeNull()
  })

  it('multiplica e arredonda meio centavo para cima', () => {
    expect(calculateQuoteSubtotal('100,00', '1,5')).toBe('150.00')
    expect(calculateQuoteSubtotal('0,01', '0,5')).toBe('0.01')
    expect(sumQuoteSubtotals(['150.00', '0.01'])).toBe('150.01')
  })

  it('não oferece total enquanto houver linha incompleta', () => {
    expect(calculateQuoteSubtotal('', '1')).toBeNull()
    expect(sumQuoteSubtotals(['10.00', null])).toBeNull()
  })
})
