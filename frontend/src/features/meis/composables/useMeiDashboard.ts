import { onScopeDispose, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ApiError } from '@/config/api'
import { meiService } from '@/services/meis'
import type { Mei } from '@/types/mei'
import { useMeiSession } from './useMeiSession'

/** Resolve o contexto pela URL sem conservar dados do MEI anterior. */
export function useMeiDashboard() {
  const route = useRoute()
  const mei = ref<Mei | null>(null)
  const error = ref('')
  const loading = ref(false)
  const handleSession = useMeiSession()
  let request = 0
  onScopeDispose(() => { request++ })

  async function load() {
    const current = ++request
    mei.value = null
    error.value = ''
    loading.value = true
    try {
      const result = await meiService.get(String(route.params.meiId))
      if (current === request) mei.value = result
    } catch (cause: unknown) {
      if (current !== request || await handleSession(cause)) return
      error.value = cause instanceof ApiError && [403, 404].includes(cause.status)
        ? 'Este MEI não está disponível para sua conta. Escolha outro MEI.'
        : 'Não foi possível abrir este MEI. Tente novamente.'
    } finally {
      if (current === request) loading.value = false
    }
  }
  watch(() => route.params.meiId, load, { immediate: true })
  return { mei, error, loading, load }
}
