<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['guest'],
})

const { apiFetch } = useApi()
const email = ref('')
const processing = ref(false)
const errors = ref<Record<string, string>>({})
const success = ref('')

async function onSubmit() {
  errors.value = {}
  success.value = ''
  processing.value = true
  try {
    const res = await apiFetch<{ ok: boolean; message: string }>('/api/forgot-password', {
      method: 'POST',
      body: { email: email.value },
    })
    success.value = res.message
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
      <h1 class="font-heading text-3xl font-bold mb-2 text-center text-baano-ink">
        Восстановление пароля
      </h1>
      <p class="mb-6 text-center text-sm text-baano-muted">
        Укажите email — мы отправим ссылку для сброса пароля
      </p>

      <div
        v-if="success"
        class="mb-4 p-4 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm"
      >
        {{ success }}
      </div>

      <div
        v-else-if="errors.form || errors.email"
        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md"
      >
        {{ errors.form || errors.email }}
      </div>

      <form v-if="!success" class="space-y-4" @submit.prevent="onSubmit">
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

        <button type="submit" class="btn-gradient w-full" :disabled="processing">
          {{ processing ? '…' : 'Отправить ссылку' }}
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-baano-muted">
        <NuxtLink to="/login" class="text-baano-green hover:underline">Вернуться ко входу</NuxtLink>
      </p>
    </div>
  </div>
</template>
