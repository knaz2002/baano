<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['auth'],
})

type PhoneStatus = {
  phone_verified: boolean
  phone_masked: string
  resend_available_in: number
  debug_code: string | null
}

const auth = useAuthStore()
const { apiFetch } = useApi()

const code = ref('')
const processing = ref(false)
const success = ref('')
const error = ref('')
const phoneMasked = ref('')
const debugCode = ref<string | null>(null)
const cooldown = ref(0)
let cooldownTimer: ReturnType<typeof setInterval> | null = null

function startCooldown(seconds = 60) {
  cooldown.value = Math.max(0, Number(seconds) || 0)
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
    cooldownTimer = null
  }
  if (cooldown.value <= 0) {
    return
  }
  cooldownTimer = setInterval(() => {
    if (cooldown.value <= 1) {
      cooldown.value = 0
      if (cooldownTimer) {
        clearInterval(cooldownTimer)
        cooldownTimer = null
      }
      return
    }
    cooldown.value -= 1
  }, 1000)
}

function applyStatus(data: PhoneStatus) {
  phoneMasked.value = data.phone_masked
  debugCode.value = data.debug_code
  startCooldown(data.resend_available_in > 0 ? data.resend_available_in : 0)
}

async function loadStatus() {
  const res = await apiFetch<{ data: PhoneStatus }>('/api/phone/verification-status')
  if (res.data.phone_verified) {
    await auth.fetchUser()
    await navigateTo(auth.homePath())
    return
  }
  applyStatus(res.data)
  if (!res.data.debug_code && res.data.resend_available_in <= 0) {
    // нет активного кода — отправим сразу
    await resend(true)
  }
}

function onCodeInput(event: Event) {
  code.value = String((event.target as HTMLInputElement).value || '')
    .replace(/\D/g, '')
    .slice(0, 4)
}

async function verify() {
  if (code.value.length !== 4 || processing.value) {
    return
  }
  processing.value = true
  error.value = ''
  success.value = ''
  try {
    const res = await apiFetch<{
      ok: boolean
      message: string
      data: {
        id: number
        name: string
        email: string
        phone: string | null
        email_verified_at: string | null
        phone_verified_at: string | null
      }
    }>('/api/phone/verify', {
      method: 'POST',
      body: { code: code.value },
    })
    auth.user = res.data
    auth.loaded = true
    success.value = res.message
    await navigateTo(auth.homePath())
  } catch (e) {
    const parsed = parseApiErrors(e)
    error.value = parsed.code || parsed.form || 'Неверный код'
  } finally {
    processing.value = false
  }
}

async function resend(silent = false) {
  if (cooldown.value > 0 || processing.value) {
    return
  }
  processing.value = true
  if (!silent) {
    error.value = ''
    success.value = ''
  }
  try {
    const res = await apiFetch<{
      ok: boolean
      message: string
      data: PhoneStatus
    }>('/api/phone/verification-notification', {
      method: 'POST',
    })
    applyStatus(res.data)
    if (!silent) {
      success.value = res.message || 'Код отправлен повторно'
    }
    code.value = ''
  } catch (e) {
    const parsed = parseApiErrors(e)
    error.value = parsed.code || parsed.form || 'Ошибка отправки'
    const match = String(error.value).match(/(\d+)\s*сек/)
    if (match) {
      startCooldown(Number(match[1]))
    }
  } finally {
    processing.value = false
  }
}

onMounted(async () => {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (auth.isPhoneVerified) {
    await navigateTo(auth.homePath())
    return
  }
  try {
    await loadStatus()
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить статус подтверждения'
  }
})

onBeforeUnmount(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-baano-cream px-4">
    <div class="glass p-8 rounded-2xl w-full max-w-md">
      <h1 class="font-heading text-3xl font-bold mb-6 text-center text-baano-ink">
        Подтверждение телефона
      </h1>

      <div
        v-if="success"
        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md"
      >
        {{ success }}
      </div>

      <div
        v-if="error"
        class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md"
      >
        {{ error }}
      </div>

      <div class="mb-6 p-4 bg-[#DDE8DC] border border-[#4F8069] text-baano-green rounded-md">
        <p class="text-sm">
          Код отправлен на номер
          <span class="font-medium">{{ phoneMasked || 'ваш телефон' }}</span>
        </p>
        <p class="text-xs mt-2 text-gray-600">
          Введите 4-значный код из SMS
        </p>
      </div>

      <div
        v-if="debugCode"
        class="mb-6 p-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-md"
      >
        <p class="text-xs font-medium uppercase tracking-wide mb-1">
          Dev only
        </p>
        <p class="text-sm">
          Код для теста:
          <span class="text-2xl font-bold tracking-widest">{{ debugCode }}</span>
        </p>
      </div>

      <form @submit.prevent="verify">
        <div class="mb-6">
          <label class="block text-sm font-medium mb-2 text-baano-ink">
            Код подтверждения
          </label>
          <input
            :value="code"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="4"
            required
            placeholder="0000"
            class="w-full px-4 py-3 text-center text-3xl tracking-widest rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-baano-green"
            @input="onCodeInput"
          >
        </div>

        <button
          type="submit"
          class="btn-gradient w-full disabled:opacity-50"
          :disabled="processing || code.length !== 4"
        >
          {{ processing ? '…' : 'Подтвердить телефон' }}
        </button>

        <button
          type="button"
          class="mt-4 w-full text-center text-sm"
          :class="cooldown > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-baano-green hover:underline'"
          :disabled="processing || cooldown > 0"
          @click="resend(false)"
        >
          <template v-if="cooldown > 0">
            Отправить повторно через {{ cooldown }} сек
          </template>
          <template v-else>
            Отправить код повторно
          </template>
        </button>
      </form>
    </div>
  </div>
</template>
