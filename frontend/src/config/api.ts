import axios, { type AxiosError, type InternalAxiosRequestConfig } from 'axios'

const CSRF_ENDPOINT = '/sanctum/csrf-cookie'

interface RetriableRequestConfig extends InternalAxiosRequestConfig {
  retriedAfterCsrfRefresh?: boolean
}

/** Erro estável da aplicação para que componentes não dependam de detalhes do Axios. */
export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: unknown,
  ) {
    super('A API retornou um erro.')
  }
}

/** Cliente único da API, configurado para a autenticação Sanctum baseada em cookie. */
export const api = axios.create({
  withCredentials: true,
  withXSRFToken: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    Accept: 'application/json',
  },
})

function toApiError(error: AxiosError): ApiError {
  return new ApiError(error.response?.status ?? 0, error.response?.data)
}

api.interceptors.response.use(
  (response) => response,
  async (error: unknown) => {
    if (!axios.isAxiosError(error)) return Promise.reject(error)

    const config = error.config as RetriableRequestConfig | undefined
    const shouldRefreshCsrf = error.response?.status === 419
      && config !== undefined
      && config.url !== CSRF_ENDPOINT
      && !config.retriedAfterCsrfRefresh

    if (shouldRefreshCsrf) {
      // Uma única repetição recupera sessões expiradas sem criar um ciclo em falhas persistentes.
      config.retriedAfterCsrfRefresh = true
      await refreshCsrfToken()
      return api.request(config)
    }

    return Promise.reject(toApiError(error))
  },
)

export function refreshCsrfToken() {
  return api.get<void>(CSRF_ENDPOINT)
}
