<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h1 class="text-2xl font-bold mb-6" style="color: #1D1B20;">Редактировать объявление</h1>

                <form @submit.prevent="updateListing">
                    <div class="space-y-6">
                        <!-- Категория -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Категория</label>
                            <select v-model="form.category_id" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;" required>
                                <option value="">Выберите категорию</option>
                                <template v-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" :disabled="cat.children && cat.children.length > 0">{{ cat.name }}</option>
                                    <template v-if="cat.children">
                                        <option v-for="child in cat.children" :key="child.id" :value="child.id" class="pl-4">— {{ child.name }}</option>
                                        <template v-if="child.children">
                                            <option v-for="grandchild in child.children" :key="grandchild.id" :value="grandchild.id">&nbsp;&nbsp;&nbsp;&nbsp;— {{ grandchild.name }}</option>
                                        </template>
                                    </template>
                                </template>
                            </select>
                        </div>

                        <!-- Заголовок -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Заголовок</label>
                            <input v-model="form.title" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;" required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Описание</label>
                            <textarea v-model="form.description" rows="6" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;" required></textarea>
                        </div>

                        <!-- Цена и тип цены -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Цена</label>
                                <input v-model.number="form.price" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #49454F;">Тип цены</label>
                                <select v-model="form.price_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;" required>
                                    <option value="fixed">Фиксированная</option>
                                    <option value="hourly">За час</option>
                                    <option value="daily">За день</option>
                                    <option value="monthly">За месяц</option>
                                    <option value="negotiable">Договорная</option>
                                </select>
                            </div>
                        </div>

                        <!-- Локация -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Локация</label>
                            <input v-model="form.location" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                        </div>

                        <!-- === ДИНАМИЧЕСКИЕ ПОЛЯ (Те же ID категорий, что и в Create.vue) === -->
                        <div v-if="form.category_id == 1" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1D1B20;">Характеристики недвижимости</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #49454F;">Тип недвижимости</label>
                                    <select v-model="form.attributes.property_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                                        <option value="apartment">Квартира</option>
                                        <option value="house">Дом</option>
                                        <option value="land">Участок</option>
                                        <option value="commercial">Коммерческая</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #49454F;">Площадь (м²)</label>
                                    <input v-model.number="form.attributes.area" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                                </div>
                            </div>
                        </div>

                        <div v-if="form.category_id == 2" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1D1B20;">Характеристики транспорта</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #49454F;">Марка</label>
                                    <input v-model="form.attributes.brand" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #49454F;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                                </div>
                            </div>
                        </div>
                        <!-- ================================================================== -->

                        <!-- Текущие фотографии -->
                        <div v-if="form.images && form.images.length > 0">
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Текущие фотографии</label>
                            <div class="flex gap-3 flex-wrap">
                                <div v-for="(img, index) in form.images" :key="index" class="relative">
                                    <img :src="img" class="w-32 h-32 object-cover rounded-lg">
                                </div>
                            </div>
                            <p class="text-sm mt-2" style="color: #79747E;">Новые фотографии заменят текущие</p>
                        </div>

                        <!-- Новые фотографии -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Новые фотографии</label>
                            <input type="file" multiple accept="image/*" @change="handleImageUpload" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                        </div>

                        <!-- Кнопки -->
                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 px-4 py-3 rounded-xl text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);">
                                Сохранить изменения
                            </button>
                            <Link href="/user/listings" class="flex-1 px-4 py-3 rounded-xl font-medium border-2 transition-all hover:shadow-md text-center" style="border-color: #6750A4; color: #6750A4;">
                                Отмена
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    listing: Object,
    categories: Array
});

const form = ref({
    category_id: props.listing.category_id,
    title: props.listing.title,
    description: props.listing.description,
    price: props.listing.price,
    price_type: props.listing.price_type,
    location: props.listing.location || '',
    attributes: props.listing.attributes || {}, // <-- ДОБАВЛЕНО: инициализация из существующих данных
    images: props.listing.images || [],
});

const handleImageUpload = (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        form.value.newImages = files;
    }
};

const updateListing = () => {
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('price', form.value.price);
    formData.append('price_type', form.value.price_type);
    formData.append('location', form.value.location);
    
    // <-- ДОБАВЛЕНО: Отправляем attributes как JSON-строку
    formData.append('attributes', JSON.stringify(form.value.attributes));
    
    if (form.value.newImages) {
        for (let i = 0; i < form.value.newImages.length; i++) {
            formData.append(`images[]`, form.value.newImages[i]);
        }
    }
    
    // Используем POST с методом _method=PUT, так как Inertia лучше работает с FormData через POST для файлов
    formData.append('_method', 'PUT');
    
    router.post(`/user/listings/${props.listing.id}`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/user/listings');
        }
    });
};
</script>