import { api } from '@/config/api'
import type { DashboardMetrics } from '@/types/dashboard'

/** Lê métricas calculadas e grava somente a configuração anual do limite. */
export const dashboardService = {
  async get(meiId: string, year?: number): Promise<DashboardMetrics> {
    return (await api.get<{ data: DashboardMetrics }>(`/api/meis/${meiId}/dashboard`, {
      params: { year },
    })).data.data
  },
  async updateLimit(meiId: string, year: number, amount: string): Promise<DashboardMetrics> {
    return (await api.put<{ data: DashboardMetrics }>(`/api/meis/${meiId}/annual-limit`, {
      year, amount,
    })).data.data
  },
}
