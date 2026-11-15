import { onScopeDispose, ref } from 'vue'
import { ApiError } from '@/config/api'
import { useMeiSession } from '@/features/meis/composables/useMeiSession'
import { quoteService } from '@/services/quotes'
import type { Quote, QuoteStatus, SaveQuotePayload } from '@/types/quote'

function validationErrors(cause: unknown): Record<string, string> {
  if (!(cause instanceof ApiError) || cause.status !== 422
    || !cause.body || typeof cause.body !== 'object' || !('errors' in cause.body)
    || !cause.body.errors || typeof cause.body.errors !== 'object') return {}

  return Object.fromEntries(Object.entries(cause.body.errors).flatMap(([field, messages]) => {
    if (!Array.isArray(messages) || typeof messages[0] !== 'string') return []
    return [[field, messages[0]]]
  }))
}

/** Coordena listagem e mutações sem permitir que respostas antigas troquem o MEI atual. */
export function useQuotes(meiId: string) {
  const quotes = ref<Quote[]>([])
  const page = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const status = ref<QuoteStatus | ''>('')
  const loading = ref(false)
  const busy = ref(false)
  const loadingDetail = ref<number | null>(null)
  const error = ref('')
  const notice = ref('')
  const fieldErrors = ref<Record<string, string>>({})
  const handleSession = useMeiSession()
  let request = 0
  let disposed = false
  onScopeDispose(() => { request++; disposed = true })

  async function load(target = page.value) {
    const current = ++request
    loading.value = true
    error.value = ''
    try {
      const result = await quoteService.list(meiId, target, status.value || undefined)
      if (current !== request) return
      quotes.value = result.data
      page.value = result.meta.current_page
      lastPage.value = result.meta.last_page
      total.value = result.meta.total
    } catch (cause: unknown) {
      if (current === request && !await handleSession(cause)) {
        error.value = 'Não foi possível carregar os orçamentos.'
      }
    } finally {
      if (current === request) loading.value = false
    }
  }

  async function loadDetail(quote: Quote): Promise<Quote | null> {
    if (quote.items) return quote
    loadingDetail.value = quote.id
    try {
      const detailed = await quoteService.get(meiId, quote.id)
      if (!disposed) replaceQuote(detailed)
      return detailed
    } catch (cause: unknown) {
      if (!await handleSession(cause)) notice.value = 'Não foi possível abrir este orçamento.'
      return null
    } finally {
      loadingDetail.value = null
    }
  }

  async function save(payload: SaveQuotePayload, quoteId?: number): Promise<boolean> {
    if (busy.value) return false
    busy.value = true
    fieldErrors.value = {}
    notice.value = ''
    try {
      if (quoteId === undefined) await quoteService.create(meiId, payload)
      else await quoteService.update(meiId, quoteId, payload)
      if (disposed) return false
      notice.value = quoteId === undefined ? 'Orçamento registrado.' : 'Orçamento atualizado.'
      await load(1)
      return true
    } catch (cause: unknown) {
      if (await handleSession(cause)) return false
      fieldErrors.value = validationErrors(cause)
      notice.value = cause instanceof ApiError && cause.status === 409
        ? 'Este orçamento não está mais pendente.'
        : 'Revise os campos indicados e tente novamente.'
      return false
    } finally {
      busy.value = false
    }
  }

  async function changeStatus(quote: Quote, next: QuoteStatus) {
    if (busy.value || quote.status === next) return
    busy.value = true
    notice.value = ''
    try {
      replaceQuote(await quoteService.updateStatus(meiId, quote.id, next))
      notice.value = 'Status do orçamento atualizado.'
    } catch (cause: unknown) {
      if (!await handleSession(cause)) notice.value = 'Não foi possível alterar o status.'
    } finally {
      busy.value = false
    }
  }

  async function remove(quote: Quote): Promise<boolean> {
    if (busy.value) return false
    busy.value = true
    notice.value = ''
    try {
      await quoteService.remove(meiId, quote.id)
      notice.value = 'Orçamento excluído.'
      await load(1)
      return true
    } catch (cause: unknown) {
      if (!await handleSession(cause)) notice.value = 'Não foi possível excluir o orçamento.'
      return false
    } finally {
      busy.value = false
    }
  }

  async function download(quote: Quote): Promise<Blob | null> {
    try {
      return await quoteService.downloadPdf(meiId, quote.id)
    } catch (cause: unknown) {
      if (!await handleSession(cause)) notice.value = 'Não foi possível baixar o PDF.'
      return null
    }
  }

  function replaceQuote(updated: Quote) {
    const index = quotes.value.findIndex((quote) => quote.id === updated.id)
    if (index !== -1) quotes.value[index] = updated
  }

  return { quotes, page, lastPage, total, status, loading, busy, loadingDetail,
    error, notice, fieldErrors, load, loadDetail, save, changeStatus, remove, download }
}
