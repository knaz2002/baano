<template>
    <AppLayout>
        <div class="container mx-auto px-6 py-8">
            <h1 class="text-3xl font-bold mb-6" style="color: #3D4449;">Редактировать объявление</h1>

            <form @submit.prevent="submit" class="glass p-6 rounded-2xl max-w-2xl mx-auto">
                <div class="space-y-6">
<!-- Категория -->
<div>
    <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Категория</label>
    <select v-model="form.category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
        <option value="">Выберите категорию</option>
        <template v-for="category in categories" :key="category.id">
            <option :value="category.id" style="font-weight: bold;">
                {{ category.name }}
            </option>
            <template v-if="category.children">
                <option v-for="child in category.children" :key="child.id" :value="child.id" style="padding-left: 20px;">
                    &nbsp;&nbsp;&nbsp;{{ child.name }}
                </option>
                <template v-if="child.children">
                    <option v-for="grandchild in child.children" :key="grandchild.id" :value="grandchild.id" style="padding-left: 40px;">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ grandchild.name }}
                    </option>
                </template>
            </template>
        </template>
    </select>
</div>
                    <!-- Заголовок -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Заголовок</label>
                        <input v-model="form.title" type="text" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>

                    <!-- Описание -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Описание</label>
                        <textarea v-model="form.description" rows="6" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>

                    <!-- Адрес -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Адрес</label>
                        <input 
                            v-model="form.location"
                            type="text" 
                            id="address-input"
                            placeholder="Начните вводить адрес..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-md"
                            autocomplete="off"
                        >
                    </div>

                    <!-- Цена и тип -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Цена</label>
                            <input v-model="form.price" type="number" required min="0" step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Тип цены</label>
                            <select v-model="form.price_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                <option value="fixed">Фиксированная</option>
                                <option value="hourly">За час</option>
                                <option value="daily">За сутки</option>
                                <option value="monthly">За месяц</option>
                                <option value="negotiable">Договорная</option>
                            </select>
                        </div>
                    </div>

                    <!-- Текущие фото -->
                    <div v-if="listing.images && listing.images.length > 0">
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Текущие фотографии</label>
                        <div class="grid grid-cols-3 gap-4">
                            <img v-for="(image, index) in listing.images" 
                                 :key="index"
                                 :src="image" 
                                 class="w-full h-32 object-cover rounded">
                        </div>
                        <p class="mt-2 text-sm" style="color: #8B9A9E;">Новые фотографии заменят текущие</p>
                    </div>

                    <!-- Новые фото -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: #3D4449;">Новые фотографии (макс. 10)</label>
                        <input type="file" @input="form.images = $event.target.files" multiple accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="mt-8 flex space-x-4">
                    <button type="submit" class="flex-1 btn-gradient">
                        Сохранить изменения
                    </button>
                    <Link href="/user/listings" class="px-6 py-3 border border-gray-300 rounded-md hover:bg-gray-50 text-center">
                        Отмена
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listing: Object,
    categories: Array,
});

const form = useForm({
    category_id: props.listing.category_id,
    title: props.listing.title,
    description: props.listing.description,
    price: props.listing.price,
    price_type: props.listing.price_type,
    location: props.listing.location,
    images: [],
});

const submit = () => {
    form.put(`/user/listings/${props.listing.id}`);
};
</script>
