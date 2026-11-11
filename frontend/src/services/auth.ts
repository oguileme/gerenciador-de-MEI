import { api, refreshCsrfToken } from '../config/api'

export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
}

interface ApiData<T> { data: T }

export const authService = {
  async register(payload: { name: string; email: string; password: string; password_confirmation: string }) {
    await refreshCsrfToken()
    return (await api.post<ApiData<AuthUser>>('/api/register', payload)).data.data
  },
  async login(payload: { email: string; password: string }) {
    await refreshCsrfToken()
    return (await api.post<ApiData<AuthUser>>('/api/login', payload)).data.data
  },
  async currentUser() {
    return (await api.get<ApiData<AuthUser>>('/api/user')).data.data
  },
  async logout() {
    await refreshCsrfToken()
    await api.post<void>('/api/logout')
  },
}
