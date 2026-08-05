<script setup lang="ts">
const config = useRuntimeConfig()
const { apiFetch } = useApi()
const auth = useAuthStore()

const health = ref<string>('…')

onMounted(async () => {
  try {
    const res = await apiFetch<{ ok: boolean; service: string }>('/api/health')
    health.value = res.ok ? res.service : 'fail'
  } catch (e) {
    health.value = 'error'
    console.error(e)
  }

  await auth.fetchUser()
})
</script>

<template>
  <div style="font-family: system-ui; padding: 2rem; max-width: 40rem">
    <h1>Baano</h1>
    <p>Nuxt frontend scaffold</p>
    <p>
      API base: <code>{{ config.public.apiBase }}</code>
    </p>
    <p>
      Health: <strong>{{ health }}</strong>
    </p>
    <p>
      Auth:
      <strong v-if="!auth.loaded">…</strong>
      <strong v-else-if="auth.user">{{ auth.user.email }}</strong>
      <strong v-else>guest</strong>
    </p>
  </div>
</template>
