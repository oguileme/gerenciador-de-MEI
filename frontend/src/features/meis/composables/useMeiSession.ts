import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { ApiError } from '@/config/api'

/**
 * Converte uma resposta 401 em encerramento do contexto local e retorno ao login.
 *
 * O booleano informa ao chamador que o erro já foi tratado, evitando mensagens duplicadas.
 */
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
