<template>
    <div class="min-h-screen" style="background-color: #E8E6E1;">
        <div class="max-w-7xl mx-auto px-4 py-4 md:py-8">
            <!-- Мобильная кнопка меню -->
            <button 
                @click="showSidebar = !showSidebar"
                class="md:hidden mb-4 px-4 py-2 rounded-xl bg-white shadow flex items-center gap-2"
            >
                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span style="color: #1D1B20;">Меню</span>
            </button>

            <div class="flex gap-4 md:gap-6">
                <!-- Sidebar - скрыт на мобильных по умолчанию -->
                <aside 
                    class="fixed md:static inset-0 z-40 transform transition-transform duration-300 md:transform-none md:w-64 md:flex-shrink-0"
                    :class="showSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
                >
                    <!-- Оверлей для мобильных -->
                    <div v-if="showSidebar" class="md:hidden fixed inset-0 bg-black/50 z-40" @click="showSidebar = false"></div>
                    
                    <div class="md:relative z-50 bg-white rounded-2xl shadow-lg p-4 md:p-6 h-full md:h-auto overflow-y-auto">
                        <!-- Профиль -->
                        <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b" style="border-color: #E7E0EC;">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                    {{ userInitial }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm md:text-base truncate" style="color: #1D1B20;">{{ auth.user.name }}</h3>
                                    <p class="text-xs md:text-sm truncate" style="color: #49454F;">{{ auth.user.email }}</p>
                                </div>
                            </div>
                            <Link href="/profile/edit" class="block w-full text-center py-2 md:py-2.5 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                Редактировать
                            </Link>
                        </div>

                        <!-- Навигация -->
                        <nav class="space-y-1 md:space-y-2">
                            <Link href="/" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors hover:bg-gray-50" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span class="text-sm md:text-base">Главная</span>
                            </Link>

                            <Link href="/dashboard" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors" :class="isActive('/dashboard') ? 'bg-purple-50' : 'hover:bg-gray-50'" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-sm md:text-base">Личная информация</span>
                            </Link>

                            <Link href="/user/listings" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors" :class="isActive('/user/listings') ? 'bg-purple-50' : 'hover:bg-gray-50'" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="text-sm md:text-base">Мои объявления</span>
                            </Link>

                            <Link href="/user/favorites" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors" :class="isActive('/user/favorites') ? 'bg-purple-50' : 'hover:bg-gray-50'" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="text-sm md:text-base">Избранное</span>
                            </Link>

                            <Link href="/dashboard/messages" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors" :class="isActive('/dashboard/messages') ? 'bg-purple-50' : 'hover:bg-gray-50'" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <span class="text-sm md:text-base">Сообщения</span>
                            </Link>

                            <Link href="/dashboard/reviews" class="flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors" :class="isActive('/dashboard/reviews') ? 'bg-purple-50' : 'hover:bg-gray-50'" style="color: #1D1B20;">
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <span class="text-sm md:text-base">Отзывы</span>
                            </Link>

                            <!-- Выход -->
                            <div class="pt-4 mt-4 border-t" style="border-color: #E7E0EC;">
                                <button @click="logout" class="w-full flex items-center gap-3 px-3 md:px-4 py-2 md:py-3 rounded-xl transition-colors hover:bg-red-50 text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="text-sm md:text-base">Выход</span>
                                </button>
                            </div>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 min-w-0">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showSidebar = ref(false);
const auth = computed(() => usePage().props.auth);
const userInitial = computed(() => auth.value.user?.name?.charAt(0).toUpperCase() || 'A');

const isActive = (path) => {
    return usePage().url.startsWith(path);
};

const logout = () => {
    if (confirm('Вы уверены, что хотите выйти?')) {
        router.post('/logout');
    }
};
</script>
