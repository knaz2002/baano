<template>
    <DashboardLayout>
        <div
            class="min-h-screen pb-24 md:pb-8"
            style="background-color: #F7F3EC;"
        >
            <!-- Шапка -->
            <div class="bg-white p-4 md:p-6 shadow-sm">
                <h1
                    class="text-xl md:text-2xl font-bold"
                    style="color: #111827;"
                >
                    Избранное
                </h1>
            </div>

            <!-- Список избранного -->
            <div
                v-if="favorites.length > 0"
                class="p-3 md:p-4 space-y-3"
            >
                <div
                    v-for="favorite in favorites"
                    :key="favorite.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden"
                >
                    <div class="flex">
                        <!-- Фото -->
                        <Link
                            :href="`/listings/${favorite.favoritable.id}`"
                            class="w-24 h-24 md:w-28 md:h-28 flex-shrink-0 bg-gray-100"
                        >
                            <img
                                :src="favorite.favoritable.image || '/images/placeholder.jpg'"
                                :alt="favorite.favoritable.title"
                                class="w-full h-full object-cover"
                                @error="$event.target.src = '/images/placeholder.jpg'"
                            >
                        </Link>

                        <!-- Контент -->
                        <div
                            class="flex-1 min-w-0 p-3 md:p-4 flex items-center justify-between gap-2"
                        >
                            <Link
                                :href="`/listings/${favorite.favoritable.id}`"
                                class="flex-1 min-w-0"
                            >
                                <h3
                                    class="font-semibold text-sm md:text-base leading-snug line-clamp-2"
                                    style="color: #111827;"
                                >
                                    {{ favorite.favoritable.title }}
                                </h3>

                                <div
                                    class="text-lg md:text-xl font-bold mt-1"
                                    style="color: #fe0000;"
                                >
                                    {{ formatPrice(favorite.favoritable.price) }} ₽
                                </div>

                                <div
                                    v-if="favorite.favoritable.category"
                                    class="text-xs md:text-sm mt-1 truncate"
                                    style="color: #111827;"
                                >
                                    {{ favorite.favoritable.category.name }}
                                </div>
                            </Link>

                            <!-- Удалить из избранного -->
                            <button
                                type="button"
                                title="Удалить из избранного"
                                aria-label="Удалить из избранного"
                                class="w-10 h-10 md:w-11 md:h-11 flex items-center justify-center rounded-full hover:bg-red-50 transition-colors flex-shrink-0"
                                @click="removeFavorite(favorite.id)"
                            >
                                <svg
                                    class="w-6 h-6"
                                    style="color: #fe0000;"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пустое состояние -->
            <div
                v-else
                class="m-3 md:m-4 bg-white rounded-xl shadow-md p-8 text-center"
            >
                <svg
                    class="w-16 h-16 mx-auto mb-4 text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                </svg>

                <p
                    class="font-medium"
                    style="color: #111827;"
                >
                    У вас пока нет избранных объявлений
                </p>

                <Link
                    href="/listings"
                    class="inline-block mt-4 px-6 py-2 rounded-xl text-white font-medium text-sm"
                    style="background-color: #315C47;"
                >
                    Смотреть объявления
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    favorites: {
        type: Array,
        default: () => [],
    },
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};

const removeFavorite = (id) => {
    router.delete(`/user/favorites/${id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
