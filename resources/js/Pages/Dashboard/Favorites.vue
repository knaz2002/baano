<template>
    <DashboardLayout>
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6" style="color: #1D1B20;">Избранное</h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div 
                    v-for="favorite in favorites" 
                    :key="favorite.id"
                    class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group border-2 relative"
                    style="border-color: #E7E0EC;"
                >
                    <Link :href="`/listings/${favorite.favoritable.id}`" class="block">
                        <div class="relative overflow-hidden">
                            <img 
                                :src="favorite.favoritable.image || '/images/placeholder.jpg'" 
                                :alt="favorite.favoritable.title"
                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        </div>

                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1" :title="favorite.favoritable.title">{{ favorite.favoritable.title }}</h3>
                            <p class="text-sm mb-2" style="color: #49454F;">{{ favorite.favoritable.category?.name }}</p>
                            <p class="text-2xl font-bold mb-2" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ formatPrice(favorite.favoritable.price) }} ₽</p>
                        </div>
                    </Link>

                    <button 
                        @click="removeFavorite(favorite.id)"
                        class="absolute bottom-4 right-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-all z-10"
                    >
                        <svg 
                            class="w-6 h-6 text-red-500" 
                            fill="currentColor" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="favorites.length === 0" class="text-center py-16">
                <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <p class="text-xl font-medium" style="color: #49454F;">У вас пока нет избранных объявлений</p>
                <a href="/listings" class="inline-block mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                    Смотреть объявления
                </a>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    favorites: { type: Array, default: () => [] }
});

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(price || 0);

const removeFavorite = (id) => {
    router.delete(`/user/favorites/${id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
