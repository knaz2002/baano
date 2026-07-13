<template>
    <div class="min-h-screen" style="background-color: #E8E6E1;">
        <!-- Шапка -->
        <header class="bg-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-3 md:px-4">
                <!-- Верхняя строка -->
                <div class="flex items-center justify-between h-14 md:h-24 gap-1 md:gap-4">
                    <!-- Логотип -->
                    <Link href="/" class="flex items-center gap-1 md:gap-2 flex-shrink-0">
                        <img src="/images/logo.png" alt="Baano" class="h-8 md:h-20 w-auto">
                    </Link>

                    <!-- Каталог - скрыт на очень маленьких экранах -->
                    <button 
                        @click="handleCatalog"
                        class="hidden md:block px-3 md:px-4 py-1.5 md:py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg flex-shrink-0"
                        style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    >
                        Каталог
                    </button>

                    <!-- Поиск - на мобильных ниже -->
                    <div class="hidden md:block flex-1 max-w-xl">
                        <div class="relative w-full">
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Поиск объявлений..."
                                class="w-full px-4 py-2 rounded-xl border-2 focus:outline-none"
                                style="border-color: #E7E0EC;"
                                @keyup.enter="performSearch"
                            >
                            <button 
                                @click="performSearch"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-lg hover:bg-gray-100"
                            >
                                <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Правая часть -->
                    <div class="flex items-center gap-1 md:gap-4 flex-shrink-0">
                        <!-- Разместить объявление - скрыто на мобильных -->
                        <Link 
                            href="/user/listings/create"
                            class="hidden lg:block px-3 md:px-4 py-1.5 md:py-2 rounded-xl text-white font-medium text-xs md:text-sm transition-all hover:shadow-lg"
                            style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                        >
                            Разместить
                        </Link>

                        <!-- Сообщения -->
                        <Link href="/dashboard/messages" class="p-1.5 md:p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </Link>

                        <!-- Профиль -->
                        <Link href="/dashboard" class="p-1.5 md:p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </Link>

                        <!-- Мобильное меню -->
                        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-1.5 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Поиск на мобильных - отдельная строка -->
                <div class="md:hidden py-2">
                    <div class="relative w-full">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Поиск объявлений..."
                            class="w-full px-4 py-2 rounded-xl border-2 focus:outline-none text-sm"
                            style="border-color: #E7E0EC;"
                            @keyup.enter="performSearch"
                        >
                        <button 
                            @click="performSearch"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-lg hover:bg-gray-100"
                        >
                            <svg class="w-5 h-5" style="color: #6750A4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Мобильное меню -->
        <div v-if="showMobileMenu" class="md:hidden bg-white border-t shadow-lg">
            <div class="px-4 py-2 space-y-2">
                <button 
                    @click="handleCatalog; showMobileMenu = false"
                    class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50"
                >
                    Каталог
                </button>
                <Link 
                    href="/user/listings/create"
                    class="block px-4 py-3 rounded-xl text-white text-center font-medium text-sm"
                    style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);"
                    @click="showMobileMenu = false"
                >
                    Разместить объявление
                </Link>
                <Link href="/" class="block px-4 py-3 rounded-xl hover:bg-gray-50">
                    Главная
                </Link>
                <Link href="/dashboard" class="block px-4 py-3 rounded-xl hover:bg-gray-50">
                    Личный кабинет
                </Link>
            </div>
        </div>

        <!-- Основной контент -->
        <main>
            <slot />
        </main>

        <!-- Футер -->
        <footer class="bg-white border-t mt-12">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center justify-center gap-2">
                    <img src="/images/logo.png" alt="Baano" class="h-6 w-auto">
                    <span class="text-sm" style="color: #49454F;">© 2026 Baano. Все права защищены.</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const searchQuery = ref('');
const showMobileMenu = ref(false);

const handleCatalog = () => {
    router.get('/');
};

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/listings', { search: searchQuery.value });
    }
};
</script>
