<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h1 class="text-2xl font-bold mb-6" style="color: #1F4234;">Создать объявление</h1>

                <form @submit.prevent="createListing">
                    <div class="space-y-6">
                        <!-- Категория -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Категория</label>
                            <select v-model="form.category_id" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" required>
                                <option value="">Выберите категорию</option>
                                <template v-for="cat in safeCategories" :key="cat.id">
                                    <option :value="cat.id" :disabled="hasChildren(cat)">{{ cat.name }}</option>
                                    <template v-if="hasChildren(cat)">
                                        <option v-for="child in safeChildren(cat)" :key="child.id" :value="child.id">— {{ child.name }}</option>
                                        <template v-if="hasChildren(child)">
                                            <option v-for="grandchild in safeChildren(child)" :key="grandchild.id" :value="grandchild.id">&nbsp;&nbsp;&nbsp;&nbsp;— {{ grandchild.name }}</option>
                                        </template>
                                    </template>
                                </template>
                            </select>
                        </div>

                        <!-- Заголовок -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Заголовок</label>
                            <input v-model="form.title" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Описание</label>
                            <textarea v-model="form.description" rows="6" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" required></textarea>
                        </div>

                        <!-- Цена и Тип цены -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #68736B;">Цена</label>
                                <input v-model.number="form.price" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип цены</label>
                                <select v-model="form.price_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" required>
                                    <option value="fixed">Фиксированная</option>
                                    <option value="hourly">За час</option>
                                    <option value="daily">За день</option>
                                    <option value="monthly">За месяц</option>
                                    <option value="negotiable">Договорная</option>
                                </select>
                            </div>
                        </div>

                        <!-- Локация (DaData) -->
                        <div class="relative" style="z-index: 100;">
                            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Локация (Адрес)</label>
                            <input v-model="locationQuery" @input="onLocationInput" @focus="showSuggestions = true" @blur="closeSuggestions" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" placeholder="Начните вводить адрес..." autocomplete="off">
                            <div v-if="showSuggestions && locationSuggestions.length > 0" class="absolute z-[9999] w-full mt-1 bg-white rounded-xl shadow-2xl border max-h-60 overflow-y-auto" style="border-color: #E8E3DA;">
                                <button v-for="(suggestion, index) in locationSuggestions" :key="index" @mousedown.prevent="selectSuggestion(suggestion)" @touchstart.prevent="selectSuggestion(suggestion)" type="button" class="w-full text-left px-4 py-3 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b last:border-0" style="border-color: #E8E3DA;">
                                    <p class="text-sm font-medium" style="color: #1F4234;">{{ suggestion.value }}</p>
                                    <p v-if="suggestion.data && suggestion.data.city_with_type" class="text-xs mt-1" style="color: #7B817D;">{{ suggestion.data.region_with_type }}</p>
                                </button>
                            </div>
                        </div>

                        <!-- === НЕДВИЖИМОСТЬ (родитель ID: 1) === -->
                        <div v-if="parentCategoryId == 1" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики недвижимости</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип недвижимости</label>
                                    <select v-model="form.attributes.property_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="apartment">Квартира</option>
                                        <option value="house">Дом</option>
                                        <option value="land">Участок</option>
                                        <option value="commercial">Коммерческая</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Площадь (м²)</label>
                                    <input v-model.number="form.attributes.area" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Этаж</label>
                                    <input v-model.number="form.attributes.floor" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Комнат</label>
                                    <input v-model.number="form.attributes.rooms" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="rough">Черновая отделка</option>
                                        <option value="pre_finish">Предчистовая</option>
                                        <option value="finish">Чистовая</option>
                                        <option value="furnished">С мебелью</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.attributes.furnished" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
                                        <label class="text-sm font-medium" style="color: #68736B;">Меблирована</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- === ТРАНСПОРТ (ID: 15) === -->
                        <div v-if="selectedCategoryId == 15" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики легкового автомобиля</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="form.attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип топлива</label>
                                    <select v-model="form.attributes.fuel_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="petrol">Бензин</option>
                                        <option value="diesel">Дизель</option>
                                        <option value="electric">Электро</option>
                                        <option value="hybrid">Гибрид</option>
                                        <option value="gas">Газ</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Коробка передач</label>
                                    <select v-model="form.attributes.transmission" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="manual">Механика</option>
                                        <option value="automatic">Автомат</option>
                                        <option value="cvt">Вариатор</option>
                                        <option value="robot">Робот</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Привод</label>
                                    <select v-model="form.attributes.drive" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="fwd">Передний</option>
                                        <option value="rwd">Задний</option>
                                        <option value="awd">Полный</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="new">Новое</option>
                                        <option value="used">Б/у</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === МОТОЦИКЛЫ (ID: 16) === -->
                        <div v-if="selectedCategoryId == 16" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики мотоцикла</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="form.attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Объём двигателя (см³)</label>
                                    <input v-model.number="form.attributes.engine_capacity" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип</label>
                                    <select v-model="form.attributes.moto_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="sport">Спортивный</option>
                                        <option value="touring">Туристический</option>
                                        <option value="cruiser">Круизер</option>
                                        <option value="enduro">Эндуро</option>
                                        <option value="scooter">Скутер</option>
                                        <option value="naked">Нейкед</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === ГРУЗОВОЙ ТРАНСПОРТ (ID: 17) === -->
                        <div v-if="selectedCategoryId == 17" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики грузового транспорта</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="form.attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Грузоподъёмность (т)</label>
                                    <input v-model.number="form.attributes.capacity" type="number" step="0.1" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип кузова</label>
                                    <select v-model="form.attributes.body_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="tent">Тент</option>
                                        <option value="refrigerator">Рефрижератор</option>
                                        <option value="van">Фургон</option>
                                        <option value="flatbed">Бортовой</option>
                                        <option value="dump">Самосвал</option>
                                        <option value="container">Контейнеровоз</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === СПЕЦТРАНСПОРТ (ID: 18) === -->
                        <div v-if="selectedCategoryId == 18" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики спецтранспорта</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Наработка (моточасы)</label>
                                    <input v-model.number="form.attributes.hours" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                            </div>
                        </div>

                        <!-- === СТРОИТЕЛЬНАЯ ТЕХНИКА (ID: 20) === -->
                        <div v-if="selectedCategoryId == 20" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики строительной техники</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="form.attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Наработка (моточасы)</label>
                                    <input v-model.number="form.attributes.hours" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Мощность (л.с.)</label>
                                    <input v-model.number="form.attributes.power" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="new">Новое</option>
                                        <option value="used_excellent">Б/у отличное</option>
                                        <option value="used_good">Б/у хорошее</option>
                                        <option value="used_fair">Б/у удовлетворительное</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === ИНСТРУМЕНТЫ (ID: 21) === -->
                        <div v-if="selectedCategoryId == 21" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики инструмента</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Производитель</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип инструмента</label>
                                    <select v-model="form.attributes.tool_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="drill">Дрель / Шуруповерт</option>
                                        <option value="grinder">Болгарка (УШМ)</option>
                                        <option value="saw">Пила</option>
                                        <option value="hammer">Перфоратор</option>
                                        <option value="other">Другое</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Питание</label>
                                    <select v-model="form.attributes.power_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="corded">Сетевой</option>
                                        <option value="cordless">Аккумуляторный</option>
                                        <option value="pneumatic">Пневматический</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="new">Новый</option>
                                        <option value="used_excellent">Б/у отличное</option>
                                        <option value="used_good">Б/у хорошее</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === ГЕНЕРАТОРЫ (ID: 22) === -->
                        <div v-if="selectedCategoryId == 22" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики генератора</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Производитель</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Мощность (кВт)</label>
                                    <input v-model.number="form.attributes.power" type="number" step="0.1" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип топлива</label>
                                    <select v-model="form.attributes.fuel_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="petrol">Бензин</option>
                                        <option value="diesel">Дизель</option>
                                        <option value="gas">Газ</option>
                                        <option value="inverter">Инверторный</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="new">Новый</option>
                                        <option value="used_excellent">Б/у отличное</option>
                                        <option value="used_good">Б/у хорошее</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === ФОТО И ВИДЕОТЕХНИКА (ID: 23) === -->
                        <div v-if="selectedCategoryId == 23" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики фото/видеотехники</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Производитель</label>
                                    <select v-model="form.attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="form.attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!form.attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип техники</label>
                                    <select v-model="form.attributes.device_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="camera">Фотоаппарат</option>
                                        <option value="video">Видеокамера</option>
                                        <option value="lens">Объектив</option>
                                        <option value="action">Экшн-камера</option>
                                        <option value="light">Свет</option>
                                        <option value="accessories">Аксессуары</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="form.attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="new">Новый</option>
                                        <option value="used_excellent">Б/у отличное</option>
                                        <option value="used_good">Б/у хорошее</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- === УСЛУГИ (родитель ID: 24) === -->
                        <div v-if="parentCategoryId == 24" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Детали услуги</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Стаж работы (лет)</label>
                                    <input v-model.number="form.attributes.experience_years" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Зона обслуживания</label>
                                    <input v-model="form.attributes.service_area" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" placeholder="Например: Москва и МО">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">График работы</label>
                                    <select v-model="form.attributes.work_schedule" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="full_time">Полный день</option>
                                        <option value="part_time">Частичная занятость</option>
                                        <option value="project">Проектная работа</option>
                                        <option value="24_7">24/7</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Гарантия (месяцев)</label>
                                    <input v-model.number="form.attributes.warranty_months" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <input v-model="form.attributes.warranty" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
                                    <label class="text-sm font-medium" style="color: #68736B;">Предоставляю гарантию</label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.attributes.emergency_service" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
                                    <label class="text-sm font-medium" style="color: #68736B;">Срочный выезд</label>
                                </div>
                            </div>
                        </div>

                        <!-- Фотографии -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #68736B;">Фотографии</label>
                            <input type="file" multiple accept="image/*" @change="handleImageUpload" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                            <p class="text-sm mt-2" style="color: #7B817D;">Максимум 10 фотографий</p>
                        </div>

                        <!-- Кнопки -->
                        <div class="flex flex-col gap-3">
                            <button type="submit" class="w-full py-3.5 rounded-xl text-white font-semibold text-base transition-all hover:shadow-lg active:scale-95 confirm-action action-green" style="background-color: #315C47;" :disabled="form.processing">
                                {{ form.processing ? 'Отправка...' : 'Создать' }}
                            </button>
                            <Link href="/user/listings" class="w-full py-3.5 rounded-xl font-semibold text-base border-2 transition-all hover:shadow-md text-center cancel-action action-red" style="border-color: #315C47; color: #315C47;">Отмена</Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// ЕДИНЫЙ ИМПОРТ ВСЕХ ДАННЫХ
