<template>
    <AppLayout>
        <div class="min-h-screen py-8 md:py-12" style="background-color: #F7F3EC;">
            <div class="max-w-2xl mx-auto px-4">
                <div class="bg-white rounded-2xl shadow-lg p-5 md:p-8">
                    <div class="mb-6">
                        <p
                            class="text-sm font-medium mb-2"
                            style="color: #315C47;"
                        >
                            Отзыв к объявлению
                        </p>

                        <h1
                            class="text-2xl md:text-3xl font-bold"
                            style="color: #1F4234;"
                        >
                            {{ invite.listing.title }}
                        </h1>

                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            Оцените взаимодействие с владельцем объявления.
                            Отзыв будет опубликован после прохождения модерации.
                        </p>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mb-6">
                            <label
                                class="block text-sm font-semibold mb-3"
                                style="color: #1F4234;"
                            >
                                Ваша оценка
                            </label>

                            <div class="flex items-center gap-2">
                                <button
                                    v-for="value in 5"
                                    :key="value"
                                    type="button"
                                    class="text-4xl transition-transform hover:scale-110 focus:outline-none"
                                    :class="value <= form.rating ? 'text-yellow-400' : 'text-gray-300'"
                                    :aria-label="`Оценка ${value} из 5`"
                                    @click="form.rating = value"
                                >
                                    ★
                                </button>
                            </div>

                            <p
                                v-if="form.errors.rating"
                                class="mt-2 text-sm"
                                style="color: #B3261E;"
                            >
                                {{ form.errors.rating }}
                            </p>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label
                                    for="comment"
                                    class="text-sm font-semibold"
                                    style="color: #1F4234;"
                                >
                                    Комментарий
                                </label>

                                <span class="text-xs text-gray-400">
                                    {{ form.comment.length }}/1000
                                </span>
                            </div>

                            <textarea
                                id="comment"
                                v-model="form.comment"
                                rows="6"
                                maxlength="1000"
                                required
                                placeholder="Расскажите о вашем опыте взаимодействия..."
                                class="w-full rounded-xl border-2 px-4 py-3 resize-y focus:outline-none"
                                style="border-color: #E8E3DA;"
                            ></textarea>

                            <p
                                v-if="form.errors.comment"
                                class="mt-2 text-sm"
                                style="color: #B3261E;"
                            >
                                {{ form.errors.comment }}
                            </p>
                        </div>

                        <div
                            v-if="form.errors.invite"
                            class="mb-5 rounded-xl px-4 py-3 text-sm"
                            style="background-color: #FDECEA; color: #B3261E;"
                        >
                            {{ form.errors.invite }}
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl px-5 py-3 text-white font-semibold transition-opacity disabled:opacity-50"
                            style="background-color: #315C47;"
                            :disabled="
                                form.processing
                                || form.rating < 1
                                || !form.comment.trim()
                            "
                        >
                            {{ form.processing ? 'Отправка…' : 'Отправить отзыв' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    invite: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    rating: 0,
    comment: '',
});

const submit = () => {
    form.post(window.location.href);
};
</script>
