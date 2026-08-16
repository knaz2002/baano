<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth'],
})

type ConversationItem = {
  id: number
  other_user: { id: number; name: string }
  listing: { id: number; title: string } | null
  last_message: {
    body: string
    sender_id: number
    created_at: string
  } | null
  unread_count: number
  can_request_review?: boolean
}

type ChatMessage = {
  id: number
  body: string
  sender_id: number
  sender_name: string
  is_mine: boolean
  created_at: string
}

const route = useRoute()
const { apiFetch } = useApi()
const auth = useAuthStore()

const conversations = ref<ConversationItem[]>([])
const selectedConversation = ref<ConversationItem | null>(null)
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const loading = ref(true)
const loadingMessages = ref(false)
const sending = ref(false)
const error = ref('')
const conversationToDelete = ref<ConversationItem | null>(null)
const deletingConversation = ref(false)
const requestingReview = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

const currentUserId = computed(() => auth.user?.id ?? 0)
const selectedConversationId = computed(() => selectedConversation.value?.id ?? null)
const canRequestReview = computed(() => !!selectedConversation.value?.can_request_review)

function formatDate(iso: string) {
  try {
    return new Date(iso).toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'short',
    })
  } catch {
    return ''
  }
}

function linkifyMessage(body: string): string {
  const escaped = body
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  return escaped.replace(
    /(https?:\/\/[^\s]+)/g,
    '<a href="$1" class="underline break-all" target="_blank" rel="noopener noreferrer">$1</a>',
  )
}

async function ensureVerified() {
  if (!auth.loaded) {
    await auth.fetchUser()
  }
  if (!auth.isEmailVerified) {
    await navigateTo('/verify-email')
    return false
  }
  return true
}

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

async function loadConversations() {
  loading.value = true
  error.value = ''
  try {
    if (!(await ensureVerified())) {
      return
    }
    const res = await apiFetch<{ data: ConversationItem[] }>('/api/conversations')
    conversations.value = res.data

    const q = route.query.conversation
    if (q) {
      await selectConversation(Number(q))
    }
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить сообщения'
  } finally {
    loading.value = false
  }
}

async function selectConversation(id: number) {
  selectedConversation.value = conversations.value.find(c => c.id === id) || null
  loadingMessages.value = true
  messages.value = []
  try {
    const res = await apiFetch<{
      data: {
        conversation: ConversationItem
        messages: ChatMessage[]
      }
    }>(`/api/conversations/${id}/messages`)
    selectedConversation.value = {
      ...(selectedConversation.value || res.data.conversation),
      ...res.data.conversation,
      unread_count: 0,
    }
    messages.value = res.data.messages
    const idx = conversations.value.findIndex(c => c.id === id)
    if (idx !== -1) {
      conversations.value[idx] = {
        ...conversations.value[idx],
        unread_count: 0,
      }
    }
    await scrollToBottom()
  } catch (e) {
    console.error(e)
  } finally {
    loadingMessages.value = false
  }
}

async function sendMessage() {
  if (!selectedConversationId.value || !newMessage.value.trim() || sending.value) {
    return
  }
  sending.value = true
  const body = newMessage.value.trim()
  try {
    const res = await apiFetch<{ data: ChatMessage }>(
      `/api/conversations/${selectedConversationId.value}/messages`,
      {
        method: 'POST',
        body: { body },
      },
    )
    messages.value.push(res.data)
    newMessage.value = ''
    const idx = conversations.value.findIndex(c => c.id === selectedConversationId.value)
    if (idx !== -1) {
      conversations.value[idx] = {
        ...conversations.value[idx],
        last_message: {
          body: res.data.body,
          sender_id: res.data.sender_id,
          created_at: new Date().toISOString(),
        },
      }
      const [item] = conversations.value.splice(idx, 1)
      conversations.value.unshift(item)
    }
    await scrollToBottom()
  } catch (e) {
    console.error(e)
  } finally {
    sending.value = false
  }
}

