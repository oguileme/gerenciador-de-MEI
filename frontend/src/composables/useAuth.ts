import { ref } from 'vue'
import { ApiError } from '../services/api'
import { authService, type AuthUser } from '../services/auth'

const user = ref<AuthUser | null>(null)
const initialized = ref(false)
const loading = ref(false)
let initialization: Promise<void> | null = null

export function useAuth() {
  async function refreshUser(): Promise<void> {
    if (initialization) return initialization
    initialization = authService.currentUser()
      .then((current) => { user.value = current })
      .catch((error: unknown) => {
        if (!(error instanceof ApiError) || error.status !== 401) throw error
        user.value = null
      })
      .finally(() => { initialized.value = true; initialization = null })
    return initialization
  }

  async function login(email: string, password: string) {
    loading.value = true
    try { user.value = await authService.login({ email, password }) } finally { loading.value = false }
  }

  async function register(name: string, email: string, password: string, passwordConfirmation: string) {
    loading.value = true
    try { user.value = await authService.register({ name, email, password, password_confirmation: passwordConfirmation }) } finally { loading.value = false }
  }

  async function logout() {
    await authService.logout()
    user.value = null
  }

  return { user, initialized, loading, refreshUser, login, register, logout }
}
