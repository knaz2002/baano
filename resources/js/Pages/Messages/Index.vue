<template>
    <DashboardLayout>
        <div class="min-h-screen pb-20 md:pb-0" style="background-color: #E8E6E1;">
            <div class="max-w-6xl mx-auto px-3 md:px-4 py-4 md:py-8">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <h1 class="text-xl md:text-2xl font-bold" style="color: #1D1B20;">Сообщения</h1>
                    <Link href="/" class="text-sm font-medium hover:underline" style="color: #6750A4;">На главную</Link>
                </div>

                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col md:flex-row" style="min-height: 600px;">
                    
                    <!-- ЛЕВАЯ ПАНЕЛЬ: Список диалогов (ВСЕГДА видна) -->
                    <div class="w-full md:w-80 border-r" style="border-color: #E7E0EC;">
                        <div v-if="!conversations || conversations.length === 0" class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="text-lg font-medium">У вас пока нет диалогов</p>
                            <p class="text-sm mt-2 text-gray-400">Начните общение с продавцами</p>
                        </div>

                        <div v-else class="divide-y divide-gray-100 overflow-y-auto" style="max-height: calc(100vh - 250px);">
                            <div
                                v-for="conv in conversations"
                                :key="conv.id"
                                @click="selectConversation(conv.id)"
                                class="w-full text-left p-3 md:p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                                :class="selectedConversationId === conv.id ? 'bg-purple-50' : ''"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background: linear-gradient(135deg, #6750A4 0%, #9B7FCF 100%);">
                                        {{ conv.other_user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="font-semibold text-gray-900 truncate text-sm">{{ conv.other_user.name }}</h3>
                                            <span v-if="conv.last_message" class="text-xs text-gray-500 flex-shrink-0">{{ formatDate(conv.last_message.created_at) }}</span>
                                        </div>
                                        <p
                                            v-if="conv.listing"
                                            class="text-xs font-medium truncate mt-1"
                                            style="color: #6750A4;"
                                        >
                                            {{ conv.listing.title }}
                                        </p>
                                        <p v-if="conv.last_message" class="text-xs text-gray-600 truncate mt-1">
                                            <span v-if="conv.last_message.sender_id === currentUserId" class="text-gray-400">Вы: </span>
                                            {{ conv.last_message.body }}
                                        </p>
                                    </div>
                                    <div v-if="conv.unread_count > 0" class="text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                        {{ conv.unread_count }}
                                    </div>

                                    <button
                                        type="button"
                                        title="Удалить диалог"
                                        class="p-2 rounded-lg hover:bg-red-50 transition-colors flex-shrink-0"
                                        style="color: #B3261E;"
                                        @click.stop="openDeleteModal(conv)"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ПРАВАЯ ПАНЕЛЬ: Чат -->
                    <div class="flex-1 flex flex-col">
                        <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-lg">Выберите диалог, чтобы начать переписку</p>
                            </div>
                        </div>

                        <div v-else class="flex-1 flex flex-col">
                            <div class="bg-white border-b p-4 flex items-center gap-3 sticky top-0 z-10" style="border-color: #E7E0EC;">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background: linear-gradient(135deg, #6750A4 0%, #9B7FCF 100%);">
                                    {{ selectedConversation.other_user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1">
                                    <h2 class="font-semibold text-base" style="color: #1D1B20;">{{ selectedConversation.other_user.name }}</h2>
                                    <p class="text-xs text-green-500">в сети</p>
                                    <Link
                                        v-if="selectedConversation.listing"
                                        :href="`/listings/${selectedConversation.listing.id}`"
                                        class="block text-xs font-medium truncate hover:underline mt-1"
                                        style="color: #6750A4;"
                                    >
                                        {{ selectedConversation.listing.title }}
                                    </Link>
                                </div>
                            </div>

                            <div ref="messagesContainer" class="flex-1 p-4 space-y-3 overflow-y-auto" style="background-color: #F5F5F5;">
                                <div v-if="loadingMessages" class="text-center text-gray-500 py-8">Загрузка...</div>
                                
                                <div v-else v-for="msg in messages" :key="msg.id" class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                                    <div class="max-w-[80%] md:max-w-md px-4 py-2 rounded-2xl"
                                        :class="msg.is_mine ? 'text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                                        :style="msg.is_mine ? 'background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);' : ''">
                                        <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                        <p class="text-xs mt-1 opacity-75 text-right">{{ msg.created_at }}</p>
                                    </div>
                                </div>

                                <div v-if="!loadingMessages && messages.length === 0" class="text-center text-gray-500 py-8">
                                    Начните диалог — напишите первое сообщение
                                </div>
                            </div>

                            <form @submit.prevent="sendMessage" class="bg-white p-4 flex gap-2 border-t" style="border-color: #E7E0EC;">
                                <input v-model="newMessage" type="text" placeholder="Напишите сообщение..." class="flex-1 px-4 py-2 border-2 rounded-full focus:outline-none text-sm" style="border-color: #E7E0EC;" required maxlength="2000">
                                <button type="submit" class="px-6 py-2 rounded-full text-white font-medium transition-all hover:shadow-lg flex-shrink-0" :disabled="sending || !newMessage.trim()" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                    <svg v-if="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span v-else class="text-sm">...</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="conversationToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(0, 0, 0, 0.5);"
            @click.self="closeDeleteModal"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-5 md:p-6">
                <h2 class="text-lg md:text-xl font-bold mb-3" style="color: #1D1B20;">
                    Удалить диалог?
                </h2>

                <p class="text-sm leading-relaxed mb-2" style="color: #49454F;">
                    Диалог и история сообщений будут удалены только из вашего списка.
                </p>

                <p class="text-sm leading-relaxed mb-6" style="color: #49454F;">
                    У второго участника переписка останется. При новом сообщении диалог снова появится.
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100"
                        style="color: #49454F;"
                        :disabled="deletingConversation"
                        @click="closeDeleteModal"
                    >
                        Отмена
                    </button>

                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-white font-medium disabled:opacity-50"
                        style="background-color: #B3261E;"
                        :disabled="deletingConversation"
                        @click="deleteConversation"
                    >
                        {{ deletingConversation ? 'Удаление…' : 'Удалить' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const page = usePage();
const props = defineProps({
    conversations: { type: Array, default: () => [] }
});

const selectedConversationId = ref(null);
const selectedConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const sending = ref(false);
const loadingMessages = ref(false);
const messagesContainer = ref(null);
const conversationToDelete = ref(null);
const deletingConversation = ref(false);

const currentUserId = computed(() => page.props.auth?.user?.id || null);

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const selectConversation = async (convId) => {
    selectedConversationId.value = convId;
    loadingMessages.value = true;
    messages.value = [];
    
    try {
        // ИСПРАВЛЕНО: добавлен префикс /dashboard
        const response = await fetch(`/dashboard/messages/api/${convId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        selectedConversation.value = data.conversation;
        messages.value = data.messages;
        
        const convIndex = props.conversations.findIndex(c => c.id === convId);
        if (convIndex !== -1) {
            props.conversations[convIndex].unread_count = 0;
        }
        
        scrollToBottom();
    } catch (error) {
        console.error('Error loading messages:', error);
    } finally {
        loadingMessages.value = false;
    }
};

const openDeleteModal = (conversation) => {
    conversationToDelete.value = conversation;
};

const closeDeleteModal = () => {
    if (deletingConversation.value) return;

    conversationToDelete.value = null;
};

const deleteConversation = async () => {
    if (!conversationToDelete.value || deletingConversation.value) return;

    deletingConversation.value = true;
    const conversationId = conversationToDelete.value.id;

    try {
        const response = await fetch(`/dashboard/messages/${conversationId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });

        if (!response.ok) {
            throw new Error('Delete error');
        }

        const index = props.conversations.findIndex(
            conversation => conversation.id === conversationId
        );

        if (index !== -1) {
            props.conversations.splice(index, 1);
        }

        if (selectedConversationId.value === conversationId) {
            selectedConversationId.value = null;
            selectedConversation.value = null;
            messages.value = [];
        }

        conversationToDelete.value = null;
    } catch (error) {
        console.error('Error deleting conversation:', error);
    } finally {
        deletingConversation.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || sending.value || !selectedConversationId.value) return;

    sending.value = true;
    const body = newMessage.value;
    newMessage.value = '';

    try {
        // ИСПРАВЛЕНО: добавлен префикс /dashboard
        const response = await fetch(`/dashboard/messages/${selectedConversationId.value}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ body }),
        });
        
        if (!response.ok) throw new Error('Send error');
        
        await selectConversation(selectedConversationId.value);
    } catch (error) {
        console.error('Error sending message:', error);
        newMessage.value = body;
    } finally {
        sending.value = false;
    }
};
</script>