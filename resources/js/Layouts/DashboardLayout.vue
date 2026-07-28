<template>
    <div class="min-h-screen pb-20 md:pb-0" style="background-color: #E8E6E1;">
        <!-- Ограниченный контейнер для всего ЛК -->
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row min-h-screen">
                
                <!-- Боковая панель (Десктоп) -->
                <aside class="hidden md:flex md:flex-col w-64 bg-white border-r flex-shrink-0" style="border-color: #E7E0EC;">
                    <div class="p-6 border-b" style="border-color: #E7E0EC;">
                        <Link href="/" class="text-2xl font-bold" style="color: #6750A4;">
                            Baano
                        </Link>
                    </div>
                    
                    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                        <!-- Главная -->
                        <Link href="/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Главная</span>
                        </Link>

                        <!-- Личная информация -->
                        <Link href="/profile/edit" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50" :class="isActive('/profile') ? 'bg-purple-50' : ''">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Личная информация</span>
                        </Link>

                        <!-- Сообщения -->
                        <Link href="/messages" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50" :class="isActive('/messages') ? 'bg-purple-50' : ''">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Сообщения</span>
                        </Link>

                        <!-- Мои объявления -->
                        <Link href="/user/listings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50" :class="isActive('/user/listings') ? 'bg-purple-50' : ''">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Мои объявления</span>
                        </Link>

                        <!-- Избранное -->
                        <Link href="/user/favorites" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50" :class="isActive('/user/favorites') ? 'bg-purple-50' : ''">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Избранное</span>
                        </Link>

                        <!-- Отзывы -->
                        <Link href="/dashboard/reviews" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-purple-50" :class="isActive('/dashboard/reviews') ? 'bg-purple-50' : ''">
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #1D1B20;">Отзывы</span>
                        </Link>
                    </nav>

                    <!-- Выход -->
                    <div class="p-4 border-t" style="border-color: #E7E0EC;">
                        <Link href="/logout" method="post" as="button" class="flex items-center gap-3 px-4 py-3 w-full rounded-xl transition-all hover:bg-red-50 text-left">
                            <svg class="w-5 h-5" style="color: #B3261E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="text-sm font-medium" style="color: #B3261E;">Выйти</span>
                        </Link>
                    </div>
                </aside>

                <!-- Основной контент -->
                <main class="flex-1 flex flex-col">
                    <!-- Мобильная шапка -->
                    <header class="md:hidden bg-white border-b p-4 flex items-center justify-between sticky top-0 z-20" style="border-color: #E7E0EC;">
                        <Link href="/" class="text-xl font-bold" style="color: #6750A4;">Baano</Link>
                        <Link href="/logout" method="post" as="button" class="text-sm font-medium" style="color: #B3261E;">Выйти</Link>
                    </header>

                    <!-- Контент страницы -->
                    <div class="flex-1 p-4 md:p-6 lg:p-8">
                        <slot />
                    </div>
                </main>
            </div>
        </div>

        <!-- === МОБИЛЬНАЯ НИЖНЯЯ НАВИГАЦИЯ (всегда видна) === -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50" style="border-color: #E7E0EC;">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-around items-center py-2">
                    <Link href="/" class="flex flex-col items-center gap-1 p-2 rounded-lg transition-colors" :class="isActive('/') ? 'text-purple-600' : 'text-gray-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-xs">Главная</span>
                    </Link>

                    <Link href="/user/listings" class="flex flex-col items-center gap-1 p-2 rounded-lg transition-colors" :class="isActive('/user/listings') ? 'text-purple-600' : 'text-gray-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="text-xs">Объявления</span>
                    </Link>

                    <Link href="/user/favorites" class="flex flex-col items-center gap-1 p-2 rounded-lg transition-colors" :class="isActive('/user/favorites') ? 'text-purple-600' : 'text-gray-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span class="text-xs">Избранное</span>
                    </Link>

                    <Link href="/messages" class="flex flex-col items-center gap-1 p-2 rounded-lg transition-colors" :class="isActive('/messages') ? 'text-purple-600' : 'text-gray-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="text-xs">Сообщения</span>
                    </Link>

                    <Link href="/dashboard" class="flex flex-col items-center gap-1 p-2 rounded-lg transition-colors" :class="isActive('/dashboard') || isActive('/profile') ? 'text-purple-600' : 'text-gray-600'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs">Кабинет</span>
                    </Link>
                </div>
            </div>
        </nav>
        <!-- === КОНЕЦ МОБИЛЬНОЙ НАВИГАЦИИ === -->
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const isActive = (path) => {
    return page.url.startsWith(path);
};
</script>