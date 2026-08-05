<x-filament-panels::page>
    @php
        $counts = $this->getQueueCounts();
    @endphp

    <div class="grid gap-4 md:grid-cols-3">
        <a
            href="{{ \App\Filament\Admin\Resources\Listings\ListingResource::getUrl('index') }}"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="text-sm font-medium text-gray-500">
                Объявления
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $counts['listings'] }}
            </div>

            <div class="mt-2 text-sm text-primary-600">
                Открыть очередь
            </div>
        </a>

        <a
            href="{{ \App\Filament\Admin\Resources\Reviews\ReviewResource::getUrl('index') }}"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="text-sm font-medium text-gray-500">
                Отзывы
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $counts['reviews'] }}
            </div>

            <div class="mt-2 text-sm text-primary-600">
                Открыть очередь
            </div>
        </a>

        <a
            href="{{ \App\Filament\Admin\Resources\Users\UserResource::getUrl('index') }}"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="text-sm font-medium text-gray-500">
                Профили
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $counts['profiles'] }}
            </div>

            <div class="mt-2 text-sm text-primary-600">
                Открыть очередь
            </div>
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-semibold">
            Принцип обработки
        </h2>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            В очереди отображаются материалы со статусами
            «На модерации» и «Ручная проверка».
            Полная история автоматических и ручных решений
            находится в разделе «История модерации».
        </p>
    </div>
</x-filament-panels::page>
