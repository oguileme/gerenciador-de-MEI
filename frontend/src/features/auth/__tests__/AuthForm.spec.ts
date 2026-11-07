import { afterEach, describe, expect, it, vi } from 'vitest'
import { enableAutoUnmount, mount } from '@vue/test-utils'
import AuthForm from '../components/AuthForm.vue'
import type { AuthMode } from '../types/auth'

enableAutoUnmount(afterEach)

function renderForm(mode: AuthMode = 'register') {
  return mount(AuthForm, { props: { mode }, attachTo: document.body })
}

afterEach(() => {
  document.body.innerHTML = ''
  vi.restoreAllMocks()
  vi.unstubAllGlobals()
})

describe('Formulários estáticos de autenticação', () => {
  it('solicita nome, e-mail, senha e confirmação no cadastro', () => {
    const wrapper = renderForm()
    expect(wrapper.findAll('input').map((input) => input.attributes('name'))).toEqual([
      'name', 'email', 'password', 'password_confirmation',
    ])
    expect(wrapper.get('#password').attributes('autocomplete')).toBe('new-password')
  })

  it('solicita somente e-mail e senha no login', () => {
    const wrapper = renderForm('login')
    expect(wrapper.findAll('input').map((input) => input.attributes('name'))).toEqual([
      'email', 'password',
    ])
    expect(wrapper.get('#password').attributes('autocomplete')).toBe('current-password')
  })

  it('mostra os campos obrigatórios e direciona o foco para o primeiro erro', async () => {
    const wrapper = renderForm()
    await wrapper.get('form').trigger('submit')
    expect(wrapper.text()).toContain('Informe seu nome.')
    expect(wrapper.text()).toContain('Informe seu e-mail.')
    expect(wrapper.text()).toContain('Informe sua senha.')
    expect(wrapper.text()).toContain('Repita sua senha.')
    expect(document.activeElement).toBe(wrapper.get('#name').element)
    expect(wrapper.get('#name').attributes('aria-describedby')).toBe('name-error')
    expect(wrapper.find('[role="status"]').exists()).toBe(false)
  })

  it('valida o e-mail ao sair do campo e remove o erro ao corrigir', async () => {
    const wrapper = renderForm('login')
    await wrapper.get('#email').setValue('email-invalido')
    expect(wrapper.find('#email-error').exists()).toBe(false)
    await wrapper.get('#email').trigger('blur')
    expect(wrapper.get('#email-error').text()).toContain('Informe um e-mail válido')
    await wrapper.get('#email').setValue('pessoa@example.com')
    expect(wrapper.find('#email-error').exists()).toBe(false)
  })

  it('rejeita um nome formado somente por espaços', async () => {
    const wrapper = renderForm()
    await wrapper.get('#name').setValue('   ')
    await wrapper.get('#name').trigger('blur')
    expect(wrapper.get('#name-error').text()).toBe('Informe seu nome.')
  })

  it('verifica a confirmação novamente quando a senha original muda', async () => {
    const wrapper = renderForm()
    await wrapper.get('#password').setValue('senha-ficticia')
    await wrapper.get('#password_confirmation').setValue('outra-senha')
    await wrapper.get('#password_confirmation').trigger('blur')
    expect(wrapper.text()).toContain('As senhas não coincidem.')
    await wrapper.get('#password').setValue('outra-senha')
    expect(wrapper.find('#password_confirmation-error').exists()).toBe(false)
  })

  it('alterna a visibilidade de cada senha de forma independente sem perder o valor', async () => {
    const wrapper = renderForm()
    await wrapper.get('#password').setValue('senha-ficticia')
    await wrapper.get('button[aria-controls="password"]').trigger('click')
    expect(wrapper.get('#password').attributes('type')).toBe('text')
    expect(wrapper.get('#password_confirmation').attributes('type')).toBe('password')
    expect(wrapper.get('button[aria-controls="password"]').attributes('aria-label')).toBe('Ocultar senha')
    await wrapper.get('button[aria-controls="password"]').trigger('click')
    expect(wrapper.get('#password').attributes('type')).toBe('password')
    expect((wrapper.get('#password').element as HTMLInputElement).value).toBe('senha-ficticia')
  })

  it.each<AuthMode>(['login', 'register'])(
    'envia o formulário de %s sem persistir tokens no navegador',
    async (mode) => {
      const fetch = vi.fn<typeof globalThis.fetch>()
        .mockResolvedValueOnce(new Response(null, { status: 204 }))
        .mockResolvedValueOnce(new Response(JSON.stringify({ data: { id: 1, name: 'Pessoa de Teste', email: 'pessoa@example.com' } }), { status: 200 }))
      vi.stubGlobal('fetch', fetch)
      const storage = vi.spyOn(Storage.prototype, 'setItem')
      const wrapper = renderForm(mode)
      if (mode === 'register') {
        await wrapper.get('#name').setValue('Pessoa de Teste')
        await wrapper.get('#password_confirmation').setValue('senha-ficticia')
      }
      await wrapper.get('#email').setValue('pessoa@example.com')
      await wrapper.get('#password').setValue('senha-ficticia')
      await wrapper.get('form').trigger('submit')
      expect(fetch).toHaveBeenCalledTimes(2)
      expect(fetch.mock.calls[1]?.[0]).toBe(`/api/${mode}`)
      expect(storage).not.toHaveBeenCalled()
      await wrapper.get('#email').setValue('outro@example.com')
      expect(wrapper.find('[role="status"]').exists()).toBe(false)
    },
  )
})
