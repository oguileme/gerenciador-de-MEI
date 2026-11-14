import { reactive, ref } from 'vue'
import { ApiError } from '@/config/api'
import { meiService } from '@/services/meis'
import type { CreateMei, Mei } from '@/types/mei'
import { useMeiSession } from './useMeiSession'

/** Mantém os dados em memória e apresenta erros de validação devolvidos pela API. */
export function useCreateMei() {
  const values = reactive<CreateMei>({
    mei_name: '', cnpj: '', opening_date: '', ibge_municipality_code: '',
  })
  const errors = ref<Partial<Record<keyof CreateMei, string>>>({})
  const error = ref('')
  const submitting = ref(false)
  const handleSession = useMeiSession()

  async function submit(): Promise<Mei | undefined> {
    if (submitting.value) return
    submitting.value = true
    errors.value = {}
    error.value = ''
    try {
      return await meiService.create({ ...values, mei_name: values.mei_name.trim() })
    } catch (cause: unknown) {
      if (await handleSession(cause)) return
      if (cause instanceof ApiError && cause.status === 422) {
        const body = cause.body
        if (body && typeof body === 'object' && 'errors' in body
          && body.errors && typeof body.errors === 'object') {
          for (const field of Object.keys(values) as (keyof CreateMei)[]) {
            const messages: unknown = Reflect.get(body.errors, field)
            if (Array.isArray(messages) && typeof messages[0] === 'string') {
              errors.value[field] = messages[0]
            }
          }
        }
        error.value = 'Confira os campos para cadastrar seu MEI.'
        return
      }
      error.value = 'Não foi possível cadastrar o MEI. Tente novamente.'
    } finally {
      submitting.value = false
    }
  }

  return { values, errors, error, submitting, submit }
}