import {
    carBrands,
    motorcycleBrands,
    truckBrands,
    constructionBrands,
    toolsBrands,
    generatorsBrands,
    photoVideoBrands
} from '@/vehicleData.js';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});

const currentYear = new Date().getFullYear();

const form = ref({
    category_id: '',
    title: '',
    description: '',
    price: 0,
    price_type: 'fixed',
    location: '',
    city: '',
    attributes: {
        brand: '',
        model: ''
    },
    images: [],
    errors: {},
    processing: false
});

const locationQuery = ref('');
const locationSuggestions = ref([]);
const showSuggestions = ref(false);
const showSuccessModal = ref(false);
let locationTimeout = null;

// === БЕЗОПАСНЫЕ HELPER-ФУНКЦИИ ===
const safeCategories = computed(() => {
    if (!props.categories || !Array.isArray(props.categories)) return [];
    return props.categories.filter(cat => cat != null);
});

const hasChildren = (category) => {
    if (!category) return false;
    if (!category.children) return false;
    if (!Array.isArray(category.children)) return false;
    return category.children.length > 0;
};

const safeChildren = (category) => {
    if (!category || !category.children || !Array.isArray(category.children)) return [];
    return category.children.filter(child => child != null);
};

// === ВЫЧИСЛЯЕМОЕ СВОЙСТВО: Определяет родительскую категорию ===
const parentCategoryId = computed(() => {
    const catId = form.value.category_id;
    if (!catId || !props.categories || !Array.isArray(props.categories)) return null;

    for (const cat of props.categories) {
        if (!cat) continue;
        if (cat.id == catId) return cat.id;

        if (cat.children && Array.isArray(cat.children)) {
            for (const child of cat.children) {
                if (!child) continue;
                if (child.id == catId) return cat.id;

                if (child.children && Array.isArray(child.children)) {
                    for (const grandchild of child.children) {
                        if (grandchild && grandchild.id == catId) return cat.id;
                    }
                }
            }
        }
    }
    return null;
});

