<x-filament-panels::page>
    @php
        $counts = $this->getQueueCounts();
    @endphp

    <style>
        .moderation-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .moderation-card,
        .moderation-info {
            display: block;
            padding: 1.5rem;
            border: 1px solid rgba(156, 163, 175, 0.25);
            border-radius: 0.75rem;
            background: rgba(31, 41, 55, 0.45);
        }

        .moderation-card {
            text-decoration: none;
            transition: border-color 0.2s, transform 0.2s;
        }

        .moderation-card:hover {
            border-color: rgb(245, 158, 11);
            transform: translateY(-2px);
        }

        .moderation-label {
            font-size: 0.875rem;
            color: rgb(156, 163, 175);
        }

        .moderation-count {
            margin-top: 0.5rem;
            font-size: 2rem;
            line-height: 1;
            font-weight: 700;
            color: white;
        }

        .moderation-link {
            margin-top: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(245, 158, 11);
        }

        .moderation-info {
            margin-top: 1.5rem;
        }

        .moderation-info h2 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
        }

        .moderation-info p {
            margin: 0.75rem 0 0;
            line-height: 1.6;
            color: rgb(209, 213, 219);
        }

        @media (max-width: 768px) {
            .moderation-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="moderation-grid">
        <a
            href="{{ \App\Filament\Admin\Resources\Listings\ListingResource::getUrl('index') }}"
            class="moderation-card"
        >
            <div class="moderation-label">Объявления</div>
            <div class="moderation-count">{{ $counts['listings'] }}</div>
            <div class="moderation-link">Открыть очередь →</div>
        </a>

        <a
            href="{{ \App\Filament\Admin\Resources\Reviews\ReviewResource::getUrl('index') }}"
            class="moderation-card"
        >
            <div class="moderation-label">Отзывы</div>
            <div class="moderation-count">{{ $counts['reviews'] }}</div>
            <div class="moderation-link">Открыть очередь →</div>
        </a>

        <a
            href="{{ \App\Filament\Admin\Resources\Users\UserResource::getUrl('index') }}"
            class="moderation-card"
        >
            <div class="moderation-label">Профили</div>
            <div class="moderation-count">{{ $counts['profiles'] }}</div>
            <div class="moderation-link">Открыть очередь →</div>
        </a>
    </div>

    <div class="moderation-info">
        <h2>Принцип обработки</h2>

        <p>
            В очереди отображаются материалы со статусами «На модерации»
            и «Ручная проверка». Полная история автоматических и ручных
            решений находится в разделе «История модерации».
        </p>
    </div>
</x-filament-panels::page>
