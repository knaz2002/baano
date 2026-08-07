export default defineNuxtRouteMiddleware(async () => {
  if (import.meta.server) {
    return
  }

  const auth = useAuthStore()
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (!auth.isAuthenticated) {
    return
  }
  if (!auth.isEmailVerified) {
    return navigateTo('/verify-email')
  }
  return navigateTo('/')
})
