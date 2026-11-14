import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import MonthlyChart from '../components/MonthlyChart.vue'

const months = Array.from({ length: 12 }, (_, index) => ({
  month: index + 1, amount: '1000.00', height_percent: '100.00',
}))

describe('Linha da média mensal', () => {
  it('exibe média recebida da API e preserva a seleção de mês', async () => {
    const wrapper = mount(MonthlyChart, { props: {
      months, selectedMonth: 1, year: 2026,
      average: { amount: '750.25', height_percent: '75.03', month_count: 6 },
    } })
    expect(wrapper.get('.average-label').text()).toContain('R$\u00a0750,25')
    expect(wrapper.get('.average-period').text()).toBe('(6 meses)')
    expect(wrapper.get('.average-line').attributes('style')).toContain('75.03%')
    await wrapper.findAll('button')[1]!.trigger('click')
    expect(wrapper.emitted('select')).toEqual([[2]])
  })

  it('desenha média zero na base e omite a linha quando não há amostra', async () => {
    const wrapper = mount(MonthlyChart, { props: {
      months, selectedMonth: 1, year: 2026,
      average: { amount: '0.00', height_percent: '0.00', month_count: 1 },
    } })
    expect(wrapper.get('.average-label').text()).toContain('R$\u00a00,00')
    expect(wrapper.get('.average-period').text()).toBe('(1 mês)')
    expect(wrapper.find('.average-line').exists()).toBe(true)
    await wrapper.setProps({ average: { amount: null, height_percent: null, month_count: 0 } })
    expect(wrapper.find('.average-line').exists()).toBe(false)
    expect(wrapper.text()).toContain('Média mensal indisponível neste período.')
  })
})
