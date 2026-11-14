import { computed, readonly, ref } from 'vue'

export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'meu-mei-theme'
const THEME_COLORS: Record<Theme, string> = { light: '#254e3a', dark: '#101713' }
const theme = ref<Theme>('light')
let initialized = false
let followsSystem = true
let systemPreference: MediaQueryList | null = null

function isTheme(value: string | null): value is Theme {
  return value === 'light' || value === 'dark'
}

function readStoredTheme(): Theme | null {
  try {
    const stored = window.localStorage.getItem(STORAGE_KEY)
    return isTheme(stored) ? stored : null
  } catch {
    return null
  }
}

function applyTheme(next: Theme) {
  theme.value = next
  document.documentElement.dataset.theme = next
  document.documentElement.style.colorScheme = next
  document.querySelector<HTMLMetaElement>('meta[name="theme-color"]')
    ?.setAttribute('content', THEME_COLORS[next])
}

function handleSystemPreference(event: MediaQueryListEvent) {
  if (followsSystem) applyTheme(event.matches ? 'dark' : 'light')
}

export function initializeTheme() {
  if (initialized || typeof window === 'undefined' || typeof document === 'undefined') return
  initialized = true
  const stored = readStoredTheme()
  followsSystem = stored === null
  systemPreference = typeof window.matchMedia === 'function'
    ? window.matchMedia('(prefers-color-scheme: dark)')
    : null
  applyTheme(stored ?? (systemPreference?.matches ? 'dark' : 'light'))
  if (followsSystem) systemPreference?.addEventListener('change', handleSystemPreference)
}

export function useTheme() {
  initializeTheme()

  function toggleTheme() {
    const next: Theme = theme.value === 'dark' ? 'light' : 'dark'
    if (followsSystem) systemPreference?.removeEventListener('change', handleSystemPreference)
    followsSystem = false
    try {
      window.localStorage.setItem(STORAGE_KEY, next)
    } catch {
      // O tema ainda muda nesta sessão quando o armazenamento do navegador está indisponível.
    }
    applyTheme(next)
  }

  return {
    theme: readonly(theme),
    isDark: computed(() => theme.value === 'dark'),
    toggleTheme,
  }
}
