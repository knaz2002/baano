<template>
    <AppLayout>
        <div class="container mx-auto px-6 py-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl font-bold mb-4" style="color: #3D4449;">{{ listing.title }}</h1>
                
                <!-- Галерея -->
                <div v-if="listing.images && listing.images.length > 0" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <img v-for="(image, index) in listing.images" 
                             :key="index"
                             :src="image" 
                             :alt="listing.title"
                             class="w-full h-64 object-cover rounded-lg">
                    </div>
                </div>
                
                <!-- Цена -->
                <div class="mb-6">
                    <p class="text-4xl font-bold" style="color: #B8949E;">{{ formatPrice(listing.price) }} ₽</p>
                </div>
                
                <!-- Кнопка избранного -->
                <div class="mb-6">
                    <button 
                        v-if="$page.props.auth.user && listing.user && listing.user.id !== $page.props.auth.user.id"
                        @click="toggleFavorite"
                        class="flex items-center gap-2 px-6 py-3 rounded-lg transition"
                        :class="isFavorited ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    >
                        <svg class="w-6 h-6" :fill="isFavorited ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        {{ isFavorited ? 'В избранном' : 'Добавить в избранное' }}
                    </button>
                </div>
                
                <!-- Описание -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2" style="color: #3D4449;">Описание</h2>
                    <p style="color: #5A6268;">{{ listing.description }}</p>
                </div>
                
                <!-- Категория -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2" style="color: #3D4449;">Категория</h2>
                    <p style="color: #5A6268;">{{ listing.category?.name }}</p>
                </div>
                
                <!-- Адрес -->
                <div v-if="listing.location" class="mb-6">
                    <h2 class="text-xl font-semibold mb-2" style="color: #3D4449;">Адрес</h2>
                    <p style="color: #5A6268;">{{ listing.location }}</p>
                </div>
                
                <!-- Продавец -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2" style="color: #3D4449;">Продавец</h2>
                    <p style="color: #5A6268;">{{ listing.user?.name }}</p>
                </div>
                
                <!-- Комментарии -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold mb-4" style="color: #3D4449;">Комментарии</h2>
                    
                    <!-- Форма -->
                    <div v-if="$page.props.auth.user && listing.user && listing.user.id !== $page.props.auth.user.id" class="glass p-6 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: #3D4449;">Оставить комментарий</h3>
                        
                        <form @submit.prevent="submitReview">
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Оценка</label>
                                <div class="flex space-x-2">
                                    <button 
                                        v-for="star in 5" 
                                        :key="star"
                                        type="button"
                                        @click="reviewForm.rating = star"
                                        class="text-2xl"
                                        :class="star <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300'"
                                    >
                                        ★
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Комментарий</label>
                                <textarea 
                                    v-model="reviewForm.comment" 
                                    rows="4" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md"
                                ></textarea>
                            </div>

                            <button type="submit" class="btn-gradient">
                                Отправить
                            </button>
                        </form>
                    </div>

                    <!-- Список -->
                    <div class="space-y-4">
                        <div 
                            v-for="review in reviews" 
                            :key="review.id"
                            class="glass p-4 rounded-lg"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold" style="color: #3D4449;">{{ review.user?.name }}</p>
                                    <div class="flex text-yellow-400">
                                        <span v-for="star in review.rating" :key="star">★</span>
                                    </div>
                                </div>
                            </div>
                            <p style="color: #5A6268;">{{ review.comment }}</p>
                        </div>

                        <div v-if="!reviews || reviews.length === 0" class="text-center py-8">
                            <p style="color: #8B9A9E;">Комментариев пока нет</p>
                        </div>
                    </div>
                </div>
                
                <Link href="/" class="inline-block px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    ← Назад к объявлениям
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listing: Object,
    isFavorited: Boolean,
    reviews: Array,
});

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const toggleFavorite = () => {
    router.post('/user/favorites/toggle', {
        listing_id: props.listing.id
    }, {
        preserveScroll: true,
    });
};

const submitReview = () => {
    reviewForm.post(`/listings/${props.listing.id}/reviews`, {
        onSuccess: () => {
            reviewForm.reset();
            reviewForm.rating = 5;
        }
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>
