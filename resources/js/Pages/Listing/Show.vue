<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-6">
                <!-- Основная часть -->
                <div class="min-w-0">
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
                        
                        <!-- Кнопки действий -->
                        <div v-if="$page.props.auth.user && listing.user && listing.user.id !== $page.props.auth.user.id" class="flex gap-3 mt-4">
                            <!-- Кнопка "Продолжить диалог" - скрыта когда чат открыт -->
                            <button 
                                v-if="!showChatSidebar"
                                @click="openChat"
                                class="flex-1 btn-gradient py-2 px-4 rounded-lg text-sm font-medium flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                {{ hasConversation ? 'Продолжить диалог' : 'Написать сообщение' }}
                            </button>
                            
                            <!-- Кнопка "Оставить комментарий" - единый стиль btn-gradient -->
                            <button 
                                @click="showCommentModal = true"
                                class="flex-1 btn-gradient py-2 px-4 rounded-lg text-sm font-medium flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                Оставить комментарий
                            </button>
                        </div>
                    </div>
                    
                    <!-- Комментарии (список) -->
                    <div class="mt-8">
                        <h2 class="text-2xl font-bold mb-4" style="color: #3D4449;">Комментарии</h2>
                        
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
                    
                    <Link href="/" class="inline-block px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 mt-6">
                        ← Назад к объявлениям
                    </Link>
                </div>

                <!-- Правый сайдбар с чатом (внутри контейнера) -->
                <div v-if="showChatSidebar" class="xl:sticky xl:top-24 xl:self-start">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col" style="height: calc(100vh - 150px); max-height: 700px;">
                        <!-- Шапка чата -->
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4 flex items-center justify-between flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white bg-opacity-30 flex items-center justify-center font-bold">
                                    {{ listing.user?.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold truncate">{{ listing.user?.name }}</h3>
                                    <p class="text-xs opacity-75">Продавец</p>
                                </div>
                            </div>
                            <button @click="showChatSidebar = false" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Сообщения -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                            <div
                                v-for="msg in chatMessages"
                                :key="msg.id"
                                class="flex"
                                :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                            >
                                <div
                                    class="max-w-[85%] px-3 py-2 rounded-2xl text-sm"
                                    :class="msg.is_mine
                                        ? 'bg-blue-500 text-white rounded-br-sm'
                                        : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                                >
                                    <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                    <p class="text-xs mt-1 opacity-75">{{ msg.created_at }}</p>
                                </div>
                            </div>

                            <div v-if="chatMessages.length === 0" class="text-center text-gray-500 py-8">
                                <p>Начните диалог</p>
                                <p class="text-sm mt-2">Напишите первое сообщение</p>
                            </div>
                        </div>

                        <!-- Форма отправки -->
                        <form @submit.prevent="sendMessage" class="p-4 border-t bg-white flex-shrink-0">
                            <div class="flex gap-2">
                                <input
                                    v-model="messageText"
                                    type="text"
                                    placeholder="Напишите сообщение..."
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-full focus:outline-none focus:border-purple-500 text-sm min-w-0"
                                    required
                                    maxlength="1000"
                                >
                                <button
                                    type="submit"
                                    class="btn-gradient px-4 py-2 rounded-full flex-shrink-0"
                                    :disabled="sendingMessage"
                                >
                                    <svg v-if="!sendingMessage" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для комментария -->
        <div v-if="showCommentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showCommentModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl" @click.stop>
                <h3 class="text-xl font-bold mb-4" style="color: #2C3E50;">Оставить комментарий</h3>
                <form @submit.prevent="submitReview">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Оценка</label>
                        <div class="flex gap-2">
                            <button 
                                v-for="star in 5" 
                                :key="star"
                                type="button"
                                @click="reviewForm.rating = star"
                                class="text-2xl transition-colors"
                                :class="star <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300'"
                            >
                                ★
                            </button>
                        </div>
                    </div>
                    
                    <textarea 
                        v-model="reviewForm.comment"
                        placeholder="Ваш комментарий..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 mb-4"
                        rows="4"
                        required
                        maxlength="1000"
                    ></textarea>
                    
                    <div class="flex gap-3">
                        <button 
                            type="button"
                            @click="showCommentModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 btn-gradient py-2 px-4 rounded-lg"
                            :disabled="reviewForm.processing"
                        >
                            {{ reviewForm.processing ? 'Отправка...' : 'Отправить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listing: Object,
    isFavorited: Boolean,
    reviews: Array,
    conversation: Object,
    chatMessages: Array,
});

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const showChatSidebar = ref(false);
const showCommentModal = ref(false);
const messageText = ref('');
const sendingMessage = ref(false);
const messagesContainer = ref(null);
const hasConversation = ref(!!props.conversation);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

watch(() => props.chatMessages, () => {
    if (showChatSidebar.value) {
        scrollToBottom();
    }
}, { deep: true });

const openChat = () => {
    showChatSidebar.value = true;
    nextTick(() => {
        scrollToBottom();
    });
};

const toggleFavorite = () => {
    router.post('/user/favorites/toggle', {
        listing_id: props.listing.id
    }, {
        preserveScroll: true,
    });
};

const sendMessage = () => {
    if (!messageText.value.trim() || sendingMessage.value) return;
    
    sendingMessage.value = true;
    
    router.post(`/message-user/${props.listing.user_id}`, {
        body: messageText.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            messageText.value = '';
            hasConversation.value = true;
            if (page.props.chatMessages) {
                props.chatMessages = page.props.chatMessages;
            }
            scrollToBottom();
        },
        onError: (errors) => {
            console.error('Ошибка:', errors);
        },
        onFinish: () => {
            sendingMessage.value = false;
        },
    });
};

const submitReview = () => {
    reviewForm.post(`/listings/${props.listing.id}/reviews`, {
        preserveScroll: true,
        onSuccess: () => {
            showCommentModal.value = false;
            reviewForm.reset();
            reviewForm.rating = 5;
        }
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};
</script>