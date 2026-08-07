<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

const auth = useAuthStore()

onMounted(async () => {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
})
</script>

<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="font-heading text-2xl font-bold text-baano-ink mb-6">
      Личная информация
    </h1>

    <div v-if="!auth.user" class="py-12 text-center text-baano-muted">
      Загрузка…
    </div>

    <div v-else class="bg-white rounded-2xl shadow-lg p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-baano-muted mb-2">Имя</label>
        <input
          type="text"
          :value="auth.user.name"
          disabled
          class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 border-baano-border"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-baano-muted mb-2">Email</label>
        <input
          type="email"
          :value="auth.user.email"
          disabled
          class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 border-baano-border"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-baano-muted mb-2">Телефон</label>
        <input
          type="tel"
          :value="auth.user.phone || 'Не указан'"
          disabled
          class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 border-baano-border"
        >
      </div>

      <div class="pt-4">
        <NuxtLink
          to="/dashboard/profile"
          class="inline-block px-6 py-3 rounded-xl text-white font-medium bg-baano-green hover:shadow-lg transition-all"
        >
          Редактировать профиль
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
