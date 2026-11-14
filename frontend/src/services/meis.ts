import { api } from '@/config/api'
import type { CreateMei, Mei, MeiPage } from '@/types/mei'

/** Acesso aos MEIs da conta autenticada; autorização sempre pertence à API. */
export const meiService = {
  async list(page = 1): Promise<MeiPage> {
    return (await api.get<MeiPage>('/api/meis', { params: { page } })).data
  },
  async create(payload: CreateMei): Promise<Mei> {
    return (await api.post<{ data: Mei }>('/api/meis', payload)).data.data
  },
  async update(id: string, payload: Omit<CreateMei, 'cnpj'>): Promise<Mei> {
    return (await api.patch<{ data: Mei }>(`/api/meis/${encodeURIComponent(id)}`, payload)).data.data
  },
  async get(id: string): Promise<Mei> {
    return (await api.get<{ data: Mei }>(`/api/meis/${encodeURIComponent(id)}`)).data.data
  },
}
