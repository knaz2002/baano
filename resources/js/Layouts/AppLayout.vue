<template>
    <div class="min-h-screen" style="background-color: #E8E6E1;">
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center gap-4">
                    <!-- Логотип -->
                    <Link href="/" class="flex-shrink-0">
                        <img src="/images/logo.png" alt="Baano" class="h-12 w-auto">
                    </Link>

                    <!-- Каталог -->
                    <button 
                        @click="toggleCatalog"
                        class="btn-gradient flex-shrink-0"
                    >
                        Каталог
                    </button>
                    
                    <!-- Поисковая строка (занимает всё доступное пространство) -->
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

                    <!-- Разместить объявление-->
                    <Link href="/user/listings/create" class="btn-gradient flex-shrink-0">
                        Разместить объявление
                    </Link>

                    <!-- Иконки -->
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

                    <Link href="/favorites" class="icon-glass group relative flex-shrink-0" title="Избранное">
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
            </div>
        </header>

        <main class="container mx-auto px-6 py-8">
            <slot />
        </main>

        <footer class="mt-16 py-8 text-center" style="color: #5A6268;">
            <p>© {{ new Date().getFullYear() }} Baano. Все права защищены.</p>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-catalog']);

const showCatalog = ref(false);
const searchQuery = ref('');

const toggleCatalog = () => {
    showCatalog.value = !showCatalog.value;
    emit('toggle-catalog', showCatalog.value);
};

const logout = () => {
    router.post('/logout');
};
</script>
