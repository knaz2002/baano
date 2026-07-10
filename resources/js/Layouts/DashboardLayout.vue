<template>
    <div class="min-h-screen" style="background-color: #F5F5F5;">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-12 gap-6">
                <!-- Левый сайдбар навигации -->
                <div class="col-span-3">
                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <!-- Личная информация -->
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                    {{ userInitial }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ displayName }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ userEmail }}</p>
                                </div>
                            </div>
                            <Link href="/profile/edit" class="btn-gradient w-full py-2 px-4 rounded-lg text-sm text-center block">
                                Редактировать
                            </Link>
                        </div>

                        <!-- Навигация -->
                        <nav class="space-y-1">
                            <Link 
                                href="/" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'home' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Главная
                            </Link>

                            <Link 
                                href="/dashboard" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'profile' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Личная информация
                            </Link>

                            <Link 
                                href="/dashboard/listings" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'listings' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Мои объявления
                            </Link>

                            <Link 
                                href="/dashboard/favorites" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'favorites' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                Избранное
                            </Link>

                            <Link 
                                href="/dashboard/messages" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'messages' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                Сообщения
                            </Link>

                            <Link 
                                href="/dashboard/reviews" 
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors"
                                :class="activeTab === 'reviews' ? 'bg-purple-50 text-purple-600' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                Отзывы
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Основной контент -->
                <div class="col-span-9">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeTab: {
        type: String,
        default: 'profile'
    }
});

const page = usePage();

// Получаем данные пользователя
const userName = computed(() => {
    return page.props.auth?.user?.name || '';
});

const userEmail = computed(() => {
    return page.props.auth?.user?.email || '';
});

// Первая буква имени или 'П'
const userInitial = computed(() => {
    const name = userName.value;
    return name ? name.charAt(0).toUpperCase() : 'П';
});

// Отображаемое имя
const displayName = computed(() => {
    const name = userName.value;
    return name || 'Пользователь';
});
</script>
