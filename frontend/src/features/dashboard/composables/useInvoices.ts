import { onScopeDispose, ref } from 'vue'
import { ApiError } from '@/config/api'
import { invoiceService } from '@/services/invoices'
import type { Invoice, InvoiceStatus } from '@/types/invoice'
import { useMeiSession } from '@/features/meis/composables/useMeiSession'

/** Mantém listagem e mutações restritas ao MEI da instância atual do painel. */
export function useInvoices(meiId: string, changed: () => Promise<void>) {
  const invoices = ref<Invoice[]>([])
  const page = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const status = ref<InvoiceStatus | ''>('')
  const loading = ref(false)
  const error = ref('')
  const notice = ref('')
  const updating = ref<number | null>(null)
  const importing = ref(false)
  const uploads = ref<{ name: string; message: string; success: boolean }[]>([])
  const handleSession = useMeiSession()
  let request = 0
  let disposed = false
  onScopeDispose(() => { request++; disposed = true })

  async function load(target = page.value) {
    const current = ++request
    loading.value = true
    error.value = ''
    try {
      const result = await invoiceService.list(meiId, target, status.value || undefined)
      if (current !== request) return
      invoices.value = result.data
      page.value = result.meta.current_page
      lastPage.value = result.meta.last_page
      total.value = result.meta.total
    } catch (cause: unknown) {
      if (current === request && !await handleSession(cause)) {
        error.value = 'Não foi possível carregar as notas fiscais.'
      }
    } finally {
      if (current === request) loading.value = false
    }
  }

  async function updateStatus(invoice: Invoice, next: InvoiceStatus) {
    if (updating.value !== null) return
    updating.value = invoice.id
    notice.value = ''
    try {
      await invoiceService.updateStatus(meiId, invoice.id, next)
      if (disposed) return
      notice.value = 'Status atualizado. O faturamento considera apenas notas ativas.'
      await Promise.all([load(1), changed()])
    } catch (cause: unknown) {
      if (!disposed && !await handleSession(cause)) notice.value = 'Não foi possível alterar o status. Tente novamente.'
    } finally {
      updating.value = null
    }
  }

  async function importFiles(files: File[]) {
    if (importing.value || !files.length) return
    importing.value = true
    uploads.value = []
    let imported = false
    try {
      for (const file of files) {
        if (disposed) break
        try {
          await invoiceService.importXml(meiId, file)
          uploads.value.push({ name: file.name, message: 'Importada', success: true })
          imported = true
        } catch (cause: unknown) {
          if (await handleSession(cause)) break
          let message = 'Não foi possível importar. Tente novamente.'
          if (cause instanceof ApiError && [409, 422].includes(cause.status)) {
            if (cause.body && typeof cause.body === 'object' && 'message' in cause.body
              && typeof cause.body.message === 'string') message = cause.body.message
          }
          uploads.value.push({ name: file.name, message, success: false })
        }
      }
      if (imported && !disposed) await Promise.all([load(1), changed()])
    } finally {
      importing.value = false
    }
  }

  return { invoices, page, lastPage, total, status, loading, error, notice,
    updating, importing, uploads, load, updateStatus, importFiles }
}
