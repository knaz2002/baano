<template>
    <AppLayout>
        <div class="container mx-auto px-6 py-8">
            <h1 class="text-3xl font-bold mb-6" style="color: #3D4449;">Избранное</h1>

            <div v-if="favorites && favorites.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div 
                    v-for="favorite in favorites" 
                    :key="favorite.id"
                    class="card overflow-hidden cursor-pointer hover:-translate-y-1 transition-all"
                    @click="goToListing(favorite.favoritable)"
                >
                    <img 
                        v-if="favorite.favoritable?.image"
                        :src="favorite.favoritable.image" 
                        :alt="favorite.favoritable.title"
                        class="w-full h-48 object-cover"
                    >
                    <div v-else class="w-full h-48 flex items-center justify-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <svg class="w-16 h-16" style="stroke: #8B9A9E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-1" style="color: #3D4449;">
                            {{ favorite.favoritable?.title }}
                        </h3>
                        
                        <p class="text-xs mb-2" style="color: #8B9A9E;">
                            {{ favorite.favoritable?.category?.name }}
                        </p>

                        <p class="text-xl font-bold mb-2" style="color: #B8949E;">
                            {{ formatPrice(favorite.favoritable?.price) }} ₽
                        </p>
                        
                        <button 
                            @click.stop="removeFromFavorites(favorite.id)"
                            class="mt-2 text-red-600 hover:text-red-800 text-sm"
                        >
                            Удалить из избранного
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <p class="text-lg" style="color: #8B9A9E;">У вас пока нет избранных объявлений</p>
                <Link href="/" class="btn-gradient mt-4 inline-block">
                    Смотреть объявления
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    favorites: Array,
});

const goToListing = (listing) => {
    if (listing && listing.id) {
        router.get(`/listings/${listing.id}`);
    }
};

const removeFromFavorites = (id) => {
    if (confirm('Удалить из избранного?')) {
        router.delete(`/user/favorites/${id}`);
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>
