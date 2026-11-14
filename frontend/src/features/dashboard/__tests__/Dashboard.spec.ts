import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import { dashboardService } from '@/services/dashboard'
import { invoiceService } from '@/services/invoices'
import { meiService } from '@/services/meis'
import { ApiError } from '@/config/api'
import type { DashboardMetrics } from '@/types/dashboard'
import type { Invoice } from '@/types/invoice'
import type { Mei } from '@/types/mei'
import DashboardShell from '../components/DashboardShell.vue'

vi.mock('@/services/dashboard', () => ({ dashboardService: {
  get: vi.fn<typeof dashboardService.get>(), updateLimit: vi.fn<typeof dashboardService.updateLimit>(),
} }))
vi.mock('@/services/invoices', () => ({ invoiceService: {
  list: vi.fn<typeof invoiceService.list>(), updateStatus: vi.fn<typeof invoiceService.updateStatus>(),
  importXml: vi.fn<typeof invoiceService.importXml>(),
} }))
vi.mock('@/services/meis', () => ({ meiService: { update: vi.fn<typeof meiService.update>() } }))
enableAutoUnmount(afterEach)
const mei: Mei = { id: 1, mei_name: 'Ateliê Exemplo', cnpj: '00000000000100',
  opening_date: '2024-01-01', ibge_municipality_code: '0000000', annual_ceiling: '81000.00',
  created_at: null, updated_at: null }
const invoice: Invoice = { id: 3, mei_id: 1, type: 'nfse', status: 'active', access_key: '000000',
  invoice_number: '42', customer_name: 'Cliente Exemplo', service_amount: '1200.50', net_amount: '1200.50',
  issue_date: '2026-01-01', competence_date: '2025-12-01', description: 'Serviço de teste',
  created_at: null, updated_at: null }
const metrics: DashboardMetrics = {
  year: 2026, as_of: '2026-07-12', date_basis: 'issue_date', annual_revenue: '1200.50', active_invoice_count: 1,
  months: Array.from({ length: 12 }, (_, i) => ({ month: i + 1, amount: i === 0 ? '1200.50' : '0.00', height_percent: i === 0 ? '100.00' : '0.00' })),
  monthly_average: { amount: '200.08', height_percent: '16.67', month_count: 6 },
  ceiling: { amount: '81000.00', used_percent: '1.48', status: 'safe', remaining_amount: '79799.50', warning_percent: 80, danger_percent: 95 },
  projection: { amount: '2401.00', status: 'within_limit', completed_months: 6 },
}
async function render() {
  const router = createRouter({ history: createMemoryHistory(), routes: [
    { path: '/', component: { template: '<p>Início</p>' } },
    { path: '/meis', name: 'mei-selection', component: { template: '<p>MEIs</p>' } },
    { path: '/login', name: 'login', component: { template: '<p>Login</p>' } },
  ] })
  await router.push('/')
  const wrapper = mount(DashboardShell, { props: { mei }, global: { plugins: [router] }, attachTo: document.body })
  await flushPromises()
  return wrapper
}
beforeEach(() => {
  vi.mocked(dashboardService.get).mockResolvedValue(metrics)
  vi.mocked(invoiceService.list).mockResolvedValue({ data: [invoice], meta: { current_page: 1, last_page: 1, total: 1 } })
  HTMLDialogElement.prototype.showModal = function () { this.setAttribute('open', '') }
})
afterEach(() => { vi.resetAllMocks(); document.body.innerHTML = '' })

