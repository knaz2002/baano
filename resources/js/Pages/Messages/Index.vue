<template>
    <div>
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold" style="color: #2C3E50;">Сообщения</h1>
                <Link href="/" class="btn-gradient">Назад</Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-if="conversations.length === 0" class="p-8 text-center text-gray-500">
                    У вас пока нет диалогов
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <Link 
                        v-for="conv in conversations" 
                        :key="conv.id"
                        :href="`/messages/${conv.id}`"
                        class="block p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ conv.other_user.name.charAt(0).toUpperCase() }}
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        {{ conv.other_user.name }}
                                    </h3>
                                    <span v-if="conv.last_message" class="text-xs text-gray-500">
                                        {{ formatDate(conv.last_message.created_at) }}
                                    </span>
                                </div>
                                
                                <p v-if="conv.last_message" class="text-sm text-gray-600 truncate mt-1">
                                    <span v-if="conv.last_message.sender_id === auth.user.id" class="text-gray-400">Вы: </span>
                                    {{ conv.last_message.body }}
                                </p>
                            </div>

                            <div v-if="conv.unread_count > 0" 
                                 class="bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0">
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
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    conversations: Array,
    auth: Object,
});

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'только что';
    if (diff < 3600000) return `${Math.floor(diff/60000)} мин`;
    if (diff < 86400000) return `${Math.floor(diff/3600000)} ч`;
    
    return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' });
};
</script>