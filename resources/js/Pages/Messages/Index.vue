<template>
    <DashboardLayout
        :hide-mobile-header="Boolean(selectedConversation)"
        :hide-mobile-nav="Boolean(selectedConversation)"
        :flush-mobile-content="Boolean(selectedConversation)"
    >
        <div
            class="min-h-screen md:pb-0"
            :class="selectedConversation ? 'pb-0' : 'pb-20'"
            style="background-color: #F7F3EC;"
        >
            <div
                class="max-w-6xl mx-auto md:px-4 md:py-8"
                :class="selectedConversation ? 'px-0 py-0' : 'px-3 py-4'"
            >
                <div
                    class="items-center justify-between mb-4 md:mb-6"
                    :class="selectedConversation ? 'hidden md:flex' : 'flex'"
                >
                    <h1 class="text-xl md:text-2xl font-bold" style="color: #1F4234;">Сообщения</h1>
                    <Link href="/" class="text-sm font-medium hover:underline" style="color: #315C47;">На главную</Link>
                </div>

                <div
                    class="bg-white md:rounded-2xl md:shadow-lg overflow-hidden flex flex-col md:flex-row"
                    :class="selectedConversation
                        ? 'min-h-[100dvh] md:min-h-[600px]'
                        : 'min-h-[600px]'"
                >
                    
                    <!-- ЛЕВАЯ ПАНЕЛЬ: Список диалогов (ВСЕГДА видна) -->
                    <div
                        class="w-full md:w-80 border-r"
                        :class="selectedConversation ? 'hidden md:block' : 'block'"
                        style="border-color: #E8E3DA;"
                    >
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
                                :class="selectedConversationId === conv.id ? 'bg-[#F1F6F2]' : ''"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background-color: #315C47;">
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
                                            style="color: #315C47;"
                                        >
                                            {{ conv.listing.title }}
                                        </p>
                                        <p v-if="conv.last_message" class="text-xs text-gray-600 truncate mt-1">
                                            <span v-if="conv.last_message.sender_id === currentUserId" class="text-gray-400">Вы: </span>
                                            {{ conv.last_message.body }}
                                        </p>
                                    </div>
                                    <div v-if="conv.unread_count > 0" class="text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0" style="background-color: #315C47;">
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
                    <div
                        class="flex-1 flex-col min-w-0"
                        :class="selectedConversation ? 'flex' : 'hidden md:flex'"
                    >
                        <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-lg">Выберите диалог, чтобы начать переписку</p>
                            </div>
                        </div>

                        <div v-else class="flex-1 flex flex-col">
                            <!-- Собеседник -->
                            <div
                                class="bg-white border-b px-3 py-3 md:p-4 flex items-center gap-3 sticky top-0 z-20"
                                style="border-color: #E8E3DA;"
                            >
                                <Link
                                    href="/dashboard/messages"
                                    class="md:hidden flex items-center justify-center w-9 h-9 rounded-full hover:bg-gray-100 flex-shrink-0"
                                    aria-label="Назад к диалогам"
                                >
                                    <svg
                                        class="w-6 h-6"
                                        style="color: #1F4234;"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                </Link>

                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
                                    style="background-color: #315C47;"
                                >
                                    {{ selectedConversation.other_user.name.charAt(0).toUpperCase() }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <h2
                                            class="font-bold text-base md:text-lg truncate"
                                            style="color: #1F4234;"
                                        >
                                            {{ selectedConversation.other_user.name }}
                                        </h2>

                                        <span
                                            class="flex-shrink-0 text-sm font-bold"
                                            style="color: #315C47;"
                                        >
                                            <span style="color: #F4B400;">★</span>
                                            {{ formatRating(selectedConversation.other_user.rating) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Объявление, по которому идёт переписка -->
                            <Link
                                v-if="selectedConversation.listing"
                                :href="`/listings/${selectedConversation.listing.id}`"
                                class="flex items-center gap-3 px-3 py-2.5 md:px-4 bg-white border-b hover:bg-gray-50 transition-colors"
                                style="border-color: #E8E3DA;"
                            >
                                <img
                                    v-if="selectedConversation.listing.image"
                                    :src="selectedConversation.listing.image"
                                    :alt="selectedConversation.listing.title"
                                    class="w-14 h-12 md:w-16 md:h-14 rounded-lg object-cover flex-shrink-0"
                                >

                                <div
                                    v-else
                                    class="w-14 h-12 md:w-16 md:h-14 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <svg
                                        class="w-6 h-6 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm md:text-base font-semibold truncate"
                                        style="color: #1F4234;"
                                    >
                                        {{ selectedConversation.listing.title }}
                                    </p>
                                    <p
                                        class="text-xs mt-0.5"
                                        style="color: #68736B;"
                                    >
                                        Перейти к объявлению
                                    </p>
                                </div>

                                <svg
                                    class="w-5 h-5 flex-shrink-0"
                                    style="color: #68736B;"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </Link>

                            <div ref="messagesContainer" class="flex-1 p-4 space-y-3 overflow-y-auto" style="background-color: #F7F3EC;">
                                <div v-if="loadingMessages" class="text-center text-gray-500 py-8">Загрузка...</div>
                                
                                <div v-else v-for="msg in messages" :key="msg.id" class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                                    <div class="max-w-[80%] md:max-w-md px-4 py-2 rounded-2xl"
                                        :class="msg.is_mine ? 'text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                                        :style="msg.is_mine ? 'background-color: #315C47;' : ''">
                                        <p class="text-sm whitespace-pre-wrap break-words">
                                            <template
                                                v-for="(part, index) in splitMessageBody(msg.body)"
                                                :key="`${msg.id}-${index}`"
                                            >
                                                <a
                                                    v-if="part.isUrl"
                                                    :href="part.text"
                                                    class="underline break-all font-medium"
                                                >
                                                    {{ part.text }}
                                                </a>
                                                <span v-else>{{ part.text }}</span>
                                            </template>
                                        </p>
                                        <p class="text-xs mt-1 opacity-75 text-right">{{ msg.created_at }}</p>
                                    </div>
                                </div>

                                <div v-if="!loadingMessages && messages.length === 0" class="text-center text-gray-500 py-8">
                                    Начните диалог — напишите первое сообщение
                                </div>
                            </div>

                            <form
                                @submit.prevent="sendMessage"
                                class="bg-white p-3 md:p-4 flex gap-2 border-t sticky bottom-0 z-20"
                                style="border-color: #E8E3DA;"
                            >
                                <input v-model="newMessage" type="text" placeholder="Напишите сообщение..." class="flex-1 px-4 py-2 border-2 rounded-full focus:outline-none text-sm" style="border-color: #E8E3DA;" required maxlength="2000">
                                <button type="submit" class="px-6 py-2 rounded-full text-white font-medium transition-all hover:shadow-lg flex-shrink-0" :disabled="sending || !newMessage.trim()" style="background-color: #315C47;">
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
                <h2 class="text-lg md:text-xl font-bold mb-3" style="color: #1F4234;">
                    Удалить диалог?
                </h2>

                <p class="text-sm leading-relaxed mb-2" style="color: #68736B;">
                    Диалог и история сообщений будут удалены только из вашего списка.
                </p>

                <p class="text-sm leading-relaxed mb-6" style="color: #68736B;">
                    У второго участника переписка останется. При новом сообщении диалог снова появится.
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100 cancel-action action-red"
                        style="color: #68736B;"
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
import { ref, computed, nextTick, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const page = usePage();
const props = defineProps({
    conversations: { type: Array, default: () => [] },
    messages: { type: Array, default: () => [] },
    selectedConversation: { type: Object, default: null },
});

const selectedConversationId = ref(props.selectedConversation?.id || null);
const selectedConversation = ref(props.selectedConversation || null);
const messages = ref(props.messages || []);
const newMessage = ref('');
const sending = ref(false);
const loadingMessages = ref(false);
const messagesContainer = ref(null);
const conversationToDelete = ref(null);
const deletingConversation = ref(false);

const currentUserId = computed(() => page.props.auth?.user?.id || null);

const splitMessageBody = (body = '') => {
    const parts = String(body).split(/(https?:\/\/[^\s]+)/g);

    return parts
        .filter(Boolean)
        .map(part => ({
            text: part,
            isUrl: /^https?:\/\//.test(part),
        }));
};

const formatRating = (rating) => {
    if (rating === null || rating === undefined) {
        return '—';
    }

    return Number(rating).toLocaleString('ru-RU', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    });
};

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

const loadConversation = async (convId) => {
    selectedConversationId.value = convId;
    loadingMessages.value = true;
    messages.value = [];

    try {
        const response = await fetch(`/dashboard/messages/api/${convId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Network error');
        }

        const data = await response.json();

        selectedConversation.value = data.conversation;
        messages.value = data.messages;

        const convIndex = props.conversations.findIndex(
            conversation => conversation.id === convId
        );

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

const selectConversation = async (convId) => {
    if (window.matchMedia('(max-width: 767px)').matches) {
        window.location.href = `/dashboard/messages/${convId}`;
        return;
    }

    await loadConversation(convId);
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
        
        await loadConversation(selectedConversationId.value);
    } catch (error) {
        console.error('Error sending message:', error);
        newMessage.value = body;
    } finally {
        sending.value = false;
    }
};

onMounted(() => {
    if (selectedConversation.value) {
        scrollToBottom();
    }
});
</script>