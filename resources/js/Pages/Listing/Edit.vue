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
                          <!-- Город -->
                          <div>
                              <label
                                  class="block text-sm font-medium mb-2"
                                  style="color: #49454F;"
                              >
                                  Город
                              </label>

                              <input
                                  v-model="form.city"
                                  type="text"
                                  maxlength="120"
                                  placeholder="Например, Москва"
                                  class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none"
                                  style="border-color: #E7E0EC;"
                              >
                          </div>



                        <!-- Локация -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #49454F;">Локация</label>
                            <input v-model="form.location" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E7E0EC;">
                        </div>

                          <!-- Характеристики категории -->
                          <ListingAttributesFields
                              v-model="form.attributes"
                              :category-id="form.category_id"
                              :categories="categories"
                          />

                          <!-- Текущие фотографии -->
                          <div v-if="form.images.length > 0">
                              <label
                                  class="block text-sm font-medium mb-2"
                                  style="color: #49454F;"
                              >
                                  Текущие фотографии
                              </label>

                              <div class="flex gap-3 flex-wrap">
                                  <div
                                      v-for="image in form.images"
                                      :key="image.id"
                                      class="relative"
                                  >
                                      <img
                                          :src="image.url"
                                          class="w-32 h-32 object-cover rounded-lg"
                                      >

                                      <button
                                          type="button"
                                          title="Удалить изображение"
                                          aria-label="Удалить изображение"
                                          :disabled="!canRemoveExistingImage()"
                                          class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full text-xl font-bold text-white shadow-md transition-transform hover:scale-110 disabled:cursor-not-allowed disabled:opacity-40"
                                          style="background-color: #DC2626;"
                                          @click="removeExistingImage(image.id)"
                                      >
                                          ×
                                      </button>
                                  </div>
                              </div>

                              <p
                                  class="text-sm mt-2"
                                  style="color: #79747E;"
                              >
                                  В объявлении должна оставаться минимум одна фотография
                              </p>
                          </div>

                          <p
                              v-if="imageError"
                              class="text-sm"
                              style="color: #DC2626;"
                          >
                              {{ imageError }}
                          </p>

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
import ListingAttributesFields from '@/Components/ListingAttributesFields.vue';

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
    city: props.listing.city || '',
    attributes: {
        ...(props.listing.attributes || {}),
    },
    images: props.listing.images || [],
    removed_media_ids: [],
});

const imageError = ref('');

const newImagesCount = () => {
    return form.value.newImages?.length || 0;
};

const canRemoveExistingImage = () => {
    return (
        form.value.images.length
        + newImagesCount()
    ) > 1;
};

const removeExistingImage = (mediaId) => {
    if (!canRemoveExistingImage()) {
        imageError.value =
            'Нельзя удалить последнюю фотографию.';
        return;
    }

    if (!form.value.removed_media_ids.includes(mediaId)) {
        form.value.removed_media_ids.push(mediaId);
    }

    form.value.images = form.value.images.filter(
        image => image.id !== mediaId
    );

    imageError.value = '';
};

const handleImageUpload = (event) => {
    form.value.newImages = Array.from(
        event.target.files || []
    );

    imageError.value = '';
};

const updateListing = () => {
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('price', form.value.price);
    formData.append('price_type', form.value.price_type);
    formData.append('location', form.value.location);
    formData.append('city', form.value.city || '');
    
    // <-- ДОБАВЛЕНО: Отправляем attributes как JSON-строку
    formData.append('attributes', JSON.stringify(form.value.attributes));

    form.value.removed_media_ids.forEach((mediaId) => {
        formData.append('removed_media_ids[]', mediaId);
    });
    
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