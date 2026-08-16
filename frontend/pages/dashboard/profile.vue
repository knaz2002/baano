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
  <div class="max-w-3xl">
    <h1 class="text-3xl font-bold mb-8" style="color: #1F4234;">
      Личная информация
    </h1>

    <div class="bg-white rounded-2xl shadow-lg p-8">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Имя</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors"
              style="border-color: #E8E3DA; color: #1F4234;"
            >
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
              {{ errors.name }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors"
              style="border-color: #E8E3DA; color: #1F4234;"
            >
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">
              {{ errors.email }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Телефон</label>
            <input
              v-model="form.phone"
              type="tel"
              class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none transition-colors"
              style="border-color: #E8E3DA; color: #1F4234;"
            >
            <p v-if="errors.phone" class="text-red-500 text-sm mt-1">
              {{ errors.phone }}
            </p>
          </div>

          <p v-if="errors.form" class="text-red-500 text-sm">
            {{ errors.form }}
          </p>
          <p v-if="success" class="text-sm" style="color: #315C47;">
            {{ success }}
          </p>

          <div class="pt-4">
            <button
              type="submit"
              class="px-8 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg disabled:opacity-50"
              style="background-color: #315C47;"
              :disabled="saving"
            >
              {{ saving ? 'Сохранение…' : 'Редактировать профиль' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
