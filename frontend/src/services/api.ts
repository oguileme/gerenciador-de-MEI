export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: unknown,
  ) {
    super('A API retornou um erro.')
  }
}

function csrfToken(): string | undefined {
  const cookie = document.cookie.split('; ').find((item) => item.startsWith('XSRF-TOKEN='))
  return cookie ? decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) : undefined
}

async function request<T>(path: string, init: RequestInit = {}, retry = true): Promise<T> {
  const headers = new Headers(init.headers)
  headers.set('Accept', 'application/json')
  if (init.body && !headers.has('Content-Type')) headers.set('Content-Type', 'application/json')
  const token = csrfToken()
  if (token) headers.set('X-XSRF-TOKEN', token)

  const response = await fetch(path, { ...init, headers, credentials: 'include' })
  if (response.status === 419 && retry) {
    await request<unknown>('/sanctum/csrf-cookie', { method: 'GET' }, false)
    return request<T>(path, init, false)
  }
  if (!response.ok) {
    let body: unknown = undefined
    try { body = await response.json() } catch { /* resposta sem JSON */ }
    throw new ApiError(response.status, body)
  }
  if (response.status === 204) return undefined as T
  return response.json() as Promise<T>
}

export const api = {
  csrf: () => request<void>('/sanctum/csrf-cookie'),
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, body?: unknown) => request<T>(path, {
    method: 'POST',
    body: body === undefined ? undefined : JSON.stringify(body),
  }),
}
