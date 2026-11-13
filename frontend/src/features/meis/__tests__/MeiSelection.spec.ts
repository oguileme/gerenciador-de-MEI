import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import { meiService } from '@/services/meis'
import { ApiError } from '@/config/api'
import type { Mei } from '@/types/mei'
import MeiSelectionView from '../MeiSelectionView.vue'
import MeiDashboardView from '../MeiDashboardView.vue'

vi.mock('@/services/meis', () => ({ meiService: { list: vi.fn<typeof meiService.list>(), get: vi.fn<typeof meiService.get>(), create: vi.fn<typeof meiService.create>() } }))
enableAutoUnmount(afterEach)
const mei: Mei = {
  id: 1, mei_name: 'Ateliê Exemplo', cnpj: '00000000000100', opening_date: '2024-01-01',
  ibge_municipality_code: '0000000', created_at: null, updated_at: null,
}

async function render(path = '/meis') {
  const router = createRouter({ history: createMemoryHistory(), routes: [
    { path: '/', component: { template: '<p>Início</p>' } },
    { path: '/login', name: 'login', component: { template: '<p>Login</p>' } },
    { path: '/meis', name: 'mei-selection', component: MeiSelectionView },
    { path: '/meis/:meiId/dashboard', name: 'mei-dashboard', component: MeiDashboardView },
  ] })
  await router.push(path)
  await router.isReady()
  const wrapper = mount({ template: '<RouterView />' }, {
    global: { plugins: [router], stubs: { DashboardShell: { template: '<p>Visão geral</p>' } } }, attachTo: document.body,
  })
  await flushPromises()
  return { wrapper, router }
}

beforeEach(() => {
  vi.mocked(meiService.list).mockResolvedValue({ data: [mei], meta: { current_page: 1, last_page: 1 } })
  vi.mocked(meiService.get).mockResolvedValue(mei)
  HTMLDialogElement.prototype.showModal = function () { this.setAttribute('open', '') }
})
afterEach(() => { vi.resetAllMocks(); document.body.innerHTML = '' })

describe('Seleção de MEIs', () => {
  it('exibe nome e CNPJ, permite adicionar e não oferece exclusão', async () => {
    const { wrapper } = await render()
    expect(wrapper.text()).toContain('Ateliê Exemplo')
    expect(wrapper.text()).toContain('00.000.000/0001-00')
    expect(wrapper.text()).toContain('Adicionar MEI')
    expect(wrapper.text()).not.toMatch(/excluir|deletar|editar/i)
    await wrapper.get('a[href="/meis/1/dashboard"]').trigger('click')
    await flushPromises()
    expect(meiService.get).toHaveBeenCalledWith('1')
    expect(wrapper.text()).toContain('Visão geral')
  })

  it('oferece cadastro para uma conta vazia', async () => {
    vi.mocked(meiService.list).mockResolvedValue({ data: [], meta: { current_page: 1, last_page: 1 } })
    const { wrapper } = await render()
    expect(wrapper.text()).toContain('Seu primeiro MEI começa aqui.')
    await wrapper.get('.add-profile').trigger('click')
    expect(wrapper.get('dialog').attributes('aria-labelledby')).toBe('create-mei-title')
    await wrapper.get('button[type="button"]').trigger('click')
    expect(wrapper.find('dialog').exists()).toBe(false)
    expect(document.activeElement).toBe(wrapper.get('.add-profile').element)
  })

  it('mostra carregamento e permite repetir uma falha', async () => {
    vi.mocked(meiService.list).mockRejectedValueOnce(new ApiError(0, null))
    const { wrapper } = await render()
    expect(wrapper.get('[role="alert"]').text()).toContain('Não foi possível carregar')
    let finish!: (value: Awaited<ReturnType<typeof meiService.list>>) => void
    vi.mocked(meiService.list).mockReturnValueOnce(new Promise((resolve) => { finish = resolve }))
    await wrapper.get('.retry').trigger('click')
    expect(wrapper.get('[role="status"]').text()).toContain('Carregando')
    finish({ data: [mei], meta: { current_page: 1, last_page: 1 } })
    await flushPromises()
    expect(wrapper.text()).toContain('Ateliê Exemplo')
  })

  it('solicita a próxima página', async () => {
    vi.mocked(meiService.list).mockResolvedValueOnce({ data: [mei], meta: { current_page: 1, last_page: 2 } })
    const { wrapper } = await render()
    vi.mocked(meiService.list).mockResolvedValueOnce({ data: [{ ...mei, id: 2, mei_name: 'Segundo MEI' }], meta: { current_page: 2, last_page: 2 } })
    await wrapper.get('.pagination button:last-child').trigger('click')
    await flushPromises()
    expect(meiService.list).toHaveBeenLastCalledWith(2)
    expect(wrapper.text()).toContain('Segundo MEI')
    expect(wrapper.text()).not.toContain('Ateliê Exemplo')
  })

  it('envia cadastro, preserva campos em 422 e abre o MEI após sucesso', async () => {
    const { wrapper, router } = await render()
    await wrapper.get('.add-profile').trigger('click')
    for (const [key, value] of Object.entries({ mei_name: 'Ateliê Exemplo', cnpj: '00000000000100', opening_date: '2024-01-01', ibge_municipality_code: '0000000' })) {
      await wrapper.get(`#${key}`).setValue(value)
    }
    vi.mocked(meiService.create).mockRejectedValueOnce(new ApiError(422, { errors: { cnpj: ['CNPJ já cadastrado.'] } }))
    await wrapper.get('form').trigger('submit')
    await flushPromises()
    expect(wrapper.get('#cnpj-error').text()).toBe('CNPJ já cadastrado.')
    expect(document.activeElement).toBe(wrapper.get('#cnpj').element)
    expect((wrapper.get('#mei_name').element as HTMLInputElement).value).toBe('Ateliê Exemplo')
    vi.mocked(meiService.create).mockResolvedValueOnce(mei)
    await wrapper.get('form').trigger('submit')
    await flushPromises()
    expect(meiService.create).toHaveBeenLastCalledWith({ mei_name: 'Ateliê Exemplo', cnpj: '00000000000100', opening_date: '2024-01-01', ibge_municipality_code: '0000000' })
    expect(router.currentRoute.value.fullPath).toBe('/meis/1/dashboard')
  })

  it('volta ao login quando a sessão expira', async () => {
    vi.mocked(meiService.list).mockRejectedValueOnce(new ApiError(401, null))
    const { router } = await render()
    expect(router.currentRoute.value.name).toBe('login')
  })

  it.each([403, 404])('não apresenta dados de um MEI indisponível (%s)', async (status) => {
    vi.mocked(meiService.get).mockRejectedValueOnce(new ApiError(status, null))
    const { wrapper } = await render('/meis/42/dashboard')
    expect(wrapper.text()).toContain('Este MEI não está disponível para sua conta.')
    expect(wrapper.text()).not.toContain('Ateliê Exemplo')
    expect(wrapper.get('a[href="/meis"]').text()).toBe('Trocar MEI')
  })
})
