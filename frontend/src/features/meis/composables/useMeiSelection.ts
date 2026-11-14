import { ref } from 'vue'
import { meiService } from '@/services/meis'
import type { Mei } from '@/types/mei'
import { useMeiSession } from './useMeiSession'

/** Carrega uma página por vez, preservando a opção de repetir uma falha. */
export function useMeiSelection() {
  const meis = ref<Mei[]>([])
  const loading = ref(false)
  const error = ref('')
  const page = ref(1)
  const lastPage = ref(1)
  const handleSession = useMeiSession()

  async function load(target = page.value) {
    if (loading.value) return
    loading.value = true
    error.value = ''
    page.value = target
    try {
      const result = await meiService.list(target)
      meis.value = result.data
      page.value = result.meta.current_page
      lastPage.value = result.meta.last_page
    } catch (cause: unknown) {
      if (!await handleSession(cause)) error.value = 'Não foi possível carregar seus MEIs.'
    } finally {
      loading.value = false
    }
  }

  return { meis, loading, error, page, lastPage, load }
}