// === ВЫЧИСЛЯЕМОЕ СВОЙСТВО: Текущий выбранный ID категории ===
const selectedCategoryId = computed(() => {
    return form.value.category_id ? Number(form.value.category_id) : null;
});

// === ЛОГИКА МАРКИ И МОДЕЛИ ===
const availableBrands = computed(() => {
    const catId = selectedCategoryId.value;
    if (catId == 15) return Object.keys(carBrands).sort();
    if (catId == 16) return Object.keys(motorcycleBrands).sort();
    if (catId == 17) return Object.keys(truckBrands).sort();
    if (catId == 18 || catId == 20) return Object.keys(constructionBrands).sort();
    if (catId == 21) return Object.keys(toolsBrands).sort();
    if (catId == 22) return Object.keys(generatorsBrands).sort();
    if (catId == 23) return Object.keys(photoVideoBrands).sort();
    return [];
});

const availableModels = computed(() => {
    const brand = form.value.attributes.brand;
    const catId = selectedCategoryId.value;

    if (!brand) return [];

    let brandsMap = {};
    if (catId == 15) brandsMap = carBrands;
    else if (catId == 16) brandsMap = motorcycleBrands;
    else if (catId == 17) brandsMap = truckBrands;
    else if (catId == 18 || catId == 20) brandsMap = constructionBrands;
    else if (catId == 21) brandsMap = toolsBrands;
    else if (catId == 22) brandsMap = generatorsBrands;
    else if (catId == 23) brandsMap = photoVideoBrands;

    return brandsMap[brand] ? brandsMap[brand].sort() : [];
});

