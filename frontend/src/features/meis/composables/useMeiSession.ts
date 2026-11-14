import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { ApiError } from '@/config/api'

/** Encerra o contexto local quando a API informa que a sessão expirou. */
export function useMeiSession() {
  const router = useRouter()
  const auth = useAuth()
  return async (error: unknown): Promise<boolean> => {
    if (!(error instanceof ApiError) || error.status !== 401) return false
    auth.user.value = null
    await router.replace({ name: 'login' })
    return true
  }
}
