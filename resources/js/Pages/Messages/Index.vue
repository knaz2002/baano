<template>
    <div class="min-h-screen" style="background-color: #E8E6E1;">
        <div class="max-w-4xl mx-auto px-3 md:px-4 py-4 md:py-8">
            <!-- Заголовок -->
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h1 class="text-xl md:text-2xl font-bold" style="color: #1D1B20;">Сообщения</h1>
                <Link href="/" class="text-sm font-medium hover:underline" style="color: #6750A4;">
                    На главную
                </Link>
            </div>

            <!-- Список диалогов -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div v-if="!conversations || conversations.length === 0" class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="text-lg font-medium">У вас пока нет диалогов</p>
                    <p class="text-sm mt-2 text-gray-400">Начните общение с продавцами</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <Link
                        v-for="conv in conversations"
                        :key="conv.id"
                        :href="`/messages/${conv.id}`"
                        class="block p-3 md:p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-3 md:gap-4">
                            <!-- Аватар -->
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                {{ conv.other_user.name.charAt(0).toUpperCase() }}
                            </div>

                            <!-- Контент -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-semibold text-gray-900 truncate text-sm md:text-base">
                                        {{ conv.other_user.name }}
                                    </h3>
                                    <span v-if="conv.last_message" class="text-xs text-gray-500 flex-shrink-0">
                                        {{ formatDate(conv.last_message.created_at) }}
                                    </span>
                                </div>

                                <p v-if="conv.last_message" class="text-xs md:text-sm text-gray-600 truncate mt-1">
                                    <span v-if="conv.last_message.sender_id === currentUserId" class="text-gray-400">Вы: </span>
                                    {{ conv.last_message.body }}
                                </p>
                            </div>

                            <!-- Счётчик непрочитанных -->
                            <div v-if="conv.unread_count > 0"
                                 class="text-white text-xs font-bold rounded-full w-5 h-5 md:w-6 md:h-6 flex items-center justify-center flex-shrink-0"
                                 style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                {{ conv.unread_count }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const props = defineProps({
    conversations: {
        type: Array,
        default: () => []
    }
});

const currentUserId = computed(() => {
    return page.props.auth?.user?.id || null;
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) return 'только что';
    if (diff < 3600000) return `${Math.floor(diff / 60000)} мин`;
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} ч`;

    return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' });
};
</script>