async function requestReview() {
  if (!selectedConversationId.value || requestingReview.value || !canRequestReview.value) {
    return
  }
  requestingReview.value = true
  try {
    const res = await apiFetch<{
      data: { message: ChatMessage }
      message: string
    }>(`/api/conversations/${selectedConversationId.value}/review-invite`, {
      method: 'POST',
    })
    messages.value.push(res.data.message)
    if (selectedConversation.value) {
      selectedConversation.value = {
        ...selectedConversation.value,
        can_request_review: false,
      }
    }
    await scrollToBottom()
  } catch (e: any) {
    console.error(e)
    alert(e?.data?.message || 'Не удалось отправить запрос на отзыв')
  } finally {
    requestingReview.value = false
  }
}

function openDeleteModal(conv: ConversationItem) {
  conversationToDelete.value = conv
}

function closeDeleteModal() {
  if (deletingConversation.value) {
    return
  }
  conversationToDelete.value = null
}

async function deleteConversation() {
  if (!conversationToDelete.value || deletingConversation.value) {
    return
  }
  deletingConversation.value = true
  try {
    const id = conversationToDelete.value.id
    await apiFetch(`/api/conversations/${id}`, { method: 'DELETE' })
    conversations.value = conversations.value.filter(c => c.id !== id)
    if (selectedConversationId.value === id) {
      selectedConversation.value = null
      messages.value = []
    }
    conversationToDelete.value = null
  } catch (e) {
    console.error(e)
  } finally {
    deletingConversation.value = false
  }
}

onMounted(() => {
  loadConversations()
})
</script>

