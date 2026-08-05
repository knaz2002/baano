<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['guest'],
})

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const remember = ref(false)
const processing = ref(false)
const errors = ref<Record<string, string>>({})

async function onSubmit() {
  errors.value = {}
  processing.value = true
  try {
    await auth.login(email.value, password.value, remember.value)
    await navigateTo('/')
  } catch (e) {
    errors.value = parseApiErrors(e)
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-baano-cream px-4">
    <div class="glass p-8 rounded-2xl w-full max-w-md">
      <h1 class="font-heading text-3xl font-bold mb-6 text-center text-baano-ink">
        Вход
      </h1>

      <div
        v-if="errors.form || errors.email"
        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md"
      >
        {{ errors.form || errors.email }}
      </div>

      <form class="space-y-4" @submit.prevent="onSubmit">
        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Email</label>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="username"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Пароль</label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
        </div>

        <label class="flex items-center gap-2 text-sm text-baano-ink">
          <input v-model="remember" type="checkbox">
          Запомнить меня
        </label>

        <button type="submit" class="btn-gradient w-full" :disabled="processing">
          {{ processing ? '…' : 'Войти' }}
        </button>

        <p class="mt-4 text-center text-sm text-baano-muted">
          Нет аккаунта?
          <NuxtLink to="/register" class="text-baano-green hover:underline">Зарегистрироваться</NuxtLink>
        </p>
      </form>
    </div>
  </div>
</template>
