<template>
  <div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="font-heading text-2xl font-bold text-baano-ink mb-2">Каталог</h1>
    <p class="text-baano-muted">
      Страница-заглушка. Поиск из шапки уже ведёт сюда — список объявлений появится после API.
    </p>
    <p v-if="$route.query.search" class="mt-4 text-sm text-baano-ink">
      Запрос: <strong>{{ $route.query.search }}</strong>
    </p>
  </div>
</template>