<template>
  <div class="min-h-screen pb-4">
    <div class="flex items-center justify-between mb-4 md:mb-6">
      <h1 class="font-heading text-xl md:text-2xl font-bold text-baano-ink">
        Сообщения
      </h1>
      <NuxtLink to="/" class="text-sm font-medium text-baano-green hover:underline">
        На главную
      </NuxtLink>
    </div>

    <div v-if="loading" class="py-16 text-center text-baano-muted">
      Загрузка…
    </div>
    <div v-else-if="error" class="py-16 text-center text-red-600">
      {{ error }}
    </div>

    <div
      v-else
      class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col md:flex-row"
      style="min-height: 600px;"
    >
      <div class="w-full md:w-80 border-r border-baano-border">
        <div
          v-if="conversations.length === 0"
          class="p-8 text-center text-gray-500"
        >
          <p class="text-lg font-medium">
            У вас пока нет диалогов
          </p>
          <p class="text-sm mt-2 text-gray-400">
            Начните общение с продавцами
          </p>
        </div>

        <div
          v-else
          class="divide-y divide-gray-100 overflow-y-auto"
          style="max-height: calc(100vh - 250px);"
        >
          <div
            v-for="conv in conversations"
            :key="conv.id"
            class="w-full text-left p-3 md:p-4 hover:bg-gray-50 transition-colors cursor-pointer"
            :class="selectedConversationId === conv.id ? 'bg-[#F1F6F2]' : ''"
            @click="selectConversation(conv.id)"
          >
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 bg-baano-green">
                {{ conv.other_user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <h3 class="font-semibold text-gray-900 truncate text-sm">
                    {{ conv.other_user.name }}
                  </h3>
                  <span
                    v-if="conv.last_message"
                    class="text-xs text-gray-500 flex-shrink-0"
                  >
                    {{ formatDate(conv.last_message.created_at) }}
                  </span>
                </div>
                <p
                  v-if="conv.listing"
                  class="text-xs font-medium truncate mt-1 text-baano-green"
                >
                  {{ conv.listing.title }}
                </p>
                <p
                  v-if="conv.last_message"
                  class="text-xs text-gray-600 truncate mt-1"
                >
                  <span
                    v-if="conv.last_message.sender_id === currentUserId"
                    class="text-gray-400"
                  >Вы: </span>
                  {{ conv.last_message.body }}
                </p>
              </div>
              <div
                v-if="conv.unread_count > 0"
                class="text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 bg-baano-green"
              >
                {{ conv.unread_count }}
              </div>
              <button
                type="button"
                title="Удалить диалог"
                class="p-2 rounded-lg hover:bg-red-50 text-red-700 flex-shrink-0"
                @click.stop="openDeleteModal(conv)"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 flex flex-col">
        <div
          v-if="!selectedConversation"
          class="flex-1 flex items-center justify-center text-gray-500"
        >
          <p class="text-lg">
            Выберите диалог, чтобы начать переписку
          </p>
        </div>

        <div v-else class="flex-1 flex flex-col min-h-[400px]">
            <div class="bg-white border-b p-4 flex items-center gap-3 border-baano-border">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold bg-baano-green">
                {{ selectedConversation.other_user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-base text-baano-ink">
                  {{ selectedConversation.other_user.name }}
                </h2>
                <NuxtLink
                  v-if="selectedConversation.listing"
                  :to="`/listings/${selectedConversation.listing.id}`"
                  class="block text-xs font-medium truncate text-baano-green hover:underline mt-1"
                >
                  {{ selectedConversation.listing.title }}
                </NuxtLink>
              </div>
              <button
                v-if="canRequestReview"
                type="button"
                class="px-3 py-2 rounded-xl text-xs md:text-sm font-medium text-white bg-baano-green disabled:opacity-50 whitespace-nowrap"
                :disabled="requestingReview"
                @click="requestReview"
              >
                {{ requestingReview ? '…' : 'Запросить отзыв' }}
              </button>
            </div>

          <div
            ref="messagesContainer"
            class="flex-1 p-4 space-y-3 overflow-y-auto bg-baano-cream"
            style="max-height: 420px;"
          >
            <div v-if="loadingMessages" class="text-center text-gray-500 py-8">
              Загрузка…
            </div>
            <template v-else>
              <div
                v-for="msg in messages"
                :key="msg.id"
                class="flex"
                :class="msg.is_mine ? 'justify-end' : 'justify-start'"
              >
                <div
                  class="max-w-[80%] md:max-w-md px-4 py-2 rounded-2xl"
                  :class="msg.is_mine
                    ? 'text-white rounded-br-sm bg-baano-green'
                    : 'bg-white text-gray-900 rounded-bl-sm shadow-sm'"
                >
                  <p
                    class="text-sm whitespace-pre-wrap break-words"
                    v-html="linkifyMessage(msg.body)"
                  />
                  <p class="text-xs mt-1 opacity-75 text-right">
                    {{ msg.created_at }}
                  </p>
                </div>
              </div>
              <div
                v-if="messages.length === 0"
                class="text-center text-gray-500 py-8"
              >
                Начните диалог — напишите первое сообщение
              </div>
            </template>
          </div>

          <form
            class="bg-white p-4 flex gap-2 border-t border-baano-border"
            @submit.prevent="sendMessage"
          >
            <input
              v-model="newMessage"
              type="text"
              placeholder="Напишите сообщение..."
              class="flex-1 px-4 py-2 border-2 rounded-full focus:outline-none text-sm border-baano-border"
              required
              maxlength="1000"
            >
            <button
              type="submit"
              class="px-6 py-2 rounded-full text-white font-medium bg-baano-green disabled:opacity-50"
              :disabled="sending || !newMessage.trim()"
            >
              {{ sending ? '…' : '→' }}
            </button>
          </form>
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
        <h2 class="text-lg font-bold mb-3 text-baano-ink">
          Удалить диалог?
        </h2>
        <p class="text-sm text-baano-muted mb-2">
          Диалог будет скрыт только у вас. У второго участника переписка останется.
        </p>
        <div class="flex justify-end gap-3 mt-6">
          <button
            type="button"
            class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100"
            :disabled="deletingConversation"
            @click="closeDeleteModal"
          >
            Отмена
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-xl text-white font-medium bg-red-700 disabled:opacity-50"
            :disabled="deletingConversation"
            @click="deleteConversation"
          >
            {{ deletingConversation ? 'Удаление…' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
