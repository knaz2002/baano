<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['auth'],
})

const route = useRoute()
const auth = useAuthStore()
const processing = ref(false)
const message = ref('')
const error = ref('')

const justVerified = computed(() => route.query.verified === '1')

onMounted(async () => {
  await auth.fetchUser()
  if (auth.isEmailVerified) {
    // уже подтверждён — можно на главную
  }
})

async function resend() {
  processing.value = true
  message.value = ''
  error.value = ''
  try {
    const res = await auth.resendVerification()
    message.value = res.message || 'Ссылка отправлена повторно'
  } catch (e) {
    const parsed = parseApiErrors(e)
    error.value = parsed.form || parsed.email || 'Не удалось отправить письмо'
  } finally {
    processing.value = false
  }
}

async function logout() {
  await auth.logout()
  await navigateTo('/login')
}

async function goHome() {
  await auth.fetchUser()
  await navigateTo('/')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-baano-cream px-4">
    <div class="glass p-8 rounded-2xl w-full max-w-md">
      <h1 class="font-heading text-3xl font-bold mb-6 text-center text-baano-ink">
        Подтверждение email
      </h1>

      <div
        v-if="justVerified || auth.isEmailVerified"
        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md"
      >
        Email подтверждён. Можно пользоваться сервисом.
      </div>

      <div
        v-else
        class="mb-6 p-4 bg-[#DDE8DC] border border-[#4F8069] text-baano-green rounded-md"
      >
        <p class="text-sm">
          На почту
          <strong>{{ auth.user?.email }}</strong>
          отправлена ссылка для подтверждения.
        </p>
        <p class="text-xs mt-2 text-gray-600">Проверьте папки «Входящие» и «Спам».</p>
      </div>

      <div
        v-if="message"
        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md"
      >
        {{ message }}
      </div>
      <div
        v-if="error"
        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md"
      >
        {{ error }}
      </div>

      <p
        v-if="!auth.isEmailVerified"
        class="mb-6 text-center text-sm text-baano-muted"
      >
        После подтверждения email будут доступны избранное, отзывы и сообщения.
      </p>

      <template v-if="auth.isEmailVerified">
        <button type="button" class="btn-gradient w-full" @click="goHome">
          На главную
        </button>
      </template>
      <template v-else>
        <button
          type="button"
          class="btn-gradient w-full"
          :disabled="processing"
          @click="resend"
        >
          {{ processing ? '…' : 'Отправить ссылку повторно' }}
        </button>
      </template>

      <button
        type="button"
        class="mt-4 w-full text-center text-sm text-gray-600 hover:underline"
        @click="logout"
      >
        Выйти из аккаунта
      </button>
    </div>
  </div>
</template>
