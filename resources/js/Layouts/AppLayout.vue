<template>
    <div class="min-h-screen" style="background-color: #E8E6E1;">
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-4 md:px-6 py-3 md:py-4">
                <!-- Десктопная версия -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Логотип -->
                    <Link href="/" class="flex-shrink-0">
                        <img src="/images/logo.png" alt="Baano" class="h-12 w-auto">
                    </Link>

                    <!-- Каталог -->
                    <button @click="handleCatalog" class="btn-gradient flex-shrink-0">
                        Каталог
                    </button>

                    <!-- Поисковая строка -->
                    <div class="relative flex-1">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Поиск объявлений..." 
                            class="w-full px-6 py-3 bg-white transition-all focus:outline-none"
                            style="border-radius: 16px; border: 2px solid rgba(139, 154, 158, 0.2);"
                        >
                        <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5" 
                             style="color: #8B9A9E;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Разместить объявление -->
                    <Link href="/user/listings/create" class="btn-gradient flex-shrink-0">
                        Разместить объявление
                    </Link>

                    <!-- Иконки -->
                    <Link href="/messages" class="icon-glass group relative flex-shrink-0" title="Сообщения">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                            style="stroke: #6B7F8C;" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </Link>
                    
                    <Link href="/" class="icon-glass group relative flex-shrink-0" title="Главная">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </Link>

                    <Link href="/user/listings" class="icon-glass group relative flex-shrink-0" title="Мои объявления">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </Link>

                    <Link href="/user/favorites" class="icon-glass group relative flex-shrink-0" title="Избранное">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </Link>

                    <Link v-if="!$page.props.auth.user" href="/login" class="icon-glass group relative flex-shrink-0" title="Войти">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </Link>
                    
                    <button v-else @click="logout" class="icon-glass group relative flex-shrink-0" title="Выйти">
                        <svg class="w-6 h-6 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>

                <!-- Мобильная версия -->
                <div class="md:hidden">
                    <!-- Верхняя строка: логотип + гамбургер -->
                    <div class="flex items-center justify-between">
                        <Link href="/" class="flex-shrink-0">
                            <img src="/images/logo.png" alt="Baano" class="h-10 w-auto">
                        </Link>
                        
                        <button @click="showMobileMenu = !showMobileMenu" class="icon-glass p-2">
                            <svg class="w-6 h-6" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Поисковая строка -->
                    <div class="relative mt-3">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Поиск..." 
                            class="w-full px-4 py-2 bg-white transition-all focus:outline-none text-sm"
                            style="border-radius: 12px; border: 2px solid rgba(139, 154, 158, 0.2);"
                        >
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4" 
                             style="color: #8B9A9E;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Мобильное меню -->
                    <div v-if="showMobileMenu" class="mt-3 space-y-2 pb-3">
                        <button @click="handleCatalog" class="btn-gradient w-full text-center py-2">
                            Каталог
                        </button>
                        
                        <Link href="/user/listings/create" class="btn-gradient w-full text-center py-2 block">
                            Разместить объявление
                        </Link>

                        <div class="flex justify-around pt-2 border-t border-gray-200">
                            <Link href="/" class="icon-glass p-2" title="Главная">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </Link>

                            <Link href="/user/listings" class="icon-glass p-2" title="Мои объявления">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </Link>

                            <Link href="/user/favorites" class="icon-glass p-2" title="Избранное">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </Link>

                            <Link v-if="!$page.props.auth.user" href="/login" class="icon-glass p-2" title="Войти">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </Link>
                            
                            <button v-else @click="logout" class="icon-glass p-2" title="Выйти">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="container mx-auto px-4 md:px-6 py-6 md:py-8">
            <slot />
        </main>

        <footer class="mt-16 py-8 text-center" style="color: #5A6268;">
            <p>© {{ new Date().getFullYear() }} Baano. Все права защищены.</p>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-catalog']);

const showCatalog = ref(false);
const showMobileMenu = ref(false);
const searchQuery = ref('');

const handleCatalog = () => {
    if (window.location.pathname === '/') {
        showCatalog.value = !showCatalog.value;
        showMobileMenu.value = false;
        emit('toggle-catalog', showCatalog.value);
    } else {
        router.get('/');
    }
};

const toggleCatalog = () => {
    showCatalog.value = !showCatalog.value;
    emit('toggle-catalog', showCatalog.value);
};

const logout = () => {
    router.post('/logout');
};
</script>