// При смене марки сбрасываем модель
watch(() => form.value.attributes.brand, () => {
    form.value.attributes.model = '';
});

// При смене категории сбрасываем марку и модель
watch(() => form.value.category_id, () => {
    form.value.attributes.brand = '';
    form.value.attributes.model = '';
});

const extractCityFromSuggestion = (suggestion) => {
    const data = suggestion?.data || {};

    return (
        data.city
        || data.settlement
        || data.area
        || ''
    );
};

const onLocationInput = () => {

    form.value.city = '';
    clearTimeout(locationTimeout);
    if (locationQuery.value.length < 3) { locationSuggestions.value = []; return; }
    locationTimeout = setTimeout(async () => {
        try {
            const token = import.meta.env.VITE_DADATA_TOKEN;
            if (!token) return;
            const response = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Token ${token}` },
                body: JSON.stringify({ query: locationQuery.value, count: 5 })
            });
            const data = await response.json();
            locationSuggestions.value = data.suggestions || [];
        } catch (error) { console.error('DaData error:', error); }
    }, 300);
};

const selectSuggestion = (suggestion) => {
    locationQuery.value = suggestion.value;
    form.value.location = suggestion.value;
    form.value.city = extractCityFromSuggestion(suggestion);
    locationSuggestions.value = [];
    showSuggestions.value = false;
    if (typeof window !== 'undefined') document.activeElement.blur();
};

const closeSuggestions = () => { setTimeout(() => { showSuggestions.value = false; }, 300); };
watch(locationQuery, (val) => { form.value.location = val; });

const closeSuccessModal = () => { showSuccessModal.value = false; router.visit('/user/listings'); };

const handleImageUpload = (event) => { const files = event.target.files; if (files.length > 0) form.value.images = files; };

const createListing = () => {
    form.value.processing = true;
    form.value.errors = {};
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    formData.append('title', form.value.title);
    formData.append('description', form.value.description);
    formData.append('price', form.value.price);
    formData.append('price_type', form.value.price_type);
    formData.append('location', form.value.location);
    formData.append('city', form.value.city || '');
    formData.append('attributes', JSON.stringify(form.value.attributes));

    if (form.value.images) {
        for (let i = 0; i < form.value.images.length; i++) {
            formData.append(`images[]`, form.value.images[i]);
        }
    }

    router.post('/user/listings', formData, {
        forceFormData: true,
        onSuccess: () => { showSuccessModal.value = true; },
        onError: (errors) => { form.value.errors = errors; },
        onFinish: () => { form.value.processing = false; }
    });
};
</script>
