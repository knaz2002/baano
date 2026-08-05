<template>
    <DashboardLayout>
        <div class="min-h-screen pb-24 md:pb-8" style="background-color: #F7F3EC;">
            <!-- Шапка -->
            <div class="bg-white p-4 md:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl md:text-2xl font-bold" style="color: #1F4234;">Мои объявления</h1>
                    <Link 
                        href="/user/listings/create" 
                        class="px-4 py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg whitespace-nowrap confirm-action action-green"
                        style="background-color: #315C47;"
                    >
                        Создать новое
                    </Link>
                </div>
            </div>

            <!-- Список объявлений -->
            <div class="p-3 md:p-4 space-y-3">
                <div 
                    v-for="listing in listings" 
                    :key="listing.id"
                    class="my-listings-text bg-white rounded-xl shadow-md overflow-hidden"
                >
                    <div class="flex">
<!-- Миниатюра -->
<div class="w-24 h-24 md:w-28 md:h-28 flex-shrink-0 bg-gray-100">
    <img 
        v-if="listing.image && listing.image !== ''" 
        :src="listing.image" 
        :alt="listing.title"
        class="w-full h-full object-cover"
        @error="listing.image = null"
    >
    <div v-else class="w-full h-full flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
</div>
                        <!-- Контент -->
                        <div class="flex-1 p-3 md:p-4 flex flex-col justify-between price-accent">
                            <div>
                                <h3 class="font-semibold text-sm md:text-base mb-1" style="color: #1F4234; line-height: 1.3;">
                                    {{ listing.title }}
                                </h3>
                                <div class="text-lg md:text-xl font-bold" style="color: #315C47;">
                                    {{ formatPrice(listing.price) }} ₽
                                </div>
                            </div>
                            
                            <div class="flex items-end justify-between gap-3 mt-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1 mb-1">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" style="color: #fe0000;" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <span class="text-xs md:text-sm font-medium" style="color: #68736B;">
                                            {{ listing.favorites_count || 0 }}
                                        </span>
                                    </div>

                                    <span
                                        class="inline-flex px-2 py-1 rounded-full text-[10px] md:text-xs font-medium"
                                        :style="statusStyle(listing)"
                                    >
                                        {{ statusLabel(listing) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="`/user/listings/${listing.id}/edit`"
                                        title="Редактировать"
                                        class="p-1.5 md:p-2 rounded-lg hover:bg-[#F1F6F2] transition-colors"
                                    >
                                        <svg class="w-5 h-5 md:w-6 md:h-6" style="color: #315C47;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </Link>

                                    <button
                                        type="button"
                                        :title="publicationDesired(listing) ? 'Снять с публикации' : 'Опубликовать'"
                                        class="p-1.5 md:p-2 rounded-lg hover:bg-[#F1F6F2] transition-colors disabled:opacity-50"
                                        :disabled="changingPublicationId === listing.id"
                                        @click="togglePublication(listing)"
                                    >
                                        <svg
                                            v-if="publicationDesired(listing)"
                                            class="w-5 h-5 md:w-6 md:h-6"
                                            style="color: #315C47;"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.73 5.08A10.8 10.8 0 0112 5c5 0 9.27 3.11 11 7a11.8 11.8 0 01-2.14 3.19M6.61 6.61A11.74 11.74 0 001 12c1.73 3.89 6 7 11 7a10.9 10.9 0 005.39-1.39M9.88 9.88a3 3 0 104.24 4.24"/>
                                        </svg>

                                        <svg
                                            v-else
                                            class="w-5 h-5 md:w-6 md:h-6"
                                            style="color: #315C47;"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        title="Удалить"
                                        class="p-1.5 md:p-2 rounded-lg hover:bg-red-50 transition-colors"
                                        style="color: #B3261E;"
                                        @click="openDeleteModal(listing)"
                                    >
                                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пустое состояние -->
                <div v-if="listings.length === 0" class="my-listings-text bg-white rounded-xl shadow-md p-8 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-500 font-medium">У вас пока нет объявлений</p>
                    <Link 
                        href="/user/listings/create" 
                        class="inline-block mt-4 px-6 py-2 rounded-xl text-white font-medium text-sm transition-all hover:shadow-lg confirm-action action-green"
                        style="background-color: #315C47;"
                    >
                        Создать первое объявление
                    </Link>
                </div>
            </div>
        </div>

        <div
            v-if="listingToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(0, 0, 0, 0.5);"
            @click.self="closeDeleteModal"
        >
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-5 md:p-6">
                <h2 class="text-lg md:text-xl font-bold mb-3" style="color: #1F4234;">
                    Удалить объявление?
                </h2>

                <p class="text-sm leading-relaxed mb-3" style="color: #68736B;">
                    Объявление «{{ listingToDelete.title }}» будет удалено без возможности восстановления.
                </p>

                <p class="text-sm leading-relaxed mb-6" style="color: #B3261E;">
                    Все данные карточки, включая фотографии, отзывы, диалоги и сообщения по этому объявлению, также будут удалены.
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl font-medium hover:bg-gray-100 cancel-action action-red"
                        style="color: #68736B;"
                        :disabled="deletingListing"
                        @click="closeDeleteModal"
                    >
                        Отмена
                    </button>

                    <button
                        type="button"
                        class="px-4 py-2 rounded-xl text-white font-medium disabled:opacity-50"
                        style="background-color: #B3261E;"
                        :disabled="deletingListing"
                        @click="deleteListing"
                    >
                        {{ deletingListing ? 'Удаление…' : 'Удалить' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    listings: {
        type: Array,
        default: () => []
    }
});

const listingToDelete = ref(null);
const deletingListing = ref(false);
const changingPublicationId = ref(null);

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price || 0);
};

const publicationDesired = (listing) => {
    if (
        listing.status === 'pending'
        && listing.requested_is_active !== null
    ) {
        return Boolean(listing.requested_is_active);
    }

    return Boolean(listing.is_active);
};

const statusLabel = (listing) => {
    if (listing.status === 'pending') {
        return publicationDesired(listing)
            ? 'На модерации: публикация'
            : 'На модерации: снятие';
    }

    if (listing.status === 'active' && listing.is_active) {
        return 'Опубликовано';
    }

    if (listing.status === 'sold') {
        return 'Завершено';
    }

    return 'Снято с публикации';
};

const statusStyle = (listing) => {
    if (listing.status === 'pending') {
        return {
            backgroundColor: '#FFF3CD',
            color: '#856404',
        };
    }

    if (listing.status === 'active' && listing.is_active) {
        return {
            backgroundColor: '#E8F5E9',
            color: '#2E7D32',
        };
    }

    return {
        backgroundColor: '#F1F6F2',
        color: '#68736B',
    };
};

const togglePublication = (listing) => {
    changingPublicationId.value = listing.id;

    router.patch(
        `/user/listings/${listing.id}/publication`,
        {
            publish: !publicationDesired(listing),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                changingPublicationId.value = null;
            },
        }
    );
};

const openDeleteModal = (listing) => {
    listingToDelete.value = listing;
};

const closeDeleteModal = () => {
    if (deletingListing.value) return;

    listingToDelete.value = null;
};

const deleteListing = () => {
    if (!listingToDelete.value || deletingListing.value) return;

    deletingListing.value = true;

    router.delete(
        `/user/listings/${listingToDelete.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                listingToDelete.value = null;
            },
            onFinish: () => {
                deletingListing.value = false;
            },
        }
    );
};
</script>
