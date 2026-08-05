<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['guest'],
})

const auth = useAuthStore()
const form = reactive({
  name: '',
  phone: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const processing = ref(false)
const errors = ref<Record<string, string>>({})

function onPhoneInput(e: Event) {
  form.phone = formatRuPhone((e.target as HTMLInputElement).value)
}

async function onSubmit() {
  errors.value = {}
  processing.value = true
  try {
    await auth.register({ ...form })
    await navigateTo(auth.homePath())
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
        Регистрация
      </h1>

      <div
        v-if="errors.form"
        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md"
      >
        {{ errors.form }}
      </div>

      <form class="space-y-4" autocomplete="off" @submit.prevent="onSubmit">
        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Имя</label>
          <input
            v-model="form.name"
            type="text"
            required
            autocomplete="off"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Телефон</label>
          <input
            :value="form.phone"
            type="tel"
            required
            placeholder="+7 (___) ___-__-__"
            autocomplete="off"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
            @input="onPhoneInput"
          >
          <p v-if="errors.phone" class="mt-1 text-sm text-red-600 font-semibold">{{ errors.phone }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            autocomplete="off"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
          <p v-if="errors.email" class="mt-1 text-sm text-red-600 font-semibold">{{ errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Пароль</label>
          <input
            v-model="form.password"
            type="password"
            required
            autocomplete="new-password"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
          <p v-if="errors.password" class="mt-1 text-sm text-red-600 font-semibold">{{ errors.password }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 text-baano-ink">Подтверждение пароля</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
          >
        </div>

        <button type="submit" class="btn-gradient w-full" :disabled="processing">
          {{ processing ? '…' : 'Зарегистрироваться' }}
        </button>

        <p class="mt-4 text-center text-sm text-baano-muted">
          Уже есть аккаунт?
          <NuxtLink to="/login" class="text-baano-green hover:underline">Войти</NuxtLink>
        </p>
      </form>
    </div>
  </div>
</template>
