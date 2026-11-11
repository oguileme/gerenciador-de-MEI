import {
  AxiosError,
  type AxiosAdapter,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios'
import { afterEach, describe, expect, it, vi } from 'vitest'
import { api, ApiError } from '../api'

const originalAdapter = api.defaults.adapter

function response(
  config: InternalAxiosRequestConfig,
  status: number,
  data: unknown,
): AxiosResponse {
  return {
    config,
    data,
    headers: {},
    status,
    statusText: '',
  }
}

function failure(config: InternalAxiosRequestConfig, status: number, data: unknown): AxiosError {
  return new AxiosError(
    'A requisição falhou.',
    AxiosError.ERR_BAD_REQUEST,
    config,
    undefined,
    response(config, status, data),
  )
}

afterEach(() => {
  api.defaults.adapter = originalAdapter
  vi.restoreAllMocks()
})

describe('Cliente da API', () => {
  it('envia cookies e configura a proteção XSRF do Sanctum', async () => {
    const adapter = vi.fn<AxiosAdapter>(async (config) => response(config, 204, undefined))
    api.defaults.adapter = adapter

    await api.get('/api/test')

    expect(api.defaults.withCredentials).toBe(true)
    expect(api.defaults.withXSRFToken).toBe(true)
    expect(api.defaults.xsrfCookieName).toBe('XSRF-TOKEN')
    expect(api.defaults.xsrfHeaderName).toBe('X-XSRF-TOKEN')
    expect(adapter.mock.calls[0]?.[0].headers.get('Accept')).toBe('application/json')
  })

  it('normaliza respostas de erro sem expor o Axios aos consumidores', async () => {
    const body = { errors: { email: ['O e-mail informado é inválido.'] } }
    api.defaults.adapter = vi.fn<AxiosAdapter>(async (config) => {
      throw failure(config, 422, body)
    })

    const request = api.post('/api/login', {})

    await expect(request).rejects.toBeInstanceOf(ApiError)
    await expect(request).rejects.toMatchObject({ status: 422, body })
  })

  it('renova o CSRF e repete uma requisição que recebeu 419', async () => {
    let loginAttempts = 0
    const adapter = vi.fn<AxiosAdapter>(async (config) => {
      if (config.url === '/sanctum/csrf-cookie') return response(config, 204, undefined)
      loginAttempts += 1
      if (loginAttempts === 1) throw failure(config, 419, undefined)
      return response(config, 200, { data: { id: 1 } })
    })
    api.defaults.adapter = adapter

    const result = await api.post('/api/login', {})

    expect(result.data).toEqual({ data: { id: 1 } })
    expect(adapter.mock.calls.map(([config]) => config.url)).toEqual([
      '/api/login',
      '/sanctum/csrf-cookie',
      '/api/login',
    ])
  })

  it('repete a requisição no máximo uma vez após renovar o CSRF', async () => {
    const adapter = vi.fn<AxiosAdapter>(async (config) => {
      if (config.url === '/sanctum/csrf-cookie') return response(config, 204, undefined)
      throw failure(config, 419, undefined)
    })
    api.defaults.adapter = adapter

    const request = api.post('/api/login', {})

    await expect(request).rejects.toMatchObject({ status: 419 })
    expect(adapter.mock.calls.map(([config]) => config.url)).toEqual([
      '/api/login',
      '/sanctum/csrf-cookie',
      '/api/login',
    ])
  })

  it('não tenta renovar o CSRF quando o próprio endpoint de renovação falha', async () => {
    const adapter = vi.fn<AxiosAdapter>(async (config) => {
      throw failure(config, 419, undefined)
    })
    api.defaults.adapter = adapter

    const request = api.get('/sanctum/csrf-cookie')

    await expect(request).rejects.toMatchObject({ status: 419 })
    expect(adapter).toHaveBeenCalledOnce()
  })
})
