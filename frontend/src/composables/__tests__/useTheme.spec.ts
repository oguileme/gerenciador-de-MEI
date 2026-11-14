import { beforeEach, describe, expect, it, vi } from 'vitest'

interface MatchMediaMock {
  media: MediaQueryList
  emit: (matches: boolean) => void
}

function mockMatchMedia(initial: boolean): MatchMediaMock {
  let listener: ((event: MediaQueryListEvent) => void) | undefined
  const media = {
    matches: initial,
    media: '(prefers-color-scheme: dark)',
    onchange: null,
    addEventListener: vi.fn<(type: string, callback: EventListenerOrEventListenerObject) => void>((_type, callback) => {
      listener = callback as (event: MediaQueryListEvent) => void
    }),
    removeEventListener: vi.fn<(...args: unknown[]) => void>(),
    addListener: vi.fn<(...args: unknown[]) => void>(),
    removeListener: vi.fn<(...args: unknown[]) => void>(),
    dispatchEvent: vi.fn<(event: Event) => boolean>(() => true),
  } as unknown as MediaQueryList
  vi.stubGlobal('matchMedia', vi.fn<(query: string) => MediaQueryList>(() => media))
  return {
    media,
    emit: (matches: boolean) => listener?.({ matches } as MediaQueryListEvent),
  }
}

async function loadTheme() {
  vi.resetModules()
  return import('../useTheme')
}

beforeEach(() => {
  window.localStorage.clear()
  delete document.documentElement.dataset.theme
  document.documentElement.style.colorScheme = ''
  let themeColor = document.querySelector<HTMLMetaElement>('meta[name="theme-color"]')
  if (!themeColor) {
    themeColor = document.createElement('meta')
    themeColor.name = 'theme-color'
    document.head.append(themeColor)
  }
  themeColor.content = '#254e3a'
  vi.unstubAllGlobals()
})

describe('tema da aplicação', () => {
  it('prioriza a escolha salva sobre o sistema', async () => {
    window.localStorage.setItem('meu-mei-theme', 'light')
    mockMatchMedia(true)
    const { initializeTheme, useTheme } = await loadTheme()
    initializeTheme()
    expect(useTheme().theme.value).toBe('light')
    expect(document.documentElement.dataset.theme).toBe('light')
  })

  it('acompanha o sistema enquanto não existe escolha manual', async () => {
    const system = mockMatchMedia(true)
    const { initializeTheme, useTheme } = await loadTheme()
    initializeTheme()
    expect(useTheme().theme.value).toBe('dark')
    system.emit(false)
    expect(useTheme().theme.value).toBe('light')
  })

  it('persiste a escolha manual e deixa de acompanhar o sistema', async () => {
    const system = mockMatchMedia(false)
    const { initializeTheme, useTheme } = await loadTheme()
    initializeTheme()
    const state = useTheme()
    state.toggleTheme()
    expect(state.theme.value).toBe('dark')
    expect(window.localStorage.getItem('meu-mei-theme')).toBe('dark')
    expect(document.documentElement.style.colorScheme).toBe('dark')
    expect(document.querySelector('meta[name="theme-color"]')?.getAttribute('content')).toBe('#101713')
    expect(system.media.removeEventListener).toHaveBeenCalledWith('change', expect.any(Function))
    system.emit(false)
    expect(state.theme.value).toBe('dark')
  })

  it('usa o tema claro quando matchMedia não está disponível', async () => {
    const { initializeTheme, useTheme } = await loadTheme()
    initializeTheme()
    expect(useTheme().theme.value).toBe('light')
  })
})