describe('Dashboard do MEI', () => {
  it('exibe os totais da API, 12 meses e permite consultar um mês', async () => {
    const wrapper = await render()
    expect(wrapper.get('#annual-heading').text()).toBe('Faturamento bruto anual')
    expect(wrapper.text()).toContain('R$\u00a01.200,50')
    expect(wrapper.findAll('.month')).toHaveLength(12)
    expect(wrapper.get('#monthly-heading').text()).toBe('Julho de 2026')
    await wrapper.get('.month').trigger('click')
    expect(wrapper.get('#monthly-heading').text()).toBe('Janeiro de 2026')
    expect(wrapper.text()).toContain('R$\u00a02.401,00')
    expect(wrapper.text()).not.toMatch(/em construção|excluir nota|editar nota/i)
  })

  it('troca o ano e remove métricas anteriores quando a consulta falha', async () => {
    const wrapper = await render()
    vi.mocked(dashboardService.get).mockRejectedValueOnce(new ApiError(0, null))
    await wrapper.get('.year-filter select').setValue(2025)
    await flushPromises()
    expect(dashboardService.get).toHaveBeenLastCalledWith('1', 2025)
    expect(wrapper.get('[role="alert"]').text()).toContain('Não foi possível atualizar')
    expect(wrapper.find('#annual-heading').exists()).toBe(false)
  })

  it('salva limite decimal sem Number e mantém os campos após erro', async () => {
    const wrapper = await render()
    await wrapper.get('.ceiling-panel button').trigger('click')
    await wrapper.get('#settings-amount').setValue('90000,01')
    vi.mocked(dashboardService.updateLimit).mockRejectedValueOnce(new ApiError(422, { errors: { amount: ['Valor inválido.'] } }))
    await wrapper.get('dialog form').trigger('submit')
    await flushPromises()
    expect(wrapper.text()).toContain('Valor inválido.')
    expect((wrapper.get('#settings-amount').element as HTMLInputElement).value).toBe('90000,01')
    vi.mocked(dashboardService.updateLimit).mockResolvedValueOnce(metrics)
    await wrapper.get('dialog form').trigger('submit')
    await flushPromises()
    expect(dashboardService.updateLimit).toHaveBeenLastCalledWith('1', 2026, '90000.01')
    expect(wrapper.find('dialog').exists()).toBe(false)
  })

  it('salva dados cadastrais e preserva CNPJ', async () => {
    const wrapper = await render()
    await wrapper.get('nav button').trigger('click')
    await wrapper.get('#settings-mei_name').setValue('Novo nome')
    vi.mocked(meiService.update).mockResolvedValueOnce({ ...mei, mei_name: 'Novo nome' })
    await wrapper.get('dialog form').trigger('submit')
    await flushPromises()
    expect(meiService.update).toHaveBeenCalledWith('1', { mei_name: 'Novo nome', opening_date: '2024-01-01', ibge_municipality_code: '0000000' })
    expect(wrapper.emitted('updated')?.[0]?.[0]).toMatchObject({ mei_name: 'Novo nome' })
  })

  it('altera apenas o status e renova métricas e listagem', async () => {
    const wrapper = await render()
    vi.mocked(invoiceService.updateStatus).mockResolvedValueOnce({ ...invoice, status: 'cancelled' })
    await wrapper.get('.status-select').setValue('cancelled')
    await flushPromises()
    expect(invoiceService.updateStatus).toHaveBeenCalledWith('1', 3, 'cancelled')
    expect(dashboardService.get).toHaveBeenCalledTimes(2)
    expect(invoiceService.list).toHaveBeenCalledTimes(2)
  })

  it('informa resultados por XML e renova faturamento depois de importar', async () => {
    const wrapper = await render()
    vi.mocked(invoiceService.importXml).mockResolvedValueOnce(invoice)
      .mockRejectedValueOnce(new ApiError(409, { message: 'Esta nota já foi importada.' }))
    const input = wrapper.get('input[type="file"]')
    Object.defineProperty(input.element, 'files', { value: [new File(['<xml/>'], 'nova.xml'), new File(['<xml/>'], 'duplicada.xml')] })
    await input.trigger('change')
    await flushPromises()
    expect(wrapper.text()).toContain('nova.xml: Importada')
    expect(wrapper.text()).toContain('duplicada.xml: Esta nota já foi importada.')
    expect(dashboardService.get).toHaveBeenCalledTimes(2)
  })

  it('abre o seletor por clique e teclado', async () => {
    const wrapper = await render()
    const input = wrapper.get('input[type="file"]')
    const click = vi.spyOn(input.element as HTMLInputElement, 'click').mockImplementation(() => {})
    const dropzone = wrapper.get('.upload-dropzone')
    await dropzone.trigger('click')
    await dropzone.trigger('keydown', { key: 'Enter' })
    await dropzone.trigger('keydown', { key: ' ' })
    expect(click).toHaveBeenCalledTimes(3)
  })

  it('destaca a zona durante o arraste sem piscar em elementos internos', async () => {
    const wrapper = await render()
    const dropzone = wrapper.get('.upload-dropzone')
    await dropzone.trigger('dragenter')
    await dropzone.trigger('dragenter')
    expect(dropzone.classes()).toContain('dragging')
    await dropzone.trigger('dragleave')
    expect(dropzone.classes()).toContain('dragging')
    await dropzone.trigger('dragleave')
    expect(dropzone.classes()).not.toContain('dragging')
  })

  it('importa os XMLs soltos e remove o destaque ao concluir o drop', async () => {
    const wrapper = await render()
    vi.mocked(invoiceService.importXml).mockResolvedValueOnce(invoice)
      .mockRejectedValueOnce(new ApiError(422, { message: 'XML inválido.' }))
    const dropzone = wrapper.get('.upload-dropzone')
    const files = [new File(['<xml/>'], 'nova.xml'), new File(['inválido'], 'invalida.xml')]
    await dropzone.trigger('dragenter')
    await dropzone.trigger('drop', { dataTransfer: { files } })
    await flushPromises()
    expect(dropzone.classes()).not.toContain('dragging')
    expect(invoiceService.importXml).toHaveBeenNthCalledWith(1, '1', files[0])
    expect(invoiceService.importXml).toHaveBeenNthCalledWith(2, '1', files[1])
    expect(wrapper.text()).toContain('nova.xml: Importada')
    expect(wrapper.text()).toContain('invalida.xml: XML inválido.')
    expect(dashboardService.get).toHaveBeenCalledTimes(2)
    expect(invoiceService.list).toHaveBeenCalledTimes(2)
  })

  it('ignora drop vazio e novos arquivos durante uma importação', async () => {
    const wrapper = await render()
    const dropzone = wrapper.get('.upload-dropzone')
    await dropzone.trigger('drop', { dataTransfer: { files: [] } })
    expect(invoiceService.importXml).not.toHaveBeenCalled()

    let finishImport: ((invoice: Invoice) => void) | undefined
    vi.mocked(invoiceService.importXml).mockImplementationOnce(() => new Promise((resolve) => {
      finishImport = resolve
    }))
    const first = new File(['<xml/>'], 'primeira.xml')
    const ignored = new File(['<xml/>'], 'ignorada.xml')
    void dropzone.trigger('drop', { dataTransfer: { files: [first] } })
    await vi.waitFor(() => expect(dropzone.attributes('aria-disabled')).toBe('true'))
    await dropzone.trigger('drop', { dataTransfer: { files: [ignored] } })
    expect(invoiceService.importXml).toHaveBeenCalledTimes(1)
    finishImport?.(invoice)
    await flushPromises()
  })

  it('exibe estados vazios e projeção sem dados sem inventar estimativa', async () => {
    vi.mocked(invoiceService.list).mockResolvedValueOnce({ data: [], meta: { current_page: 1, last_page: 1, total: 0 } })
    vi.mocked(dashboardService.get).mockResolvedValueOnce({ ...metrics, projection: { amount: null, status: 'insufficient_data', completed_months: 0 } })
    const wrapper = await render()
    expect(wrapper.text()).toContain('Suas notas começam aqui.')
    expect(wrapper.text()).toContain('Aguardando o primeiro mês completo')
  })
})
