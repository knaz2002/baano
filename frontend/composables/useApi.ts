import type { FetchOptions } from 'ofetch'

function xsrfToken(): string | null {
  if (!import.meta.client) {
    return null
  }

  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/)
  return match ? decodeURIComponent(match[1]) : null
}

/**
 * $fetch к Laravel API с cookies + CSRF (Sanctum SPA).
 */
export function useApi() {
  const config = useRuntimeConfig()

  const api = $fetch.create({
    baseURL: config.public.apiBase as string,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    onRequest({ options }) {
      const token = xsrfToken()
      if (!token) {
        return
      }

      const headers = new Headers(options.headers as HeadersInit)
      headers.set('X-XSRF-TOKEN', token)
      options.headers = headers
    },
  })

  async function ensureCsrf(): Promise<void> {
    await api('/sanctum/csrf-cookie', { method: 'GET' })
  }

  async function apiFetch<T>(path: string, options?: FetchOptions<'json'>): Promise<T> {
    const method = (options?.method ?? 'GET').toString().toUpperCase()
    if (method !== 'GET' && method !== 'HEAD') {
      await ensureCsrf()
    }

    return api<T>(path, options)
  }

  return {
    api,
    apiFetch,
    ensureCsrf,
  }
}
