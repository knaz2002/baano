<template>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all">
        <!-- Изображение -->
        <div class="relative">
            <img 
                :src="listing.image || '/images/placeholder.jpg'" 
                :alt="listing.title"
                class="w-full h-40 object-cover"
            >
            <button 
                @click.prevent="toggleFavorite"
                class="absolute top-2 right-2 bg-white p-1.5 rounded-full shadow-md"
            >
                <svg v-if="isFavorited" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </div>

        <!-- Контент -->
        <div class="p-4">
            <!-- Заголовок -->
            <h3 class="font-bold text-base text-gray-900 mb-1 line-clamp-1">{{ listing.title }}</h3>
            
            <!-- Описание -->
            <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ listing.description || 'Описание услуги' }}</p>
            
            <!-- Цена -->
            <div class="mb-3">
                <span class="text-xl font-bold" style="color: #4A3B8F;">{{ formatPrice(listing.price) }} ₽</span>
            </div>

            <!-- Исполнитель -->
            <div v-if="listing.user" class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ getUserInitial(listing.user.name) }}
                </div>
                <span class="text-sm text-gray-700">{{ listing.user.name }}</span>
            </div>
        </div>

        <!-- Кнопки (иконки) -->
        <div class="px-4 pb-4 flex gap-2">
            <a 
                v-if="listing.user?.phone"
                :href="`tel:${listing.user.phone}`"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-white font-medium transition-all active:scale-95"
                style="background: linear-gradient(135deg, #6B5CE7 0%, #A78BFA 100%);"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
            
            <Link 
                :href="`/listings/${listing.id}`"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-white font-medium transition-all active:scale-95"
                style="background: linear-gradient(135deg, #6B5CE7 0%, #A78BFA 100%);"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </Link>
            
            <button 
                @click="toggleFavorite"
                class="flex items-center justify-center p-2.5 rounded-xl border-2 border-purple-200 text-purple-600 transition-all active:scale-95"
            >
                <svg v-if="isFavorited" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    listing: {
        type: Object,
        required: true
    },
    isFavorited: {
        type: Boolean,
        default: false
    }
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};

const getUserInitial = (name) => {
    if (!name) return '?';
    return name.charAt(0).toUpperCase();
};

const toggleFavorite = () => {
    router.post('/user/favorites/toggle', { 
        listing_id: props.listing.id 
    }, {
        preserveScroll: true,
    });
};
</script>
