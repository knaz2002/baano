<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

const auth = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  phone: '',
})
const errors = reactive<Record<string, string>>({})
const saving = ref(false)
const success = ref('')

onMounted(async () => {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (auth.user) {
    form.name = auth.user.name
    form.email = auth.user.email
    form.phone = auth.user.phone || ''
  }
})

async function submit() {
  saving.value = true
  success.value = ''
  Object.keys(errors).forEach(k => delete errors[k])

  try {
    await auth.updateProfile({
      name: form.name,
      email: form.email,
      phone: form.phone || null,
    })
    success.value = 'Профиль обновлён'
  } catch (e: any) {
    const data = e?.data?.errors
    if (data) {
      for (const [key, msgs] of Object.entries(data)) {
        errors[key] = Array.isArray(msgs) ? String(msgs[0]) : String(msgs)
      }
    } else {
      errors.form = 'Не удалось сохранить профиль'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h1 class="font-heading text-2xl font-bold text-baano-ink mb-6">
        Редактировать профиль
      </h1>

      <form class="space-y-6" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-baano-green"
          >
          <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-baano-green"
          >
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
          <input
            v-model="form.phone"
            type="tel"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-baano-green"
          >
          <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
        </div>

        <p v-if="errors.form" class="text-red-500 text-sm">{{ errors.form }}</p>
        <p v-if="success" class="text-baano-green text-sm">{{ success }}</p>

        <div class="pt-4 flex gap-3">
          <button
            type="submit"
            class="px-6 py-2 rounded-lg text-white bg-baano-green disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'Сохранение…' : 'Сохранить' }}
          </button>
          <NuxtLink
            to="/dashboard"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Отмена
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>
