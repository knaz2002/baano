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
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6" style="color: #1F4234;">
      Отзывы
    </h1>

    <div v-if="loading" class="py-16 text-center" style="color: #68736B;">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <div
      v-else-if="reviews.length === 0"
      class="bg-white rounded-2xl shadow-lg p-8 text-center"
    >
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
      </svg>
      <p class="text-lg font-medium text-gray-700">
        У вас пока нет отзывов
      </p>
      <p class="text-gray-500 mt-2">
        Отзывы появятся, когда другие пользователи оценят вашу работу
      </p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="review in reviews"
        :key="review.id"
        class="bg-white rounded-2xl shadow-lg p-6"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-base" style="color: #1F4234;">
              {{ review.listing?.title || 'Объявление' }}
            </h3>
            <p class="text-sm text-gray-500">
              {{ review.user?.name }}
            </p>
          </div>
          <div class="flex items-center">
            <div class="flex text-yellow-400 mr-2">
              <span
                v-for="n in 5"
                :key="n"
                :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
              >★</span>
            </div>
            <span class="text-sm font-bold" style="color: #315C47;">{{ review.rating }}/5</span>
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
