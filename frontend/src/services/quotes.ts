import { api } from '@/config/api'
import type {
  Quote,
  QuotePage,
  QuoteStatus,
  SaveQuotePayload,
} from '@/types/quote'

function quotePath(meiId: string, quoteId?: number): string {
  const base = `/api/meis/${encodeURIComponent(meiId)}/quotes`
  return quoteId === undefined ? base : `${base}/${quoteId}`
}

/** Operações comerciais de orçamento, sempre escopadas ao MEI selecionado. */
export const quoteService = {
  async list(meiId: string, page = 1, status?: QuoteStatus): Promise<QuotePage> {
    return (await api.get<QuotePage>(quotePath(meiId), { params: { page, status } })).data
  },
  async get(meiId: string, quoteId: number): Promise<Quote> {
    return (await api.get<{ data: Quote }>(quotePath(meiId, quoteId))).data.data
  },
  async create(meiId: string, payload: SaveQuotePayload): Promise<Quote> {
    return (await api.post<{ data: Quote }>(quotePath(meiId), payload)).data.data
  },
  async update(meiId: string, quoteId: number, payload: SaveQuotePayload): Promise<Quote> {
    return (await api.put<{ data: Quote }>(quotePath(meiId, quoteId), payload)).data.data
  },
  async updateStatus(meiId: string, quoteId: number, status: QuoteStatus): Promise<Quote> {
    return (await api.patch<{ data: Quote }>(`${quotePath(meiId, quoteId)}/status`, { status }))
      .data.data
  },
  async remove(meiId: string, quoteId: number): Promise<void> {
    await api.delete(quotePath(meiId, quoteId))
  },
  async downloadPdf(meiId: string, quoteId: number): Promise<Blob> {
    return (await api.get<Blob>(`${quotePath(meiId, quoteId)}/pdf`, { responseType: 'blob' })).data
  },
}
