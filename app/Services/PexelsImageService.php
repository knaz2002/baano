<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PexelsImageService
{
    /**
     * Уже использованные фотографии Pexels.
     */
    private array $usedPhotoIds = [];

    /**
     * Уже использованное содержимое файлов.
     */
    private array $usedContentHashes = [];

    /**
     * Очереди найденных фотографий для каждого запроса.
     */
    private array $photoQueues = [];

    /**
     * Следующая страница поиска для каждого запроса.
     */
    private array $nextPages = [];

    /**
     * Получить уникальную реалистичную фотографию объявления.
     *
     * @return array{
     *     photo_id:int,
     *     query:string,
     *     content:string,
     *     content_hash:string,
     *     source_url:string,
     *     pexels_url:?string,
     *     photographer:?string,
     *     photographer_url:?string
     * }
     */
    public function fetchUniquePhoto(
        string $title,
        string $imageKey
    ): array {
        $apiKey = (string) config('services.pexels.key');

        if ($apiKey === '') {
            throw new RuntimeException(
                'PEXELS_API_KEY не настроен в config/services.php'
            );
        }

        foreach ($this->buildQueries($title, $imageKey) as $query) {
            for ($attempt = 0; $attempt < 160; $attempt++) {
                $photo = $this->nextCandidate($apiKey, $query);

                if ($photo === null) {
                    break;
                }

                $photoId = (int) ($photo['id'] ?? 0);

                if (
                    $photoId <= 0 ||
                    isset($this->usedPhotoIds[$photoId])
                ) {
                    continue;
                }

                $sourceUrl =
                    $photo['src']['large2x']
                    ?? $photo['src']['large']
                    ?? $photo['src']['original']
                    ?? null;

                if (!is_string($sourceUrl) || $sourceUrl === '') {
                    continue;
                }

                $content = $this->downloadImage($sourceUrl);

                if ($content === null) {
                    continue;
                }

                $contentHash = hash('sha256', $content);

                if (isset($this->usedContentHashes[$contentHash])) {
                    continue;
                }

                $imageInfo = @getimagesizefromstring($content);

                if ($imageInfo === false) {
                    continue;
                }

                $width = (int) ($imageInfo[0] ?? 0);
                $height = (int) ($imageInfo[1] ?? 0);

                // Используем только качественные горизонтальные изображения.
                if (
                    $width < 1200 ||
                    $height < 800 ||
                    $width <= $height
                ) {
                    continue;
                }

                $this->usedPhotoIds[$photoId] = true;
                $this->usedContentHashes[$contentHash] = true;

                return [
                    'photo_id' => $photoId,
                    'query' => $query,
                    'content' => $content,
                    'content_hash' => $contentHash,
                    'source_url' => $sourceUrl,
                    'pexels_url' => $photo['url'] ?? null,
                    'photographer' => $photo['photographer'] ?? null,
                    'photographer_url' =>
                        $photo['photographer_url'] ?? null,
                ];
            }
        }

        throw new RuntimeException(
            "Не удалось подобрать уникальную фотографию для: {$title}"
        );
    }

    /**
     * Получить следующую фотографию из результатов поиска.
     */
    private function nextCandidate(
        string $apiKey,
        string $query
    ): ?array {
        if (!isset($this->photoQueues[$query])) {
            $this->photoQueues[$query] = [];
            $this->nextPages[$query] = 1;
        }

        for ($pageAttempt = 0; $pageAttempt < 6; $pageAttempt++) {
            if ($this->photoQueues[$query] !== []) {
                return array_shift($this->photoQueues[$query]);
            }

            $page = $this->nextPages[$query];

            try {
                $response = Http::withHeaders([
                    'Authorization' => $apiKey,
                ])
                    ->acceptJson()
                    ->timeout(30)
                    ->get('https://api.pexels.com/v1/search', [
                        'query' => $query,
                        'orientation' => 'landscape',
                        'size' => 'large',
                        'per_page' => 80,
                        'page' => $page,
                    ]);
            } catch (\Throwable $exception) {
                throw new RuntimeException(
                    'Ошибка соединения с Pexels: ' .
                    $exception->getMessage(),
                    previous: $exception
                );
            }

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Pexels API вернул HTTP ' . $response->status()
                );
            }

            $photos = $response->json('photos', []);

            if (!is_array($photos) || $photos === []) {
                return null;
            }

            $this->photoQueues[$query] = array_values($photos);
            $this->nextPages[$query] = $page + 1;
        }

        return null;
    }

    /**
     * Скачать изображение с CDN Pexels.
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(40)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $content = $response->body();

        if (strlen($content) < 50000) {
            return null;
        }

        $contentType = strtolower(
            (string) $response->header('Content-Type')
        );

        if (
            $contentType !== '' &&
            !str_starts_with($contentType, 'image/')
        ) {
            return null;
        }

        return $content;
    }

    /**
     * Точные поисковые запросы по названию объявления.
     */
    private function buildQueries(
        string $title,
        string $imageKey
    ): array {
        $titleLower = mb_strtolower(trim($title));

        $rules = [
            // ТОЧНЫЕ ЗАПРОСЫ ДЛЯ КАТЕГОРИЙ SEEDER
            // Более конкретные правила должны идти раньше общих.

            // IT И ДИЗАЙН
            'разработка сайта' => 'web developer coding website',
            'интернет-магазин' => 'ecommerce website developer',
            'логотип' => 'graphic designer logo branding',
            'ux/ui' => 'ui ux designer mobile app',

            // БАНКЕТЫ И МЕРОПРИЯТИЯ
            'банкетного зала' => 'banquet hall interior',
            'банкетный зал' => 'banquet hall interior',
            'зал для свадьбы' => 'wedding reception hall',
            'зал для юбилея' => 'banquet event hall',
            'конференц-зала' => 'conference room interior',
            'конференц-зал' => 'conference room interior',
            'зал для семинаров' => 'training seminar room',
            'переговорная комната' => 'meeting room interior',
            'площадка для семейного праздника' => 'outdoor family event venue',
            'беседки' => 'wooden gazebo outdoor',
            'зоны барбекю' => 'outdoor barbecue area',
            'домик для отдыха' => 'small vacation cabin exterior',

            // ГАРАЖИ, ПАРКОВКИ И ХРАНЕНИЕ
            'подземном паркинге' => 'underground parking garage',
            'машиноместа' => 'indoor parking space',
            'гаража' => 'private garage interior',
            'тёплый гараж' => 'heated private garage interior',
            'бокса для хранения' => 'self storage unit interior',
            'складской контейнер' => 'shipping container storage',
            'место для хранения автомобиля' => 'secure vehicle storage',
            'склада-бокса' => 'storage unit warehouse',

            // КОММЕРЧЕСКИЕ И ТОРГОВЫЕ ПОМЕЩЕНИЯ
            'помещения свободного назначения' => 'empty commercial space interior',
            'коммерческое помещение' => 'commercial retail space interior',
            'помещение под офис или услуги' => 'small commercial office interior',
            'помещения на первой линии' => 'street retail storefront',
            'магазина 60' => 'small retail store interior',
            'торговое помещение' => 'retail commercial space interior',
            'павильон в торговом центре' => 'shopping mall retail store',
            'продуктовый магазин' => 'grocery store interior',

            // СПЕЦТРАНСПОРТ
            'автовышки' => 'aerial work platform truck',
            'эвакуатора' => 'tow truck roadside',
            'манипулятора' => 'truck mounted crane',
            'вакуумная машина' => 'vacuum sewage truck',

            'генератор' => 'portable gasoline generator',
            'перфоратор' => 'rotary hammer power tool',
            'шуруповерт' => 'cordless drill power tool',
            'болгарка' => 'angle grinder power tool',
            'дрель' => 'electric drill power tool',
            'лобзик' => 'electric jigsaw power tool',
            'циркуляр' => 'circular saw power tool',
            'шлифмашин' => 'angle grinder workshop',
            'краскопульт' => 'paint spray gun tool',
            'набор инструментов' => 'professional mechanic tool set',

            'склад' => 'warehouse interior storage',
            'коворкинг' => 'coworking office interior',
            'офис' => 'modern office interior',

            '1-комнат' => 'modest one bedroom apartment interior',
            '2-комнат' => 'two bedroom apartment interior',
            '3-комнат' => 'three bedroom apartment interior',
            'студия' => 'small studio apartment interior',
            'апартаменты' => 'modern apartment interior',
            'квартира' => 'ordinary apartment interior',

            'коттедж' => 'suburban cottage exterior',
            'дачный дом' => 'small country house exterior',
            'загородный дом' => 'suburban family house exterior',
            'таунхаус' => 'townhouse exterior',

            'газель' => 'white cargo delivery van',
            'камаз' => 'dump truck construction',
            'volvo fh' => 'semi truck highway',
            'scania' => 'semi truck highway',
            'actros' => 'semi truck highway',
            'ford transit' => 'cargo van',
            'isuzu' => 'refrigerated delivery truck',
            'daf' => 'semi truck highway',

            'toyota camry' => 'Toyota Camry car',
            'bmw x5' => 'BMW X5 car',
            'hyundai solaris' => 'compact sedan car',
            'kia rio' => 'compact sedan car',
            'audi a6' => 'Audi sedan car',
            'volkswagen tiguan' => 'SUV car',
            'mazda cx-5' => 'SUV car',
            'nissan qashqai' => 'SUV car',
            'skoda octavia' => 'station wagon car',

            'мотоцикл' => 'sport motorcycle',
            'yamaha' => 'sport motorcycle',
            'kawasaki' => 'sport motorcycle',
            'suzuki' => 'sport motorcycle',
            'harley' => 'cruiser motorcycle',
            'ducati' => 'sport motorcycle',

            'экскаватор' => 'excavator construction machine',
            'бульдозер' => 'bulldozer construction machine',
            'погрузчик' => 'wheel loader construction machine',
            'автокран' => 'mobile crane construction',
            'бетоносмеситель' => 'concrete mixer truck',
            'буровая' => 'drilling rig construction',
            'грейдер' => 'road grader construction',
            'каток' => 'road roller construction',

            'фотоаппарат' => 'professional photo camera',
            'видеокамер' => 'professional video camera',
            'объектив' => 'camera lens photography',
            'штатив' => 'camera tripod photography',
            'вспышка' => 'camera flash photography',

            'ремонт квартир' => 'apartment renovation worker',
            'сантехнич' => 'plumber working',
            'электрик' => 'electrician working',
            'укладка плитки' => 'tiler working',
            'ламинат' => 'floor installer working',
            'обоев' => 'wallpaper installation',

            'уборка' => 'professional cleaning service',
            'химчистка' => 'upholstery cleaning service',
            'мойка окон' => 'window cleaning service',
            'клининг' => 'professional office cleaning',

            'грузоперевоз' => 'moving delivery truck',
            'переезд' => 'moving company workers',
            'грузчики' => 'moving workers carrying boxes',

            'маникюр' => 'manicure nail technician',
            'репетитор' => 'private tutor teaching',
            'разработка сайтов' => 'web developer working',
            'английского' => 'English language teacher',
            'фотограф' => 'professional photographer working',
            'видеосъемка' => 'videographer working',
            'ресниц' => 'eyelash technician',
            'стрижка собак' => 'dog groomer working',
            'ремонт телефонов' => 'smartphone repair technician',
            'компьютерная помощь' => 'computer repair technician',
        ];

        $specificQuery = null;

        foreach ($rules as $needle => $query) {
            if (mb_strpos($titleLower, $needle) !== false) {
                $specificQuery = $query;
                break;
            }
        }

        $fallbackQueries = [
            'apartment' => 'ordinary apartment interior',
            'house' => 'suburban family house',
            'office' => 'office interior',
            'commercial' => 'commercial property interior',
            'car' => 'passenger car',
            'motorcycle' => 'motorcycle',
            'truck' => 'commercial truck',
            'construction' => 'construction machine',
            'tools' => 'professional power tool',
            'generator' => 'portable generator',
            'camera' => 'photography equipment',
            'cleaning' => 'professional cleaning worker',
            'repair' => 'home renovation worker',
            'moving' => 'moving company workers',
            'default' => 'professional service worker',
        ];

        $fallbackQuery =
            $fallbackQueries[$imageKey]
            ?? $fallbackQueries['default'];

        return array_values(array_unique(array_filter([
            $specificQuery,
            $fallbackQuery,
        ])));
    }
}