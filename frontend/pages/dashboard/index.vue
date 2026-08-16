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
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6" style="color: #1F4234;">
      Личная информация
    </h1>

    <div v-if="!auth.user" class="py-12 text-center" style="color: #68736B;">
      Загрузка…
    </div>

    <div v-else class="bg-white rounded-2xl shadow-lg p-6">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2" style="color: #68736B;">Имя</label>
          <input
            type="text"
            :value="auth.user.name"
            disabled
            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
            style="border-color: #E8E3DA;"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" style="color: #68736B;">Email</label>
          <input
            type="email"
            :value="auth.user.email"
            disabled
            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
            style="border-color: #E8E3DA;"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2" style="color: #68736B;">Телефон</label>
          <input
            type="tel"
            :value="auth.user.phone || 'Не указан'"
            disabled
            class="w-full px-4 py-3 rounded-xl border-2 bg-gray-50 focus:outline-none"
            style="border-color: #E8E3DA;"
          >
        </div>

        <div class="pt-4">
          <NuxtLink
            to="/dashboard/profile"
            class="inline-block px-6 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg"
            style="background-color: #315C47;"
          >
            Редактировать профиль
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
