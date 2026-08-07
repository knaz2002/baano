type ToggleResponse = {
  data: {
    is_favorited: boolean
    listing_id: number
  }
  message?: string
}

/**
 * Toggle избранного + редиректы на login / verify-email.
 */
export function useFavorites() {
  const auth = useAuthStore()
  const { apiFetch } = useApi()

  async function ensureCanFavorite(): Promise<boolean> {
    if (!auth.loaded) {
      await auth.fetchUser()
    }
    if (!auth.isAuthenticated) {
      await navigateTo('/login')
      return false
    }
    if (!auth.isEmailVerified) {
      await navigateTo('/verify-email')
      return false
    }
    return true
  }

  async function toggleFavorite(listingId: number): Promise<boolean | null> {
    if (! (await ensureCanFavorite())) {
      return null
    }

    const res = await apiFetch<ToggleResponse>('/api/favorites/toggle', {
      method: 'POST',
      body: { listing_id: listingId },
    })

    return res.data.is_favorited
  }

  return {
    toggleFavorite,
    ensureCanFavorite,
  }
}
