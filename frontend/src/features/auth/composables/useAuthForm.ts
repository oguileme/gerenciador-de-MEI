import { computed, reactive, ref, watch } from 'vue'
import { ApiError } from '../../../config/api'
import { useAuth } from '../../../composables/useAuth'
import type { AuthFieldName, AuthFormValues, AuthMode } from '../types/auth'

/** Valida a prévia local sem enviar, persistir dados ou simular uma sessão autenticada. */
export function useAuthForm(mode: AuthMode) {
  const auth = useAuth()
  const values = reactive<AuthFormValues>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })
  const touched = reactive(new Set<AuthFieldName>())
  const notice = ref('')
  const submitting = ref(false)
  const fields: AuthFieldName[] = mode === 'register'
    ? ['name', 'email', 'password', 'password_confirmation']
    : ['email', 'password']

  function errorFor(field: AuthFieldName): string {
    if (field === 'name') {
      if (!values.name.trim()) return 'Informe seu nome.'
      if (values.name.trim().length > 255) return 'Use até 255 caracteres no nome.'
    }
    if (field === 'email') {
      if (!values.email.trim()) return 'Informe seu e-mail.'
      if (values.email.trim().length > 255 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email.trim())) {
        return 'Informe um e-mail válido, como voce@exemplo.com.'
      }
    }
    // O backend ainda não define uma política de complexidade ou tamanho mínimo de senha.
    if (field === 'password' && !values.password) return 'Informe sua senha.'
    if (field === 'password_confirmation') {
      if (!values.password_confirmation) return 'Repita sua senha.'
      if (values.password_confirmation !== values.password) return 'As senhas não coincidem.'
    }
    return ''
  }

  const errors = computed(() => {
    const messages: Partial<Record<AuthFieldName, string>> = {}
    for (const field of fields) {
      if (touched.has(field)) messages[field] = errorFor(field)
    }
    return messages
  })

  function touch(field: AuthFieldName) {
    touched.add(field)
  }

  async function submit(): Promise<boolean> {
    fields.forEach(touch)
    notice.value = ''
    if (fields.some((field) => errorFor(field))) return false

    submitting.value = true
    try {
      if (mode === 'register') {
        await auth.register(values.name.trim(), values.email.trim(), values.password, values.password_confirmation)
      } else {
        await auth.login(values.email.trim(), values.password)
      }
      notice.value = 'Autenticação realizada com sucesso.'
      return true
    } catch (error: unknown) {
      if (error instanceof ApiError && error.status === 422 && error.body && typeof error.body === 'object') {
        const body = error.body as { errors?: Record<string, string[]> }
        for (const [field, messages] of Object.entries(body.errors ?? {})) {
          if (fields.includes(field as AuthFieldName)) {
            notice.value = messages[0] ?? 'Confira os dados informados.'
          }
        }
      } else if (error instanceof ApiError && error.status === 401) {
        notice.value = 'E-mail ou senha inválidos.'
      } else {
        notice.value = 'Não foi possível concluir a operação. Tente novamente.'
      }
      return false
    } finally {
      submitting.value = false
    }
  }

  watch(values, () => { notice.value = '' })

  return { values, errors, notice, submitting, touch, submit }
}
