import { onScopeDispose, ref } from 'vue'
import { dashboardService } from '@/services/dashboard'
import type { DashboardMetrics } from '@/types/dashboard'
import { useMeiSession } from '@/features/meis/composables/useMeiSession'

/**
 * Mantém o recorte anual e mensal do painel sincronizado com as métricas da API.
 *
 * Importação, mudança de status e edição do limite chamam `load` novamente porque os
 * totais pertencem ao backend e nunca são recalculados na interface.
 */
export function useDashboard(meiId: string) {
  const metrics = ref<DashboardMetrics | null>(null)
  const year = ref<number>()
  const selectedMonth = ref(1)
  const loading = ref(false)
  const error = ref('')
  const handleSession = useMeiSession()
  let request = 0
  onScopeDispose(() => { request++ })

  async function load() {
    // O contador invalida respostas antigas quando ano, MEI ou tela mudam durante a requisição.
    const current = ++request
    loading.value = true
    error.value = ''
    const previousYear = metrics.value?.year
    metrics.value = null
    try {
      const result = await dashboardService.get(meiId, year.value)
      if (current !== request) return
      metrics.value = result
      year.value = result.year
      if (previousYear !== result.year) {
        selectedMonth.value = result.year === Number(result.as_of.slice(0, 4))
          ? Number(result.as_of.slice(5, 7)) : 12
      }
    } catch (cause: unknown) {
      if (current === request && !await handleSession(cause)) error.value = 'Não foi possível atualizar o faturamento.'
    } finally {
      if (current === request) loading.value = false
    }
  }
  return { metrics, year, selectedMonth, loading, error, load }
}
