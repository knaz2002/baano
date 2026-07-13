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
                    <Link v-if="$page.props.auth.user" href="/user/listings/create" class="btn-gradient flex-shrink-0">
                        Разместить объявление
                    </Link>

                    <!-- Иконка сообщений со счётчиком -->
                    <Link href="/dashboard/messages" class="icon-glass group relative flex-shrink-0 w-[45px] h-[45px] flex items-center justify-center" title="Сообщения">
                        <svg class="w-5 h-5 transition-all group-hover:scale-110" 
                            style="stroke: #6B7F8C;" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <!-- Счётчик непрочитанных -->
                        <span v-if="unreadCount > 0" 
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-white">
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </Link>
                    
                    <!-- Иконка профиля -->
                    <Link v-if="$page.props.auth.user" href="/dashboard" class="icon-glass group relative flex-shrink-0 w-[45px] h-[45px] flex items-center justify-center" title="Профиль">
                        <svg class="w-5 h-5 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </Link>

                    <Link v-else href="/login" class="icon-glass group relative flex-shrink-0 w-[45px] h-[45px] flex items-center justify-center" title="Войти">
                        <svg class="w-5 h-5 transition-all group-hover:scale-110" 
                             style="stroke: #6B7F8C;" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </Link>
                </div>

                <!-- Мобильная версия -->
                <div class="md:hidden">
                    <div class="flex items-center justify-between">
                        <Link href="/" class="flex-shrink-0">
                            <img src="/images/logo.png" alt="Baano" class="h-10 w-auto">
                        </Link>
                        
                        <div class="flex items-center gap-2">
                            <!-- Иконка сообщений со счётчиком -->
                            <Link href="/dashboard/messages" class="icon-glass p-2 w-[45px] h-[45px] flex items-center justify-center relative" title="Сообщения">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <span v-if="unreadCount > 0" 
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-white">
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </Link>
                            
                            <Link v-if="$page.props.auth.user" href="/dashboard" class="icon-glass p-2 w-[45px] h-[45px] flex items-center justify-center" title="Профиль">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </Link>
                            <Link v-else href="/login" class="icon-glass p-2 w-[45px] h-[45px] flex items-center justify-center" title="Войти">
                                <svg class="w-5 h-5" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </Link>
                            
                            <button @click="showMobileMenu = !showMobileMenu" class="icon-glass p-2 w-[45px] h-[45px] flex items-center justify-center">
                                <svg class="w-6 h-6" style="stroke: #6B7F8C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

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

                    <div v-if="showMobileMenu" class="mt-3 space-y-2 pb-3">
                        <button @click="handleCatalog" class="btn-gradient w-full text-center py-2">
                            Каталог
                        </button>
                        
                        <Link v-if="$page.props.auth.user" href="/user/listings/create" class="btn-gradient w-full text-center py-2 block">
                            Разместить объявление
                        </Link>
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
import { ref, watch, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-catalog']);

const page = usePage();

const showCatalog = ref(false);
const showMobileMenu = ref(false);
const searchQuery = ref('');

// Счётчик непрочитанных сообщений
const unreadCount = computed(() => {
    return page.props.unreadMessagesCount || 0;
});

// Toast уведомления
const toast = ref({ show: false, message: '', type: 'success' });

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showToast(flash.success, 'success');
    } else if (flash?.error) {
        showToast(flash.error, 'error');
    }
}, { deep: true });

const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

const handleCatalog = () => {
    router.get('/');
};

const toggleCatalog = () => {
    showCatalog.value = !showCatalog.value;
    emit('toggle-catalog', showCatalog.value);
};

const logout = () => {
    router.post('/logout');
};
</script>
