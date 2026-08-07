<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

type CategoryNode = {
  id: number
  name: string
  children?: CategoryNode[]
}

type ListingImage = {
  id: number
  url: string
}

type ListingDetail = {
  id: number
  title: string
  description: string
  price: number | string
  price_type: string
  location: string | null
  city: string | null
  category_id: number
  attributes: Record<string, unknown>
  status: string
  is_active: boolean
  requested_is_active: boolean
  images: ListingImage[]
}

const route = useRoute()
const { apiFetch } = useApi()
const auth = useAuthStore()

const categories = ref<CategoryNode[]>([])
const existingImages = ref<ListingImage[]>([])
const removedMediaIds = ref<number[]>([])
const imageFiles = ref<File[]>([])
const imagePreviews = ref<string[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')

const form = reactive({
  category_id: '' as string | number,
  title: '',
  description: '',
  price: '' as string | number,
  price_type: 'fixed',
  location: '',
  city: '',
})

function hasChildren(cat: CategoryNode) {
  return Array.isArray(cat.children) && cat.children.length > 0
}

function onImagesChange(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || []).slice(0, 10)
  imageFiles.value = files
  imagePreviews.value.forEach(url => URL.revokeObjectURL(url))
  imagePreviews.value = files.map(f => URL.createObjectURL(f))
}

function removeExisting(id: number) {
  removedMediaIds.value.push(id)
  existingImages.value = existingImages.value.filter(img => img.id !== id)
}

async function load() {
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

    const id = route.params.id
    const [catsRes, listingRes] = await Promise.all([
      apiFetch<{ data: CategoryNode[] }>('/api/categories'),
      apiFetch<{ data: ListingDetail }>(`/api/my/listings/${id}`),
    ])
    categories.value = catsRes.data
    const listing = listingRes.data
    form.category_id = listing.category_id
    form.title = listing.title
    form.description = listing.description
    form.price = listing.price
    form.price_type = listing.price_type
    form.location = listing.location || ''
    form.city = listing.city || ''
    existingImages.value = listing.images || []
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить объявление'
  } finally {
    loading.value = false
  }
}

async function submit() {
  saving.value = true
  error.value = ''

  const remaining = existingImages.value.length + imageFiles.value.length
  if (remaining < 1) {
    error.value = 'Нужно хотя бы одно фото'
    saving.value = false
    return
  }

  try {
    const body = new FormData()
    body.append('category_id', String(form.category_id))
    body.append('title', form.title)
    body.append('description', form.description)
    body.append('price', String(form.price))
    body.append('price_type', form.price_type)
    body.append('location', form.location)
    body.append('city', form.city)
    body.append('attributes', JSON.stringify({}))
    body.append('removed_media_ids', JSON.stringify(removedMediaIds.value))
    imageFiles.value.forEach((file) => {
      body.append('images[]', file)
    })

    await apiFetch(`/api/my/listings/${route.params.id}`, {
      method: 'POST',
      body,
    })
    await navigateTo('/dashboard/listings')
  } catch (e: any) {
    console.error(e)
    error.value = e?.data?.message || 'Не удалось сохранить'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  load()
})
</script>

<template>
  <div class="max-w-3xl mx-auto">
    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>

    <div v-else class="bg-white rounded-2xl shadow-lg p-6">
      <h1 class="font-heading text-2xl font-bold text-baano-ink mb-6">
        Редактировать объявление
      </h1>

      <form class="space-y-6" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Категория</label>
          <select
            v-model="form.category_id"
            required
            class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
          >
            <option value="">
              Выберите категорию
            </option>
            <template v-for="cat in categories" :key="cat.id">
              <option :value="cat.id" :disabled="hasChildren(cat)">
                {{ cat.name }}
              </option>
              <template v-if="hasChildren(cat)">
                <template v-for="child in cat.children" :key="child.id">
                  <option :value="child.id" :disabled="hasChildren(child)">
                    — {{ child.name }}
                  </option>
                  <option
                    v-for="grandchild in (child.children || [])"
                    :key="grandchild.id"
                    :value="grandchild.id"
                  >
                    &nbsp;&nbsp;&nbsp;&nbsp;— {{ grandchild.name }}
                  </option>
                </template>
              </template>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Заголовок</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Описание</label>
          <textarea
            v-model="form.description"
            rows="6"
            required
            class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-baano-muted mb-2">Цена</label>
            <input
              v-model="form.price"
              type="number"
              min="0"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-baano-muted mb-2">Тип цены</label>
            <select
              v-model="form.price_type"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
            >
              <option value="fixed">
                Фиксированная
              </option>
              <option value="hourly">
                За час
              </option>
              <option value="daily">
                За день
              </option>
              <option value="monthly">
                За месяц
              </option>
              <option value="negotiable">
                Договорная
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Адрес / локация</label>
          <input
            v-model="form.location"
            type="text"
            class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Город</label>
          <input
            v-model="form.city"
            type="text"
            class="w-full px-4 py-3 rounded-xl border-2 border-baano-border focus:outline-none"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Текущие фото</label>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="img in existingImages"
              :key="img.id"
              class="relative"
            >
              <img :src="img.url" class="w-20 h-20 object-cover rounded-lg">
              <button
                type="button"
                class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-600 text-white text-xs"
                @click="removeExisting(img.id)"
              >
                ×
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-baano-muted mb-2">Добавить фото</label>
          <input
            type="file"
            accept="image/*"
            multiple
            class="w-full text-sm"
            @change="onImagesChange"
          >
          <div v-if="imagePreviews.length" class="mt-3 flex flex-wrap gap-2">
            <img
              v-for="(src, i) in imagePreviews"
              :key="i"
              :src="src"
              class="w-20 h-20 object-cover rounded-lg"
            >
          </div>
        </div>

        <p v-if="error" class="text-red-600 text-sm">
          {{ error }}
        </p>

        <div class="flex gap-3">
          <button
            type="submit"
            class="px-6 py-3 rounded-xl text-white font-medium bg-baano-green disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'Сохранение…' : 'Сохранить' }}
          </button>
          <NuxtLink
            to="/dashboard/listings"
            class="px-6 py-3 rounded-xl border border-gray-300 hover:bg-gray-50"
          >
            Отмена
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>
