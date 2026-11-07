import { api } from './api'

export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
}

interface ApiData<T> { data: T }

export const authService = {
  async register(payload: { name: string; email: string; password: string; password_confirmation: string }) {
    await api.csrf()
    return (await api.post<ApiData<AuthUser>>('/api/register', payload)).data
  },
  async login(payload: { email: string; password: string }) {
    await api.csrf()
    return (await api.post<ApiData<AuthUser>>('/api/login', payload)).data
  },
  async currentUser() {
    return (await api.get<ApiData<AuthUser>>('/api/user')).data
  },
  async logout() {
    await api.csrf()
    await api.post<void>('/api/logout')
  },
}
