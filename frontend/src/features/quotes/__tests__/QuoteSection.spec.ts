import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import { quoteService } from '@/services/quotes'
import type { Quote } from '@/types/quote'
import QuoteSection from '../components/QuoteSection.vue'

vi.mock('@/services/quotes', () => ({ quoteService: {
  list: vi.fn<typeof quoteService.list>(), get: vi.fn<typeof quoteService.get>(),
  create: vi.fn<typeof quoteService.create>(), update: vi.fn<typeof quoteService.update>(),
  updateStatus: vi.fn<typeof quoteService.updateStatus>(), remove: vi.fn<typeof quoteService.remove>(),
  downloadPdf: vi.fn<typeof quoteService.downloadPdf>(),
} }))

enableAutoUnmount(afterEach)

const quote: Quote = {
  id: 7,
  mei_id: 1,
  issuer_name: 'Ateliê Exemplo',
  recipient_name: 'Cliente Exemplo',
  issue_date: '2026-11-14',
  status: 'pending',
  total_amount: '150.01',
  created_at: '2026-11-14T12:00:00Z',
  updated_at: '2026-11-14T12:00:00Z',
}
const detailed: Quote = {
  ...quote,
  items: [
    { id: 1, description: 'Consultoria', unit_price: '100.00', quantity: '1.500', subtotal: '150.00' },
    { id: 2, description: 'Ajuste', unit_price: '0.01', quantity: '0.500', subtotal: '0.01' },
  ],
}

async function render() {
  const router = createRouter({ history: createMemoryHistory(), routes: [
    { path: '/', component: { template: '<p>Início</p>' } },
    { path: '/login', name: 'login', component: { template: '<p>Login</p>' } },
  ] })
  await router.push('/')
  const wrapper = mount(QuoteSection, {
    props: { meiId: '1', meiName: 'Ateliê Exemplo' },
    global: { plugins: [router] },
    attachTo: document.body,
  })
  await flushPromises()
  return wrapper
}

beforeEach(() => {
  vi.mocked(quoteService.list).mockResolvedValue({
    data: [], meta: { current_page: 1, last_page: 1, total: 0 },
  })
  vi.mocked(quoteService.get).mockResolvedValue(detailed)
  vi.mocked(quoteService.create).mockResolvedValue(detailed)
  vi.mocked(quoteService.updateStatus).mockResolvedValue({ ...detailed, status: 'accepted' })
  vi.mocked(quoteService.remove).mockResolvedValue()
  vi.mocked(quoteService.downloadPdf).mockResolvedValue(new Blob(['pdf']))
  HTMLDialogElement.prototype.showModal = function () { this.setAttribute('open', '') }
})

afterEach(() => {
  vi.resetAllMocks()
  document.body.innerHTML = ''
})

describe('Orçamentos do MEI', () => {
  it('orienta o primeiro registro e cria serviços com prévia exata', async () => {
    const wrapper = await render()
    expect(wrapper.text()).toContain('Sua próxima proposta começa aqui.')
    await wrapper.get('.quote-heading .button-primary').trigger('click')
    await wrapper.get('#quote-recipient').setValue('Nova Cliente')
    await wrapper.get('#quote-description-1').setValue('Identidade visual')
    await wrapper.get('#quote-unit-1').setValue('12,50')
    await wrapper.get('#quote-quantity-1').setValue('2')
    expect(wrapper.get('.sheet-total').text()).toContain('R$\u00a025,00')

    await wrapper.get('.add-service').trigger('click')
    expect(wrapper.findAll('.service-line')).toHaveLength(2)
    const removeButtons = wrapper.findAll('.remove-service')
    await removeButtons[removeButtons.length - 1]?.trigger('click')
    expect(wrapper.findAll('.service-line')).toHaveLength(1)

    await wrapper.get('.quote-sheet').trigger('submit')
    await flushPromises()
    expect(quoteService.create).toHaveBeenCalledWith('1', {
      recipient_name: 'Nova Cliente',
      items: [{ description: 'Identidade visual', unit_price: '12.50', quantity: '2.000' }],
    })
    expect(wrapper.find('dialog').exists()).toBe(false)
    expect(wrapper.text()).toContain('Orçamento registrado.')
  })

  it('abre a proposta, altera status e baixa o PDF', async () => {
    vi.mocked(quoteService.list).mockResolvedValueOnce({
      data: [quote], meta: { current_page: 1, last_page: 1, total: 1 },
    })
    const createObjectUrl = vi.spyOn(URL, 'createObjectURL').mockReturnValue('blob:quote')
    const revokeObjectUrl = vi.spyOn(URL, 'revokeObjectURL').mockImplementation(() => {})
    const click = vi.spyOn(HTMLAnchorElement.prototype, 'click').mockImplementation(() => {})
    const wrapper = await render()

    await wrapper.get('.open-detail').trigger('click')
    await flushPromises()
    expect(quoteService.get).toHaveBeenCalledWith('1', 7)
    expect(wrapper.text()).toContain('Consultoria')
    expect(wrapper.text()).toContain('R$\u00a0150,01')

    await wrapper.get('.quote-status').setValue('accepted')
    await flushPromises()
    expect(quoteService.updateStatus).toHaveBeenCalledWith('1', 7, 'accepted')

    await wrapper.get('.detail-actions .button-secondary').trigger('click')
    await flushPromises()
    expect(quoteService.downloadPdf).toHaveBeenCalledWith('1', 7)
    expect(createObjectUrl).toHaveBeenCalled()
    expect(click).toHaveBeenCalled()
    expect(revokeObjectUrl).toHaveBeenCalledWith('blob:quote')
  })

  it('mantém o formulário aberto e indica campos incompletos', async () => {
    const wrapper = await render()
    await wrapper.get('.quote-heading .button-primary').trigger('click')
    await wrapper.get('.quote-sheet').trigger('submit')
    await flushPromises()
    expect(quoteService.create).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Informe quem receberá o orçamento.')
    expect(wrapper.text()).toContain('Descreva o serviço.')
    expect(wrapper.find('dialog').exists()).toBe(true)
  })
})
