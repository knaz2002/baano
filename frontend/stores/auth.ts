import { defineStore } from 'pinia'

export type AuthUser = {
  id: number
  name: string
  email: string
  phone: string | null
  email_verified_at: string | null
  phone_verified_at: string | null
}

type UserResponse = {
  data: AuthUser | null
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as AuthUser | null,
    loaded: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
    isEmailVerified: (state) => !!state.user?.email_verified_at,
  },
  actions: {
    async fetchUser() {
      const { apiFetch } = useApi()
      try {
        const res = await apiFetch<UserResponse>('/api/me')
        this.user = res.data
      } catch {
        this.user = null
      } finally {
        this.loaded = true
      }
    },
    async login(email: string, password: string, remember = false) {
      const { apiFetch } = useApi()
      const res = await apiFetch<UserResponse>('/api/login', {
        method: 'POST',
        body: { email, password, remember },
      })
      this.user = res.data
      this.loaded = true
    },
    async register(payload: {
      name: string
      email: string
      phone: string
      password: string
      password_confirmation: string
    }) {
      const { apiFetch } = useApi()
      const res = await apiFetch<UserResponse>('/api/register', {
        method: 'POST',
        body: payload,
      })
      this.user = res.data
      this.loaded = true
    },
    async logout() {
      const { apiFetch } = useApi()
      await apiFetch('/api/logout', { method: 'POST' })
      this.user = null
    },
    async resendVerification() {
      const { apiFetch } = useApi()
      return apiFetch<{ ok: boolean; message: string }>('/api/email/verification-notification', {
        method: 'POST',
      })
    },
    /** Куда вести после login/register */
    homePath(): string {
      if (!this.user) {
        return '/login'
      }
      return this.isEmailVerified ? '/' : '/verify-email'
    },
  },
})
