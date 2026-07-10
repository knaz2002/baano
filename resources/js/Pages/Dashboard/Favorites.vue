<template>
    <DashboardLayout active-tab="favorites">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold mb-6" style="color: #2C3E50;">Избранное</h1>
            
            <div v-if="!favorites || favorites.length === 0" class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <p class="text-gray-500">У вас пока нет избранных объявлений</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link 
                    v-for="item in favorites" 
                    :key="item.id"
                    :href="`/listings/${item.favoritable.id}`"
                    class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <div class="relative">
                        <img 
                            :src="item.favoritable.image || '/images/placeholder.jpg'" 
                            :alt="item.favoritable.title"
                            class="w-full h-48 object-cover"
                        >
                        <button 
                            @click.prevent="removeFavorite(item.id)"
                            class="absolute top-2 right-2 bg-white p-2 rounded-full shadow hover:bg-red-50 transition"
                        >
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ item.favoritable.title }}</h3>
                        <p class="text-lg font-bold" style="color: #B8949E;">{{ formatPrice(item.favoritable.price) }} ₽</p>
                        <p class="text-sm text-gray-500 mt-2">{{ item.favoritable.category?.name }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    favorites: {
        type: Array,
        default: () => []
    }
});

const removeFavorite = (id) => {
    router.delete(`/user/favorites/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Обновится автоматически
        }
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>