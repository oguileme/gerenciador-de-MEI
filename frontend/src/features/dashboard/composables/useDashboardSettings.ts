import { reactive, ref } from 'vue'
import { ApiError } from '@/config/api'
import { meiService } from '@/services/meis'
import { dashboardService } from '@/services/dashboard'
import type { Mei } from '@/types/mei'
import { useMeiSession } from '@/features/meis/composables/useMeiSession'

/** Grava dados cadastrais ou limite anual, mantendo os campos em caso de falha. */
export function useDashboardSettings(mei: Mei, year: number, amount: string, mode: 'mei' | 'limit') {
  const values = reactive({ mei_name: mei.mei_name, opening_date: mei.opening_date ?? '',
    ibge_municipality_code: mei.ibge_municipality_code, amount: amount.replace('.', ',') })
  const errors = ref<Record<string, string>>({})
  const error = ref('')
  const submitting = ref(false)
  const handleSession = useMeiSession()

  async function save(): Promise<Mei | true | undefined> {
    if (submitting.value) return
    errors.value = {}
    error.value = ''
    submitting.value = true
    try {
      if (mode === 'limit') {
        const normalized = values.amount.trim().replace(',', '.')
        if (!/^(?:0|[1-9]\d{0,12})(?:\.\d{1,2})?$/.test(normalized)) {
          errors.value.amount = 'Informe um valor sem separador de milhar, como 81000,00.'
          return
        }
        const [integer = '0', fraction = ''] = normalized.split('.')
        await dashboardService.updateLimit(String(mei.id), year, `${integer}.${fraction.padEnd(2, '0')}`)
        return true
      }
      return await meiService.update(String(mei.id), {
        mei_name: values.mei_name.trim(), opening_date: values.opening_date,
        ibge_municipality_code: values.ibge_municipality_code,
      })
    } catch (cause: unknown) {
      if (await handleSession(cause)) return
      if (cause instanceof ApiError && cause.status === 422 && cause.body
        && typeof cause.body === 'object' && 'errors' in cause.body
        && cause.body.errors && typeof cause.body.errors === 'object') {
        for (const [field, messages] of Object.entries(cause.body.errors)) {
          if (Array.isArray(messages) && typeof messages[0] === 'string') errors.value[field] = messages[0]
        }
      }
      error.value = 'Não foi possível salvar. Confira os campos e tente novamente.'
    } finally {
      submitting.value = false
    }
  }
  return { values, errors, error, submitting, save }
}
