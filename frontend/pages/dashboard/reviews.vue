<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

type ReviewItem = {
  id: number
  rating: number
  comment: string | null
  created_at: string | null
  listing: { id: number; title: string } | null
  user: { id: number; name: string } | null
}

const { apiFetch } = useApi()
const auth = useAuthStore()

const reviews = ref<ReviewItem[]>([])
const loading = ref(true)
const error = ref('')

function formatDate(dateStr: string | null) {
  if (!dateStr) {
    return ''
  }
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

async function loadReviews() {
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
    const res = await apiFetch<{ data: ReviewItem[] }>('/api/my/reviews')
    reviews.value = res.data
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить отзывы'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadReviews()
})
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="font-heading text-2xl font-bold text-baano-ink mb-6">
      Отзывы
    </h1>

    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <div
      v-else-if="reviews.length === 0"
      class="bg-white rounded-2xl shadow-lg p-8 text-center"
    >
      <p class="text-lg font-medium text-gray-700">
        У вас пока нет отзывов
      </p>
      <p class="text-gray-500 mt-2">
        Отзывы появятся после сделок и оценок
      </p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="review in reviews"
        :key="review.id"
        class="bg-white rounded-2xl shadow-lg p-6"
      >
        <div class="flex items-start justify-between mb-3 gap-3">
          <div>
            <h3 class="font-semibold text-base text-baano-ink">
              {{ review.listing?.title || 'Объявление' }}
            </h3>
            <p class="text-sm text-gray-500">
              {{ review.user?.name }}
            </p>
          </div>
          <div class="flex items-center">
            <div class="flex mr-2">
              <span
                v-for="n in 5"
                :key="n"
                :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
              >★</span>
            </div>
            <span class="text-sm font-bold text-baano-green">{{ review.rating }}/5</span>
          </div>
        </div>
        <p class="text-gray-700 mb-3">
          {{ review.comment }}
        </p>
        <p class="text-xs text-gray-400">
          {{ formatDate(review.created_at) }}
        </p>
      </div>
    </div>
  </div>
</template>
