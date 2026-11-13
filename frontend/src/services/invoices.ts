import { api } from '@/config/api'
import type { Invoice, InvoicePage, InvoiceStatus } from '@/types/invoice'

/** Operações permitidas sobre notas: leitura, importação e status. */
export const invoiceService = {
  async list(meiId: string, page = 1, status?: InvoiceStatus): Promise<InvoicePage> {
    return (await api.get<InvoicePage>(`/api/meis/${meiId}/invoices`, {
      params: { page, status },
    })).data
  },
  async updateStatus(meiId: string, invoiceId: number, status: InvoiceStatus): Promise<Invoice> {
    return (await api.patch<{ data: Invoice }>(
      `/api/meis/${meiId}/invoices/${invoiceId}/status`, { status },
    )).data.data
  },
  async importXml(meiId: string, file: File): Promise<Invoice> {
    const payload = new FormData()
    payload.append('xml', file)
    return (await api.post<{ data: Invoice }>(
      `/api/meis/${meiId}/invoices/import`, payload,
    )).data.data
  },
}
