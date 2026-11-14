export type InvoiceStatus = 'active' | 'cancelled' | 'substituted'

/** Contrato do InvoiceResource; valores fiscais permanecem strings decimais. */
export interface Invoice {
  id: number
  mei_id: number
  type: string
  status: InvoiceStatus
  access_key: string
  invoice_number: string
  customer_name: string | null
  service_amount: string
  net_amount: string
  issue_date: string
  competence_date: string
  description: string
  created_at: string | null
  updated_at: string | null
}

export interface InvoicePage {
  data: Invoice[]
  meta: { current_page: number; last_page: number; total: number }
}
