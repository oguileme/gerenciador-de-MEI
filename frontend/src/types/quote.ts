export type QuoteStatus = 'pending' | 'accepted' | 'rejected'

/** Linha calculada pelo backend; todos os decimais permanecem strings. */
export interface QuoteItem {
  id: number
  description: string
  unit_price: string
  quantity: string
  subtotal: string
}

/** Contrato do QuoteResource; `items` só vem carregado na consulta detalhada. */
export interface Quote {
  id: number
  mei_id: number
  issuer_name: string
  recipient_name: string
  issue_date: string
  status: QuoteStatus
  total_amount: string
  items?: QuoteItem[]
  created_at: string | null
  updated_at: string | null
}

export interface QuoteItemInput {
  description: string
  unit_price: string
  quantity: string
}

export interface SaveQuotePayload {
  recipient_name: string
  items: QuoteItemInput[]
}

export interface QuotePage {
  data: Quote[]
  meta: { current_page: number; last_page: number; total: number }
}
