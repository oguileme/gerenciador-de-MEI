import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ThemeToggle from '../ThemeToggle.vue'

beforeEach(() => {
  window.localStorage.clear()
  vi.stubGlobal('matchMedia', vi.fn<(query: string) => Pick<MediaQueryList, 'matches' | 'addEventListener' | 'removeEventListener'>>(() => ({
    matches: false,
    addEventListener: vi.fn<(...args: unknown[]) => void>(),
    removeEventListener: vi.fn<(...args: unknown[]) => void>(),
  })))
})

afterEach(() => vi.unstubAllGlobals())

describe('seletor de tema', () => {
  it('informa a ação e alterna para o modo escuro', async () => {
    const wrapper = mount(ThemeToggle)
    const button = wrapper.get('button')
    expect(button.attributes('aria-label')).toBe('Ativar modo escuro')
    expect(button.attributes('aria-pressed')).toBe('false')
    await button.trigger('click')
    expect(button.attributes('aria-label')).toBe('Ativar modo claro')
    expect(button.attributes('aria-pressed')).toBe('true')
    expect(document.documentElement.dataset.theme).toBe('dark')
  })
})
