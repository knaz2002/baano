<template>
    <DashboardLayout active-tab="messages">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="height: calc(100vh - 150px);">
            <div class="grid grid-cols-3 h-full">
                <!-- Список диалогов -->
                <div class="col-span-1 border-r border-gray-200 overflow-y-auto">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold" style="color: #2C3E50;">Сообщения</h2>
                    </div>
                    
                    <div v-if="!conversations || conversations.length === 0" class="p-8 text-center text-gray-500">
                        Нет диалогов
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <button
                            v-for="conv in conversations"
                            :key="conv.id"
                            @click="selectConversation(conv.id)"
                            class="w-full p-4 hover:bg-gray-50 transition-colors text-left"
                            :class="selectedConversationId === conv.id ? 'bg-purple-50' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold flex-shrink-0">
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
                                    
                                    <p v-if="conv.last_message" class="text-sm text-gray-600 truncate">
                                        {{ conv.last_message.body }}
                                    </p>
                                </div>
                                
                                <div v-if="conv.unread_count > 0"
                                     class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0">
                                    {{ conv.unread_count }}
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Окно чата -->
                <div class="col-span-2 flex flex-col">
                    <div v-if="selectedConversation" class="flex flex-col h-full">
                        <!-- Шапка чата -->
                        <div class="p-4 border-b border-gray-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                {{ selectedConversation.other_user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ selectedConversation.other_user.name }}</h3>
                                <p class="text-xs text-gray-500">в сети</p>
                            </div>
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
                                    class="max-w-[70%] px-4 py-2 rounded-2xl text-sm"
                                    :class="msg.is_mine
                                        ? 'bg-purple-500 text-white rounded-br-sm'
                                        : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                                >
                                    <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                    <p class="text-xs mt-1 opacity-75">{{ msg.created_at }}</p>
                                </div>
                            </div>

                            <div v-if="!chatMessages || chatMessages.length === 0" class="text-center text-gray-500 py-8">
                                <p>Нет сообщений</p>
                                <p class="text-sm mt-2">Напишите первое сообщение</p>
                            </div>
                        </div>

                        <!-- Форма отправки -->
                        <form @submit.prevent="sendMessage" class="p-4 border-t bg-white">
                            <div class="flex gap-2">
                                <input
                                    v-model="messageText"
                                    type="text"
                                    placeholder="Напишите сообщение..."
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:border-purple-500"
                                    required
                                    maxlength="1000"
                                >
                                <button
                                    type="submit"
                                    class="btn-gradient px-6 py-2 rounded-full"
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

                    <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                        <div class="text-center text-gray-500">
                            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-lg font-medium">Выберите чат, чтобы начать переписку</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    conversations: {
        type: Array,
        default: () => []
    },
    messages: {
        type: Array,
        default: () => []
    }
});

const selectedConversationId = ref(null);
const selectedConversation = ref(null);
const chatMessages = ref(props.messages || []);
const messageText = ref('');
const sendingMessage = ref(false);
const messagesContainer = ref(null);

// Следим за изменением props.messages
watch(() => props.messages, (newMessages) => {
    if (newMessages) {
        chatMessages.value = newMessages;
        nextTick(() => scrollToBottom());
    }
}, { deep: true });

const selectConversation = (id) => {
    selectedConversationId.value = id;
    selectedConversation.value = props.conversations.find(c => c.id === id);
    
    router.get(`/dashboard/messages/${id}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            // props.messages обновится автоматически из ответа сервера
        }
    });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = () => {
    if (!messageText.value.trim() || sendingMessage.value || !selectedConversationId.value) return;
    
    sendingMessage.value = true;
    
    router.post(`/dashboard/messages/${selectedConversationId.value}`, {
        body: messageText.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            messageText.value = '';
            // props.messages обновится из ответа
        },
        onFinish: () => {
            sendingMessage.value = false;
        },
    });
};

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
