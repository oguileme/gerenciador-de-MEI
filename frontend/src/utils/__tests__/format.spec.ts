import { describe, expect, it } from 'vitest'
import { formatDate, formatMoney } from '../format'

describe('Formatação de dados fiscais', () => {
  it('preserva centavos e valores grandes sem ponto flutuante', () => {
    expect(formatMoney('1234.56')).toBe('R$\u00a01.234,56')
    expect(formatMoney('9007199254740993.01')).toBe('R$\u00a09.007.199.254.740.993,01')
    expect(formatMoney('-0.01')).toBe('-R$\u00a00,01')
    expect(formatMoney('0.00')).toBe('R$\u00a00,00')
  })
  it('não desloca datas na virada de mês e ano', () => {
    expect(formatDate('2026-01-01')).toBe('01/01/2026')
    expect(formatDate('2026-02-01')).toBe('01/02/2026')
    expect(formatDate(null)).toBe('—')
  })
})
