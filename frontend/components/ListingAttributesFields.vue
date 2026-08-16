<template>
    <div class="space-y-6">
                        <!-- === НЕДВИЖИМОСТЬ (родитель ID: 1) === -->
                        <div v-if="parentCategoryId == 1" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики недвижимости</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип недвижимости</label>
                                    <select v-model="attributes.property_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="apartment">Квартира</option>
                                        <option value="house">Дом</option>
                                        <option value="land">Участок</option>
                                        <option value="commercial">Коммерческая</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Площадь (м²)</label>
                                    <input v-model.number="attributes.area" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Этаж</label>
                                    <input v-model.number="attributes.floor" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Комнат</label>
                                    <input v-model.number="attributes.rooms" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="rough">Черновая отделка</option>
                                        <option value="pre_finish">Предчистовая</option>
                                        <option value="finish">Чистовая</option>
                                        <option value="furnished">С мебелью</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <div class="flex items-center gap-2">
                                        <input v-model="attributes.furnished" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип топлива</label>
                                    <select v-model="attributes.fuel_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.transmission" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="manual">Механика</option>
                                        <option value="automatic">Автомат</option>
                                        <option value="cvt">Вариатор</option>
                                        <option value="robot">Робот</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Привод</label>
                                    <select v-model="attributes.drive" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="fwd">Передний</option>
                                        <option value="rwd">Задний</option>
                                        <option value="awd">Полный</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Объём двигателя (см³)</label>
                                    <input v-model.number="attributes.engine_capacity" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип</label>
                                    <select v-model="attributes.moto_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Пробег (км)</label>
                                    <input v-model.number="attributes.mileage" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Грузоподъёмность (т)</label>
                                    <input v-model.number="attributes.capacity" type="number" step="0.1" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип кузова</label>
                                    <select v-model="attributes.body_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Наработка (моточасы)</label>
                                    <input v-model.number="attributes.hours" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                            </div>
                        </div>

                        <!-- === СТРОИТЕЛЬНАЯ ТЕХНИКА (ID: 20) === -->
                        <div v-if="selectedCategoryId == 20" class="p-4 rounded-xl bg-gray-50 space-y-4">
                            <h3 class="font-semibold text-base" style="color: #1F4234;">Характеристики строительной техники</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Марка</label>
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите марку</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Год выпуска</label>
                                    <input v-model.number="attributes.year" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Наработка (моточасы)</label>
                                    <input v-model.number="attributes.hours" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Мощность (л.с.)</label>
                                    <input v-model.number="attributes.power" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип инструмента</label>
                                    <select v-model="attributes.tool_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.power_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="corded">Сетевой</option>
                                        <option value="cordless">Аккумуляторный</option>
                                        <option value="pneumatic">Пневматический</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Мощность (кВт)</label>
                                    <input v-model.number="attributes.power" type="number" step="0.1" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип топлива</label>
                                    <select v-model="attributes.fuel_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="petrol">Бензин</option>
                                        <option value="diesel">Дизель</option>
                                        <option value="gas">Газ</option>
                                        <option value="inverter">Инверторный</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Состояние</label>
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.brand" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Выберите производителя</option>
                                        <option v-for="brand in availableBrands" :key="brand" :value="brand">{{ brand }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Модель</label>
                                    <select v-model="attributes.model" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" :disabled="!attributes.brand">
                                        <option value="">Выберите модель</option>
                                        <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Тип техники</label>
                                    <select v-model="attributes.device_type" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <select v-model="attributes.condition" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
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
                                    <input v-model.number="attributes.experience_years" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Зона обслуживания</label>
                                    <input v-model="attributes.service_area" type="text" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;" placeholder="Например: Москва и МО">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">График работы</label>
                                    <select v-model="attributes.work_schedule" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                        <option value="">Не выбрано</option>
                                        <option value="full_time">Полный день</option>
                                        <option value="part_time">Частичная занятость</option>
                                        <option value="project">Проектная работа</option>
                                        <option value="24_7">24/7</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2" style="color: #68736B;">Гарантия (месяцев)</label>
                                    <input v-model.number="attributes.warranty_months" type="number" class="w-full px-4 py-3 rounded-xl border-2 focus:outline-none" style="border-color: #E8E3DA;">
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <input v-model="attributes.warranty" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
                                    <label class="text-sm font-medium" style="color: #68736B;">Предоставляю гарантию</label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model="attributes.emergency_service" type="checkbox" class="w-4 h-4 rounded" style="accent-color: #315C47;">
                                    <label class="text-sm font-medium" style="color: #68736B;">Срочный выезд</label>
                                </div>
                            </div>
                        </div>


    </div>
</template>

<script setup lang="ts">
import {
  carBrands,
  motorcycleBrands,
  truckBrands,
  constructionBrands,
  toolsBrands,
  generatorsBrands,
  photoVideoBrands,
} from '~/utils/vehicleData'

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    categoryId: {
        type: [String, Number],
        default: '',
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'update:modelValue',
]);

const attributes = ref({
    ...(props.modelValue || {}),
});

watch(
    () => props.modelValue,
    (value) => {
        const incoming = value || {};

        if (
            JSON.stringify(incoming)
            !== JSON.stringify(attributes.value)
        ) {
            attributes.value = {
                ...incoming,
            };
        }
    },
    {
        deep: true,
    }
);

watch(
    attributes,
    (value) => {
        emit('update:modelValue', {
            ...value,
        });
    },
    {
        deep: true,
    }
);

const currentYear = new Date().getFullYear();

const selectedCategoryId = computed(() => {
    return props.categoryId
        ? Number(props.categoryId)
        : null;
});

const parentCategoryId = computed(() => {
    const categoryId = selectedCategoryId.value;

    if (!categoryId) {
        return null;
    }

    for (const category of props.categories || []) {
        if (Number(category?.id) === categoryId) {
            return Number(category.id);
        }

        for (const child of category?.children || []) {
            if (Number(child?.id) === categoryId) {
                return Number(category.id);
            }

            for (
                const grandchild
                of child?.children || []
            ) {
                if (
                    Number(grandchild?.id)
                    === categoryId
                ) {
                    return Number(category.id);
                }
            }
        }
    }

    return null;
});

const brandsMap = computed(() => {
    const categoryId = selectedCategoryId.value;

    if (categoryId === 15) {
        return carBrands;
    }

    if (categoryId === 16) {
        return motorcycleBrands;
    }

    if (categoryId === 17) {
        return truckBrands;
    }

    if ([18, 20].includes(categoryId)) {
        return constructionBrands;
    }

    if (categoryId === 21) {
        return toolsBrands;
    }

    if (categoryId === 22) {
        return generatorsBrands;
    }

    if (categoryId === 23) {
        return photoVideoBrands;
    }

    return {};
});

const availableBrands = computed(() => {
    return Object.keys(brandsMap.value).sort();
});

const availableModels = computed(() => {
    const brand = attributes.value.brand;

    if (!brand) {
        return [];
    }

    return [
        ...(brandsMap.value[brand] || []),
    ].sort();
});

watch(
    () => attributes.value.brand,
    (brand, previousBrand) => {
        if (
            previousBrand !== undefined
            && brand !== previousBrand
        ) {
            attributes.value.model = '';
        }
    }
);

watch(
    () => props.categoryId,
    (categoryId, previousCategoryId) => {
        if (
            previousCategoryId !== undefined
            && categoryId !== previousCategoryId
        ) {
            attributes.value.brand = '';
            attributes.value.model = '';
        }
    }
);
</script>
