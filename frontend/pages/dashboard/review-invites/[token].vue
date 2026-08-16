<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

const route = useRoute()
const { apiFetch } = useApi()
const auth = useAuthStore()

const token = computed(() => String(route.params.token || ''))
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')
const listingTitle = ref('')

const form = reactive({
  rating: 0,
  comment: '',
})
const fieldErrors = reactive<Record<string, string>>({})

async function loadInvite() {
  loading.value = true
  error.value = ''
  try {
    if (!auth.loaded) {
      await auth.fetchUser()
    }
    if (!auth.isEmailVerified) {
      await navigateTo('/verify-email')
      return
    }

    const res = await apiFetch<{
      data: {
        token: string
        listing: { id: number; title: string }
        expires_at: string
      }
    }>(`/api/review-invites/${token.value}`)

    listingTitle.value = res.data.listing.title
  } catch (e: any) {
    console.error(e)
    error.value = e?.data?.message || 'Не удалось открыть запрос на отзыв'
  } finally {
    loading.value = false
  }
}

async function submit() {
  saving.value = true
  error.value = ''
  success.value = ''
  Object.keys(fieldErrors).forEach(k => delete fieldErrors[k])

  try {
    await apiFetch(`/api/review-invites/${token.value}`, {
      method: 'POST',
      body: {
        rating: form.rating,
        comment: form.comment,
      },
    })
    success.value = 'Отзыв отправлен на модерацию.'
    setTimeout(() => {
      navigateTo('/dashboard/reviews')
    }, 1200)
  } catch (e: any) {
    console.error(e)
    const data = e?.data?.errors
    if (data) {
      for (const [key, msgs] of Object.entries(data)) {
        fieldErrors[key] = Array.isArray(msgs) ? String(msgs[0]) : String(msgs)
      }
    }
    error.value = e?.data?.message || fieldErrors.invite || 'Не удалось отправить отзыв'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadInvite()
})
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>

    <div
      v-else-if="error && !listingTitle"
      class="bg-white rounded-2xl shadow-lg p-6 text-center text-red-600"
    >
      {{ error }}
    </div>

    <div v-else class="bg-white rounded-2xl shadow-lg p-5 md:p-8">
      <p class="text-sm font-medium text-baano-green mb-2">
        Отзыв к объявлению
      </p>
      <h1 class="font-heading text-2xl md:text-3xl font-bold text-baano-ink">
        {{ listingTitle }}
      </h1>
      <p class="mt-3 text-sm text-baano-muted leading-relaxed">
        Оцените взаимодействие с владельцем. Отзыв появится после модерации.
      </p>

      <form class="mt-6 space-y-6" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-semibold text-baano-ink mb-3">Ваша оценка</label>
          <div class="flex items-center gap-2">
            <button
              v-for="value in 5"
              :key="value"
              type="button"
              class="text-4xl transition-transform hover:scale-110 focus:outline-none"
              :class="value <= form.rating ? 'text-yellow-400' : 'text-gray-300'"
              @click="form.rating = value"
            >
              ★
            </button>
          </div>
          <p v-if="fieldErrors.rating" class="mt-2 text-sm text-red-600">
            {{ fieldErrors.rating }}
          </p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-semibold text-baano-ink">Комментарий</label>
            <span class="text-xs text-gray-400">{{ form.comment.length }}/1000</span>
          </div>
          <textarea
            v-model="form.comment"
            rows="6"
            maxlength="1000"
            required
            placeholder="Расскажите о вашем опыте…"
            class="w-full rounded-xl border-2 border-baano-border px-4 py-3 focus:outline-none"
          />
          <p v-if="fieldErrors.comment" class="mt-2 text-sm text-red-600">
            {{ fieldErrors.comment }}
          </p>
        </div>

        <p v-if="error" class="text-sm text-red-600">
          {{ error }}
        </p>
        <p v-if="success" class="text-sm text-baano-green">
          {{ success }}
        </p>

        <button
          type="submit"
          class="w-full rounded-xl px-5 py-3 text-white font-semibold bg-baano-green disabled:opacity-50"
          :disabled="saving || form.rating < 1 || !form.comment.trim()"
        >
          {{ saving ? 'Отправка…' : 'Отправить отзыв' }}
        </button>
      </form>
    </div>
  </div>
</template>
