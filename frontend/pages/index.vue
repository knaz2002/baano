<script setup lang="ts">
const { apiFetch } = useApi()
const auth = useAuthStore()

const health = ref<string>('…')
const loggingOut = ref(false)

onMounted(async () => {
  try {
    const res = await apiFetch<{ ok: boolean; service: string }>('/api/health')
    health.value = res.ok ? res.service : 'fail'
  } catch (e) {
    health.value = 'error'
    console.error(e)
  }

  if (!auth.loaded) {
    await auth.fetchUser()
  }
})

async function logout() {
  loggingOut.value = true
  try {
    await auth.logout()
  } finally {
    loggingOut.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <section class="glass rounded-2xl p-6 md:p-8 mb-10">
      <h1 class="font-heading text-3xl md:text-4xl font-bold text-baano-ink mb-3">
        Baano
      </h1>
      <p class="text-baano-muted mb-6">
        Nuxt-фронт в синхронизации с актуальным шаблоном (шапка, цвета, шрифты).
      </p>

      <div class="flex flex-wrap gap-3 text-sm">
        <span class="px-3 py-1 rounded-full bg-white/70 border border-baano-border">
          API: <strong>{{ health }}</strong>
        </span>
        <span class="px-3 py-1 rounded-full bg-white/70 border border-baano-border">
          Auth:
          <strong v-if="!auth.loaded">…</strong>
          <strong v-else-if="auth.user">{{ auth.user.email }}</strong>
          <strong v-else>guest</strong>
        </span>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <template v-if="auth.user">
          <button
            type="button"
            class="btn-gradient"
            :disabled="loggingOut"
            @click="logout"
          >
            {{ loggingOut ? '…' : 'Выйти' }}
          </button>
        </template>
        <template v-else>
          <NuxtLink to="/login" class="btn-gradient inline-block text-center">Войти</NuxtLink>
          <NuxtLink
            to="/register"
            class="inline-flex items-center px-6 py-3 rounded-2xl border-2 border-baano-green text-baano-green font-medium hover:bg-white/50"
          >
            Регистрация
          </NuxtLink>
        </template>
      </div>
    </section>

    <section id="categories" class="scroll-mt-28">
      <h2 class="font-heading text-2xl font-bold text-baano-ink mb-2">
        Категории
      </h2>
      <p class="text-baano-muted">
        Блок-заглушка под каталог — API категорий подключим следующим шагом.
      </p>
    </section>
  </div>
</template>
