<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Category;
use App\Models\User;
use App\Services\PexelsImageService;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Один экземпляр сервиса используется на весь запуск Seeder,
     * чтобы Pexels ID и хеши фотографий не повторялись.
     */
    private ?PexelsImageService $pexelsImageService = null;

    private function pexelsImageService(): PexelsImageService
    {
        return $this->pexelsImageService
            ??= app(PexelsImageService::class);
    }

    /**
     * Создаёт отдельный кадр из одного исходного фото.
     * Все фото одного объявления показывают один объект,
     * но имеют разное кадрирование.
     */
    private function saveImageVariant($sourceImage, string $targetPath, int $variant): void
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            throw new \RuntimeException('Некорректный размер исходного изображения');
        }

        $targetWidth = 1200;
        $targetHeight = 900;
        $targetRatio = $targetWidth / $targetHeight;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($cropHeight * $targetRatio);
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($cropWidth / $targetRatio);
        }

        $variants = [
            ['zoom' => 1.00, 'x' => 0.50, 'y' => 0.50],
            ['zoom' => 1.10, 'x' => 0.28, 'y' => 0.46],
            ['zoom' => 1.18, 'x' => 0.72, 'y' => 0.54],
        ];

        $settings = $variants[$variant] ?? $variants[0];

        $cropWidth = max(1, (int) round($cropWidth / $settings['zoom']));
        $cropHeight = max(1, (int) round($cropHeight / $settings['zoom']));

        $maxX = max(0, $sourceWidth - $cropWidth);
        $maxY = max(0, $sourceHeight - $cropHeight);

        $sourceX = (int) round($maxX * $settings['x']);
        $sourceY = (int) round($maxY * $settings['y']);

        $output = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($output === false) {
            throw new \RuntimeException('Не удалось создать изображение GD');
        }

        $resampled = imagecopyresampled(
            $output,
            $sourceImage,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );

        if (!$resampled || !imagejpeg($output, $targetPath, 88)) {
            imagedestroy($output);
            throw new \RuntimeException('Не удалось сохранить вариант изображения');
        }

        imagedestroy($output);
    }

    private $allListings = [

        ['title' => 'Сдам 2-комнатную квартиру посуточно', 'price' => 2500, 'desc' => 'Уютная квартира в центре города, все удобства', 'image_key' => 'apartment', 'keywords' => ['аренда', 'квартира', 'посуточно']],
        ['title' => 'Загородный дом 150 м²', 'price' => 45000, 'desc' => 'Дом с участком, все коммуникации', 'image_key' => 'house', 'keywords' => ['дом', 'загородный']],
        ['title' => 'Аренда офиса 50 м²', 'price' => 35000, 'desc' => 'Офисное помещение с ремонтом, парковка', 'image_key' => 'office', 'keywords' => ['офис', 'аренда']],
        ['title' => 'Аренда Toyota Camry 2020', 'price' => 25000, 'desc' => 'Отличное состояние, один владелец', 'image_key' => 'car', 'keywords' => ['toyota', 'camry', 'легковой']],
        ['title' => ' Мотоцикл Honda CBR 600', 'price' => 6500, 'desc' => 'Спортивный, 2018 год выпуска', 'image_key' => 'motorcycle', 'keywords' => ['мотоцикл', 'honda', 'cbr']],
        ['title' => 'Аренда ГАЗель NEXT', 'price' => 1500, 'desc' => 'Тент 4 метра, 2020 год', 'image_key' => 'truck', 'keywords' => ['грузовик', 'газель', 'грузовой']],
        ['title' => 'Аренда Экскаватор JCB 3CX', 'price' => 35000, 'desc' => '2017 год, наработка 3000 моточасов', 'image_key' => 'construction', 'keywords' => ['экскаватор', 'jcb', 'спецтехника']],
        ['title' => 'Аренда Перфоратор Bosch GBH 2-26', 'price' => 1500, 'desc' => 'Новый, в комплекте фирменный кейс', 'image_key' => 'tools', 'keywords' => ['перфоратор', 'bosch', 'инструмент']],
        ['title' => 'Аренда Генератор Honda EU22i', 'price' => 95000, 'desc' => 'Инверторный, очень тихий', 'image_key' => 'generator', 'keywords' => ['генератор', 'honda']],
        ['title' => 'Аренда Фотоаппарат Canon EOS 90D', 'price' => 1000, 'desc' => 'Kit 18-135, пробег 5000 кадров', 'image_key' => 'camera', 'keywords' => ['фотоаппарат', 'canon', 'камера']],
        ['title' => 'Ремонт квартир под ключ', 'price' => 5000, 'desc' => 'От косметического до евроремонта', 'image_key' => 'repair', 'keywords' => ['ремонт', 'квартир']],
        ['title' => 'Уборка помещений', 'price' => 1500, 'desc' => 'Генеральная и поддерживающая уборка', 'image_key' => 'cleaning', 'keywords' => ['уборка', 'клининг']],
        ['title' => 'Грузоперевозки до 5 тонн', 'price' => 1000, 'desc' => 'Газель, есть опытные грузчики', 'image_key' => 'moving', 'keywords' => ['грузоперевозки', 'перевозки']],
        ['title' => 'Маникюр на дому', 'price' => 800, 'desc' => 'Наращивание, покрытие гель-лак', 'image_key' => 'default', 'keywords' => ['маникюр', 'красота']],
        ['title' => 'Репетитор по математике', 'price' => 1200, 'desc' => 'Подготовка к ЕГЭ и ОГЭ', 'image_key' => 'default', 'keywords' => ['репетитор', 'обучение']],
        ['title' => 'Сдам 2-комнатную квартиру посуточно', 'price' => 2500, 'desc' => 'Уютная квартира в центре города, все удобства', 'image_key' => 'apartment', 'keywords' => ['аренда', 'квартира', 'посуточно']],
        ['title' => '1-комнатная квартира, 40 м²', 'price' => 1800, 'desc' => 'Свежий ремонт, вся мебель и техника', 'image_key' => 'apartment', 'keywords' => ['квартира', 'аренда']],
        ['title' => '3-комнатная квартира с евроремонтом', 'price' => 3500, 'desc' => 'Посудомойка, стиралка, кондиционеры', 'image_key' => 'apartment', 'keywords' => ['квартира', 'аренда']],
        ['title' => 'Студия в новостройке, 35 м²', 'price' => 2000, 'desc' => 'Современный ремонт, первый арендатор', 'image_key' => 'apartment', 'keywords' => ['студия', 'новостройка']],
        ['title' => 'Апартаменты люкс, 60 м²', 'price' => 4500, 'desc' => 'Премиум класс, вид на город', 'image_key' => 'apartment', 'keywords' => ['апартаменты', 'люкс']],
        ['title' => 'Квартира рядом с метро', 'price' => 2200, 'desc' => '5 минут пешком до станции', 'image_key' => 'apartment', 'keywords' => ['квартира', 'метро']],
        ['title' => '2-комнатная, тихий район', 'price' => 2000, 'desc' => 'Спокойный двор, парковка', 'image_key' => 'apartment', 'keywords' => ['квартира', 'аренда']],
        ['title' => 'Квартира с балконом', 'price' => 2300, 'desc' => 'Застекленный балкон, вид во двор', 'image_key' => 'apartment', 'keywords' => ['квартира', 'балкон']],
        ['title' => 'Аренда на длительный срок', 'price' => 35000, 'desc' => 'Рассмотрим платежеспособных', 'image_key' => 'apartment', 'keywords' => ['квартира', 'долгосрочно']],
        ['title' => 'Меблированная квартира', 'price' => 2800, 'desc' => 'Вся необходимая мебель', 'image_key' => 'apartment', 'keywords' => ['квартира', 'мебель']],

    // ДОМА (8 УНИКАЛЬНЫХ)
        ['title' => 'Загородный дом 150 м²', 'price' => 45000, 'desc' => 'Дом с участком, все коммуникации', 'image_key' => 'house', 'keywords' => ['дом', 'загородный']],
        ['title' => 'Коттедж 200 м², 2 этажа', 'price' => 60000, 'desc' => 'Гараж, баня, участок 10 соток', 'image_key' => 'house', 'keywords' => ['коттедж', 'дом']],
        ['title' => 'Дачный домик, 80 м²', 'price' => 15000, 'desc' => 'Уютный дом в СНТ', 'image_key' => 'house', 'keywords' => ['дача', 'дом']],
        ['title' => 'Таунхаус 120 м²', 'price' => 40000, 'desc' => 'Закрытый поселок, охрана', 'image_key' => 'house', 'keywords' => ['таунхаус', 'дом']],
        ['title' => 'Дом у озера', 'price' => 35000, 'desc' => 'Первая линия, пляж', 'image_key' => 'house', 'keywords' => ['дом', 'озеро']],
        ['title' => 'Новый дом 180 м²', 'price' => 55000, 'desc' => 'Построен в 2023, никто не жил', 'image_key' => 'house', 'keywords' => ['дом', 'новый']],
        ['title' => 'Дом с бассейном', 'price' => 75000, 'desc' => 'Подогреваемый бассейн, сауна', 'image_key' => 'house', 'keywords' => ['дом', 'бассейн']],
        ['title' => 'Часть дома, отдельный вход', 'price' => 25000, 'desc' => '2 комнаты, кухня, санузел', 'image_key' => 'house', 'keywords' => ['дом', 'часть дома']],

    // ОФИСЫ (6 УНИКАЛЬНЫХ)
        ['title' => 'Аренда офиса 50 м²', 'price' => 35000, 'desc' => 'Офисное помещение с ремонтом, парковка', 'image_key' => 'office', 'keywords' => ['офис', 'аренда']],
        ['title' => 'Офис в бизнес-центре, 80 м²', 'price' => 60000, 'desc' => 'Класс А, охрана, рецепшн', 'image_key' => 'office', 'keywords' => ['офис', 'бизнес-центр']],
        ['title' => 'Коворкинг, рабочее место', 'price' => 15000, 'desc' => 'Гибкий график, все удобства', 'image_key' => 'office', 'keywords' => ['коворкинг', 'офис']],
        ['title' => 'Офис open-space, 150 м²', 'price' => 100000, 'desc' => 'Зонирование, переговорки', 'image_key' => 'office', 'keywords' => ['офис', 'open-space']],
        ['title' => 'Кабинет 20 м²', 'price' => 15000, 'desc' => 'Отдельный кабинет, мебель', 'image_key' => 'office', 'keywords' => ['офис', 'кабинет']],
        ['title' => 'Офис на месяц, 100 м²', 'price' => 70000, 'desc' => 'Краткосрочная аренда', 'image_key' => 'office', 'keywords' => ['офис', 'аренда']],

    // КОММЕРЧЕСКИЕ ПОМЕЩЕНИЯ (6 УНИКАЛЬНЫХ)
        ['title' => 'Торговое помещение 100 м²', 'price' => 75000, 'desc' => 'Первая линия, высокий трафик', 'image_key' => 'commercial', 'keywords' => ['торговое', 'магазин']],
        ['title' => 'Склад 200 м²', 'price' => 60000, 'desc' => 'Сухой склад, есть погрузчик', 'image_key' => 'commercial', 'keywords' => ['склад', 'складское']],
        ['title' => 'Магазин 60 м²', 'price' => 50000, 'desc' => 'Готовый бизнес, трафик', 'image_key' => 'commercial', 'keywords' => ['магазин', 'торговое']],
        ['title' => 'Помещение под ресторан, 200 м²', 'price' => 150000, 'desc' => 'Все коммуникации, вентиляция', 'image_key' => 'commercial', 'keywords' => ['ресторан', 'помещение']],
        ['title' => 'Автосервис 300 м²', 'price' => 120000, 'desc' => 'Боксы, ямы, компрессоры', 'image_key' => 'commercial', 'keywords' => ['автосервис', 'помещение']],
        ['title' => 'Помещение свободного назначения', 'price' => 80000, 'desc' => 'Любое использование', 'image_key' => 'commercial', 'keywords' => ['помещение', 'свободное']],

    // ЛЕГКОВЫЕ АВТО (10 УНИКАЛЬНЫХ)
        ['title' => 'Прокат Toyota Camry 2020', 'price' => 2500, 'desc' => 'Отличное состояние, один владелец', 'image_key' => 'car', 'keywords' => ['toyota', 'camry', 'легковой']],
        ['title' => 'Прокат BMW X5 2019', 'price' => 4500, 'desc' => 'Полный привод, панорамная крыша', 'image_key' => 'car', 'keywords' => ['bmw', 'x5']],
        ['title' => 'ПрокатHyundai Solaris 2021', 'price' => 10000, 'desc' => 'Новый, на гарантии', 'image_key' => 'car', 'keywords' => ['hyundai', 'solaris']],
        ['title' => 'Прокат Kia Rio 2022', 'price' => 14000, 'desc' => 'Пробег 15000 км, гарантия', 'image_key' => 'car', 'keywords' => ['kia', 'rio']],
        ['title' => 'Прокат Mercedes-Benz E-Class 2018', 'price' => 38000, 'desc' => 'AMG пакет, кожа', 'image_key' => 'car', 'keywords' => ['mercedes', 'e-class']],
        ['title' => 'Прокат Audi A6 2020', 'price' => 32000, 'desc' => 'Quattro, максимальная комплектация', 'image_key' => 'car', 'keywords' => ['audi', 'a6']],
        ['title' => 'Прокат Volkswagen Tiguan 2021', 'price' => 28000, 'desc' => 'Кроссовер, полный привод', 'image_key' => 'car', 'keywords' => ['volkswagen', 'tiguan']],
        ['title' => 'Прокат Mazda CX-5 2019', 'price' => 22000, 'desc' => 'Японец, без ДТП', 'image_key' => 'car', 'keywords' => ['mazda', 'cx-5']],
        ['title' => 'ПрокатNissan Qashqai 2020', 'price' => 19000, 'desc' => 'Вариатор, камера 360', 'image_key' => 'car', 'keywords' => ['nissan', 'qashqai']],
        ['title' => 'Прокат Skoda Octavia 2021', 'price' => 21000, 'desc' => 'Универсал, экономичный', 'image_key' => 'car', 'keywords' => ['skoda', 'octavia']],

    // МОТОЦИКЛЫ (6 УНИКАЛЬНЫХ)
        ['title' => 'Прокат Мотоцикл Honda CBR 600', 'price' => 6500, 'desc' => 'Спортивный, 2018 год выпуска', 'image_key' => 'motorcycle', 'keywords' => ['мотоцикл', 'honda', 'cbr']],
        ['title' => 'ПрокатYamaha YZF-R6', 'price' => 7500, 'desc' => 'Отличное состояние, пробег 15000 км', 'image_key' => 'motorcycle', 'keywords' => ['yamaha', 'мотоцикл']],
        ['title' => 'Прокат Kawasaki Ninja 650', 'price' => 5800, 'desc' => 'Идеален для города и трассы', 'image_key' => 'motorcycle', 'keywords' => ['kawasaki', 'ninja', 'мотоцикл']],
        ['title' => 'Прокат Suzuki GSX-R750', 'price' => 8500, 'desc' => 'Спортбайк, 2019 год', 'image_key' => 'motorcycle', 'keywords' => ['suzuki', 'мотоцикл']],
        ['title' => 'Прокат Harley-Davidson Street 750', 'price' => 9500, 'desc' => 'Круизер, американец', 'image_key' => 'motorcycle', 'keywords' => ['harley', 'мотоцикл']],
        ['title' => 'Прокат Ducati Monster 821', 'price' => 12000, 'desc' => 'Итальянский спорт', 'image_key' => 'motorcycle', 'keywords' => ['ducati', 'мотоцикл']],

    // ГРУЗОВИКИ (8 УНИКАЛЬНЫХ)
        ['title' => 'Аренда ГАЗель NEXT', 'price' => 180000, 'desc' => 'Тент 4 метра, 2020 год', 'image_key' => 'truck', 'keywords' => ['грузовик', 'газель', 'грузовой']],
        ['title' => 'Аренда КАМАЗ 65115', 'price' => 320000, 'desc' => 'Самосвал, 2019 год', 'image_key' => 'truck', 'keywords' => ['камаз', 'самосвал', 'грузовой']],
        ['title' => 'Аренда Volvo FH', 'price' => 550000, 'desc' => 'Тягач, 2021 год', 'image_key' => 'truck', 'keywords' => ['volvo', 'тягач', 'грузовой']],
        ['title' => 'Аренда Mercedes-Benz Actros', 'price' => 650000, 'desc' => 'Европеец, рефрижератор', 'image_key' => 'truck', 'keywords' => ['mercedes', 'грузовик']],
        ['title' => 'Аренда Scania R450', 'price' => 700000, 'desc' => 'Седельный тягач', 'image_key' => 'truck', 'keywords' => ['scania', 'грузовик']],
        ['title' => 'Аренда Ford Transit', 'price' => 250000, 'desc' => 'Фургон, 2020 год', 'image_key' => 'truck', 'keywords' => ['ford', 'фургон', 'грузовой']],
        ['title' => 'Аренда Isuzu NPR75', 'price' => 280000, 'desc' => 'Рефрижератор, 5 тонн', 'image_key' => 'truck', 'keywords' => ['isuzu', 'грузовик']],
        ['title' => 'Аренда DAF XF', 'price' => 600000, 'desc' => 'Тягач, 2018 год', 'image_key' => 'truck', 'keywords' => ['daf', 'грузовик']],

    // СПЕЦТЕХНИКА (10 УНИКАЛЬНЫХ)
        ['title' => 'Аренда Экскаватор JCB 3CX', 'price' => 3500000, 'desc' => '2017 год, наработка 3000 моточасов', 'image_key' => 'construction', 'keywords' => ['экскаватор', 'jcb', 'спецтехника']],
        ['title' => 'Аренда Бульдозер CAT D6', 'price' => 8500000, 'desc' => '2018 год, отличное состояние', 'image_key' => 'construction', 'keywords' => ['бульдозер', 'cat', 'спецтехника']],
        ['title' => 'Аренда Погрузчик Komatsu WA380', 'price' => 4200000, 'desc' => '2019 год, наработка 2500 моточасов', 'image_key' => 'construction', 'keywords' => ['погрузчик', 'komatsu', 'спецтехника']],
        ['title' => 'Аренда Автокран Liebherr LTM 1100', 'price' => 15000, 'desc' => 'Грузоподъемность 100 тонн', 'image_key' => 'construction', 'keywords' => ['автокран', 'кран', 'liebherr', 'спецтехника']],
        ['title' => 'Аренда Бетоносмеситель Cifa', 'price' => 8000, 'desc' => 'Объем барабана 9 м³', 'image_key' => 'construction', 'keywords' => ['бетоно', 'cifa', 'спецтехника']],
        ['title' => 'Аренда Буровая установка Bauer', 'price' => 25000, 'desc' => 'Глубина бурения до 50 м', 'image_key' => 'construction', 'keywords' => ['буровая', 'bauer', 'спецтехника']],
        ['title' => 'Аренда Фронтальный погрузчик XCMG', 'price' => 3800000, 'desc' => 'Китай, 2020 год', 'image_key' => 'construction', 'keywords' => ['погрузчик', 'спецтехника']],
        ['title' => 'Аренда Мини-экскаватор Kubota', 'price' => 1500000, 'desc' => 'Компактный, для частных работ', 'image_key' => 'construction', 'keywords' => ['экскаватор', 'мини', 'спецтехника']],
        ['title' => 'Аренда Грейдер CAT 140M', 'price' => 5500000, 'desc' => 'Дорожная техника', 'image_key' => 'construction', 'keywords' => ['грейдер', 'cat', 'спецтехника']],
        ['title' => 'Аренда Каток дорожный Dynapac', 'price' => 3200000, 'desc' => 'Вибрационный, 10 тонн', 'image_key' => 'construction', 'keywords' => ['каток', 'спецтехника']],

    // ИНСТРУМЕНТЫ (9 УНИКАЛЬНЫХ)
        ['title' => 'Прокат Перфоратор Bosch GBH 2-26', 'price' => 8500, 'desc' => 'Новый, в комплекте фирменный кейс', 'image_key' => 'tools', 'keywords' => ['перфоратор', 'bosch', 'инструмент']],
        ['title' => 'Прокат Болгарка Makita GA4030', 'price' => 4500, 'desc' => '125 мм, мощность 840 Вт', 'image_key' => 'tools', 'keywords' => ['болгарка', 'makita', 'инструмент']],
        ['title' => 'Прокат Шуруповерт DeWalt DCD791', 'price' => 12000, 'desc' => 'Аккумуляторный, 2 батареи в комплекте', 'image_key' => 'tools', 'keywords' => ['шуруповерт', 'dewalt', 'инструмент']],
        ['title' => 'Прокат Дрель-миксер Metabo', 'price' => 9500, 'desc' => 'Мощная, для замешивания', 'image_key' => 'tools', 'keywords' => ['дрель', 'миксер', 'инструмент']],
        ['title' => 'Прокат Лобзик Bosch GST 150', 'price' => 7500, 'desc' => 'Профессиональный, маятниковый', 'image_key' => 'tools', 'keywords' => ['лобзик', 'bosch', 'инструмент']],
        ['title' => 'Прокат Циркулярная пила Makita', 'price' => 11000, 'desc' => '190 мм, лазер', 'image_key' => 'tools', 'keywords' => ['пила', 'циркулярка', 'инструмент']],
        ['title' => 'Прокат Шлифмашинка угловая AEG', 'price' => 5500, 'desc' => '125 мм, 1200 Вт', 'image_key' => 'tools', 'keywords' => ['болгарка', 'шлифмашинка', 'инструмент']],
        ['title' => 'Прокат Краскопульт Wagner', 'price' => 8500, 'desc' => 'Электрический, для краски', 'image_key' => 'tools', 'keywords' => ['краскопульт', 'инструмент']],
        ['title' => 'Набор инструментов 120 предметов', 'price' => 6500, 'desc' => 'Чемодан, трещотки, головки', 'image_key' => 'tools', 'keywords' => ['набор', 'инструмент']],

    // ГЕНЕРАТОРЫ (6 УНИКАЛЬНЫХ)
        ['title' => 'Аренда Генератор Honda EU22i', 'price' => 95000, 'desc' => 'Инверторный, очень тихий', 'image_key' => 'generator', 'keywords' => ['генератор', 'honda']],
        ['title' => 'Аренда Генератор Huter DY3000L', 'price' => 25000, 'desc' => 'Бензиновый, 3 кВт', 'image_key' => 'generator', 'keywords' => ['генератор', 'huter']],
        ['title' => 'Аренда Генератор Fubag BS 5500', 'price' => 45000, 'desc' => '5.5 кВт, для стройки', 'image_key' => 'generator', 'keywords' => ['генератор', 'fubag']],
        ['title' => 'Аренда Генератор Champion GG6500', 'price' => 38000, 'desc' => '6.5 кВт, электростарт', 'image_key' => 'generator', 'keywords' => ['генератор', 'champion']],
        ['title' => 'Аренда Дизель-генератор Hyundai', 'price' => 120000, 'desc' => '10 кВт, автозапуск', 'image_key' => 'generator', 'keywords' => ['генератор', 'дизельный']],
        ['title' => 'Аренда Инверторный генератор Denzel', 'price' => 55000, 'desc' => '3.5 кВт, тихий', 'image_key' => 'generator', 'keywords' => ['генератор', 'инверторный']],

    // ФОТОТЕХНИКА (9 УНИКАЛЬНЫХ)
        ['title' => 'Прокат Фотоаппарат Canon EOS 90D', 'price' => 120000, 'desc' => 'Kit 18-135, пробег 5000 кадров', 'image_key' => 'camera', 'keywords' => ['фотоаппарат', 'canon', 'камера']],
        ['title' => 'Прокат Видеокамера Sony FDR-AX700', 'price' => 150000, 'desc' => 'Съемка в 4K, поддержка HDR', 'image_key' => 'camera', 'keywords' => ['видеокамера', 'sony', 'камера']],
        ['title' => 'Объектив Sigma 24-70mm f/2.8', 'price' => 85000, 'desc' => 'Для байонета Canon EF', 'image_key' => 'camera', 'keywords' => ['объектив', 'sigma', 'камера']],
        ['title' => 'Прокат Nikon D7500', 'price' => 95000, 'desc' => 'Body, пробег 12000', 'image_key' => 'camera', 'keywords' => ['nikon', 'фотоаппарат']],
        ['title' => 'Прокат Sony Alpha a7 III', 'price' => 180000, 'desc' => 'Полный кадр, беззеркалка', 'image_key' => 'camera', 'keywords' => ['sony', 'камера']],
        ['title' => 'Прокат Объектив Canon 50mm f/1.8', 'price' => 12000, 'desc' => 'Полтинник, новый', 'image_key' => 'camera', 'keywords' => ['объектив', 'canon']],
        ['title' => 'Прокат GoPro Hero 9', 'price' => 35000, 'desc' => 'Экшн-камера, 5K', 'image_key' => 'camera', 'keywords' => ['gopro', 'экшн', 'камера']],
        ['title' => 'Прокат Штатив Manfrotto', 'price' => 15000, 'desc' => 'Профессиональный, карбон', 'image_key' => 'camera', 'keywords' => ['штатив', 'камера']],
        ['title' => 'ПрокатВспышка Godox V860', 'price' => 18000, 'desc' => 'TTL, аккумулятор', 'image_key' => 'camera', 'keywords' => ['вспышка', 'камера']],

    // УСЛУГИ - РЕМОНТ (6 УНИКАЛЬНЫХ)
        ['title' => 'Ремонт квартир под ключ', 'price' => 5000, 'desc' => 'От косметического до евроремонта', 'image_key' => 'repair', 'keywords' => ['ремонт', 'квартир']],
        ['title' => 'Сантехнические работы', 'price' => 1000, 'desc' => 'Установка, ремонт, замена труб', 'image_key' => 'repair', 'keywords' => ['сантехника', 'ремонт']],
        ['title' => 'Услуги электрика', 'price' => 800, 'desc' => 'Монтаж, замена проводки, розеток', 'image_key' => 'repair', 'keywords' => ['электрик', 'ремонт']],
        ['title' => 'Укладка плитки', 'price' => 1200, 'desc' => 'Керамика, керамогранит, мозаика', 'image_key' => 'repair', 'keywords' => ['плитка', 'ремонт']],
        ['title' => 'Ламинат, паркет', 'price' => 600, 'desc' => 'Укладка, демонтаж, подготовка', 'image_key' => 'repair', 'keywords' => ['ламинат', 'пол', 'ремонт']],
        ['title' => 'Поклейка обоев', 'price' => 300, 'desc' => 'За м², все виды обоев', 'image_key' => 'repair', 'keywords' => ['обои', 'ремонт']],

    // УСЛУГИ - КЛИНИНГ (6 УНИКАЛЬНЫХ)
        ['title' => 'Уборка помещений', 'price' => 1500, 'desc' => 'Генеральная и поддерживающая уборка', 'image_key' => 'cleaning', 'keywords' => ['уборка', 'клининг']],
        ['title' => 'Химчистка мебели', 'price' => 2500, 'desc' => 'Диваны, кресла, матрасы', 'image_key' => 'cleaning', 'keywords' => ['химчистка', 'мебель', 'клининг']],
        ['title' => 'Мойка окон', 'price' => 3000, 'desc' => 'Квартиры, офисы, витрины', 'image_key' => 'cleaning', 'keywords' => ['мойка', 'окна', 'клининг']],
        ['title' => 'Уборка после ремонта', 'price' => 5000, 'desc' => 'Стройка, пыль, мусор', 'image_key' => 'cleaning', 'keywords' => ['уборка', 'клининг']],
        ['title' => 'Клининг офиса', 'price' => 10000, 'desc' => 'Ежедневная уборка', 'image_key' => 'cleaning', 'keywords' => ['клининг', 'офис']],
        ['title' => 'Мытье полов', 'price' => 50, 'desc' => 'За м², роторная машина', 'image_key' => 'cleaning', 'keywords' => ['полы', 'клининг']],

    // УСЛУГИ - ГРУЗОПЕРЕВОЗКИ (6 УНИКАЛЬНЫХ)
        ['title' => 'Грузоперевозки до 5 тонн', 'price' => 1000, 'desc' => 'Газель, есть опытные грузчики', 'image_key' => 'moving', 'keywords' => ['грузоперевозки', 'перевозки']],
        ['title' => 'Переезды квартир и офисов', 'price' => 5000, 'desc' => 'Под ключ, упаковка, разборка', 'image_key' => 'moving', 'keywords' => ['переезд', 'перевозки']],
        ['title' => 'Доставка стройматериалов', 'price' => 1500, 'desc' => 'Быстро, надежно, недорого', 'image_key' => 'moving', 'keywords' => ['доставка', 'перевозки']],
        ['title' => 'Вывоз мусора', 'price' => 3000, 'desc' => 'Старая мебель, строительный мусор', 'image_key' => 'moving', 'keywords' => ['мусор', 'вывоз', 'перевозки']],
        ['title' => 'Грузчики на час', 'price' => 500, 'desc' => 'Разгрузка, погрузка', 'image_key' => 'moving', 'keywords' => ['грузчики', 'перевозки']],
        ['title' => 'Перевозка пианино', 'price' => 3500, 'desc' => 'Аккуратно, ремни, опыт', 'image_key' => 'moving', 'keywords' => ['пианино', 'перевозки']],

    // УСЛУГИ - ПРОЧЕЕ (10 УНИКАЛЬНЫХ)
        ['title' => 'Маникюр на дому', 'price' => 800, 'desc' => 'Наращивание, покрытие гель-лак', 'image_key' => 'default', 'keywords' => ['маникюр', 'красота']],
        ['title' => 'Репетитор по математике', 'price' => 1200, 'desc' => 'Подготовка к ЕГЭ и ОГЭ', 'image_key' => 'default', 'keywords' => ['репетитор', 'обучение']],
        ['title' => 'Разработка сайтов', 'price' => 15000, 'desc' => 'Landing page, корпоративный сайт', 'image_key' => 'default', 'keywords' => ['разработка', 'сайты']],
        ['title' => 'Курсы английского', 'price' => 1500, 'desc' => 'Индивидуально и в группах', 'image_key' => 'default', 'keywords' => ['английский', 'обучение']],
        ['title' => 'Фотограф на свадьбу', 'price' => 25000, 'desc' => 'Полный день, обработка', 'image_key' => 'default', 'keywords' => ['фотограф', 'свадьба']],
        ['title' => 'Видеосъемка мероприятий', 'price' => 20000, 'desc' => 'Свадьбы, корпоративы', 'image_key' => 'default', 'keywords' => ['видео', 'съемка']],
        ['title' => 'Наращивание ресниц', 'price' => 2000, 'desc' => 'Классика, объем, 2D, 3D', 'image_key' => 'default', 'keywords' => ['ресницы', 'красота']],
        ['title' => 'Стрижка собак', 'price' => 1500, 'desc' => 'Грумер, все породы', 'image_key' => 'default', 'keywords' => ['грумер', 'собаки']],
        ['title' => 'Ремонт телефонов', 'price' => 1000, 'desc' => 'Замена экрана, батареи', 'image_key' => 'default', 'keywords' => ['ремонт', 'телефоны']],
        ['title' => 'Компьютерная помощь', 'price' => 800, 'desc' => 'Настройка, ремонт, вирусы', 'image_key' => 'default', 'keywords' => ['компьютер', 'ремонт']],
    ];

    public function run(): void
    {
        $admin = User::where('email', 'admin@baano.local')->first();
        if (!$admin) {
            $this->command->error(' Admin user not found!');
            return;
        }

        $totalCreated = 0;
        $imagesPath = storage_path('app/public/test-images');
        if (!file_exists($imagesPath)) mkdir($imagesPath, 0755, true);

        $allSubcategories = Category::whereNotNull('parent_id')->get();
        $listingsPerCategory = max(3, floor(120 / $allSubcategories->count()));

        foreach ($allSubcategories as $category) {
            $matchedListings = $this->findBestMatchingListings($category->name);
            if (empty($matchedListings)) {
                $matchedListings = $this->getDefaultListings();
            }

            $count = 0;
            while ($count < $listingsPerCategory && $totalCreated < 120) {
                $listingData = $matchedListings[$count % count($matchedListings)];
                $imageKey = $listingData['image_key'] ?? 'default';

                $listing = Listing::create([
                    'user_id' => $admin->id,
                    'category_id' => $category->id,
                    'title' => $listingData['title'],
                    'description' => $listingData['desc'],
                    'price' => $listingData['price'],
                    'price_type' => 'fixed',
                    'location' => $this->getRandomLocation(),
                    'listing_attributes' => $this->getAttributesForCategory($category->id),
                    'is_active' => true,
                ]);

                // ГЕНЕРАЦИЯ УНИКАЛЬНЫХ ФОТО ДЛЯ КАЖДОГО ОБЪЯВЛЕНИЯ
                $this->addUniqueImagesToListing($listing, $imagesPath, $imageKey);
                $totalCreated++;
                $count++;

                $this->command->info("✓ #{$totalCreated}: {$listingData['title']} → {$category->name}");
            }

            if ($totalCreated >= 120) break;
        }

        $this->command->info("✅ Всего создано объявлений: {$totalCreated}");
    }

    private function addUniqueImagesToListing(
        Listing $listing,
        string $imagesPath,
        string $imageKey
    ): void {
        $sourceImage = null;
        $temporaryPaths = [];

        try {
            $photo = $this->pexelsImageService()->fetchUniquePhoto(
                $listing->title,
                $imageKey
            );

            $sourceImage = @imagecreatefromstring($photo['content']);

            if ($sourceImage === false) {
                throw new \RuntimeException(
                    'Pexels вернул некорректное изображение'
                );
            }

            /*
             * Сначала полностью создаём все новые файлы.
             * Старые изображения остаются у объявления, если Pexels
             * или GD завершатся с ошибкой.
             */
            for ($variant = 0; $variant < 3; $variant++) {
                $imageNum = $variant + 1;
                $imageName = "listing_{$listing->id}_{$imageNum}.jpg";
                $imagePath = $imagesPath
                    . DIRECTORY_SEPARATOR
                    . $imageName;

                $temporaryPaths[] = $imagePath;

                $this->saveImageVariant(
                    $sourceImage,
                    $imagePath,
                    $variant
                );
            }

            /*
             * Удаляем старые медиа только после успешного создания
             * всех трёх новых файлов.
             */
            $listing->clearMediaCollection('images');

            foreach ($temporaryPaths as $index => $imagePath) {
                $imageNum = $index + 1;
                $imageName = "listing_{$listing->id}_{$imageNum}.jpg";

                $listing
                    ->addMedia($imagePath)
                    ->usingFileName($imageName)
                    ->withCustomProperties([
                        'source' => 'pexels',
                        'pexels_photo_id' => $photo['photo_id'],
                        'pexels_url' => $photo['pexels_url'],
                        'photographer' => $photo['photographer'],
                        'photographer_url' =>
                            $photo['photographer_url'],
                        'search_query' => $photo['query'],
                        'source_hash' => $photo['content_hash'],
                        'image_key' => $imageKey,
                    ])
                    ->toMediaCollection('images');
            }
        } catch (\Throwable $e) {
            $this->command?->warn(
                "⚠ Ошибка фото для #{$listing->id}: "
                . $e->getMessage()
            );
        } finally {
            if ($sourceImage !== null) {
                imagedestroy($sourceImage);
            }

            foreach ($temporaryPaths as $temporaryPath) {
                if (file_exists($temporaryPath)) {
                    @unlink($temporaryPath);
                }
            }
        }
    }

    /**
     * Точные шаблоны объявлений для категорий, которые раньше
     * получали бессмысленные названия из getDefaultListings().
     */
    private function getExactCategoryListings(string $categoryName): array
    {
        $categoryName = mb_strtolower(trim($categoryName));

        return match ($categoryName) {
            'it и дизайн' => [
                [
                    'title' => 'Разработка сайта под ключ',
                    'price' => 45000,
                    'desc' => 'Проектирование, адаптивная верстка, подключение форм и публикация сайта',
                    'image_key' => 'default',
                    'keywords' => ['it', 'дизайн', 'сайт'],
                ],
                [
                    'title' => 'Дизайн логотипа и фирменного стиля',
                    'price' => 18000,
                    'desc' => 'Логотип, цветовая палитра, шрифты и базовый брендбук',
                    'image_key' => 'default',
                    'keywords' => ['it', 'дизайн', 'логотип'],
                ],
                [
                    'title' => 'Создание интернет-магазина',
                    'price' => 75000,
                    'desc' => 'Каталог, корзина, онлайн-оплата и личный кабинет покупателя',
                    'image_key' => 'default',
                    'keywords' => ['it', 'дизайн', 'интернет-магазин'],
                ],
                [
                    'title' => 'UX/UI дизайн мобильного приложения',
                    'price' => 35000,
                    'desc' => 'Прототипирование экранов и подготовка интерфейса для разработки',
                    'image_key' => 'default',
                    'keywords' => ['it', 'дизайн', 'ux', 'ui'],
                ],
            ],

            'банкетные залы' => [
                [
                    'title' => 'Аренда банкетного зала на 50 гостей',
                    'price' => 35000,
                    'desc' => 'Зал для свадьбы, юбилея или корпоратива, отдельная входная группа',
                    'image_key' => 'commercial',
                    'keywords' => ['банкет', 'зал'],
                ],
                [
                    'title' => 'Банкетный зал для свадьбы',
                    'price' => 60000,
                    'desc' => 'Светлый зал, место для ведущего и танцпол',
                    'image_key' => 'commercial',
                    'keywords' => ['банкет', 'свадьба'],
                ],
                [
                    'title' => 'Зал для юбилея и корпоратива',
                    'price' => 28000,
                    'desc' => 'Посадка до 40 человек, музыкальное оборудование и гардероб',
                    'image_key' => 'commercial',
                    'keywords' => ['банкет', 'корпоратив'],
                ],
                [
                    'title' => 'Аренда банкетного зала с кухней',
                    'price' => 45000,
                    'desc' => 'Зал с кухней, сервировкой и зоной для фотосессии',
                    'image_key' => 'commercial',
                    'keywords' => ['банкет', 'зал'],
                ],
            ],

            'гаражи и парковки' => [
                [
                    'title' => 'Аренда гаража 24 м²',
                    'price' => 9000,
                    'desc' => 'Сухой капитальный гараж, электричество и удобный подъезд',
                    'image_key' => 'commercial',
                    'keywords' => ['гараж', 'парковка'],
                ],
                [
                    'title' => 'Сдам место в подземном паркинге',
                    'price' => 7000,
                    'desc' => 'Охраняемый подземный паркинг с круглосуточным доступом',
                    'image_key' => 'commercial',
                    'keywords' => ['гараж', 'парковка'],
                ],
                [
                    'title' => 'Аренда охраняемого машиноместа',
                    'price' => 5500,
                    'desc' => 'Закрытая территория, видеонаблюдение и удобный въезд',
                    'image_key' => 'commercial',
                    'keywords' => ['гараж', 'парковка'],
                ],
                [
                    'title' => 'Тёплый гараж с электричеством',
                    'price' => 12000,
                    'desc' => 'Утеплённый гараж, освещение, розетки и стеллажи',
                    'image_key' => 'commercial',
                    'keywords' => ['гараж', 'парковка'],
                ],
            ],

            'для отдыха, мероприятий и проживания' => [
                [
                    'title' => 'Аренда беседки для отдыха',
                    'price' => 6000,
                    'desc' => 'Закрытая беседка с мангалом, столом и освещением',
                    'image_key' => 'house',
                    'keywords' => ['отдых', 'мероприятия', 'проживание'],
                ],
                [
                    'title' => 'Домик для отдыха на выходные',
                    'price' => 8500,
                    'desc' => 'Домик с кухней, санузлом и зоной отдыха на природе',
                    'image_key' => 'house',
                    'keywords' => ['отдых', 'проживание'],
                ],
                [
                    'title' => 'Площадка для семейного праздника',
                    'price' => 15000,
                    'desc' => 'Крытая площадка, столы, музыкальное оборудование и парковка',
                    'image_key' => 'commercial',
                    'keywords' => ['отдых', 'мероприятия'],
                ],
                [
                    'title' => 'Аренда зоны барбекю',
                    'price' => 4000,
                    'desc' => 'Мангал, навес, стол и места для компании до 12 человек',
                    'image_key' => 'house',
                    'keywords' => ['отдых', 'мероприятия'],
                ],
            ],

            'коммерческая недвижимость' => [
                [
                    'title' => 'Аренда помещения свободного назначения',
                    'price' => 80000,
                    'desc' => 'Помещение с отдельным входом под услуги, офис или торговлю',
                    'image_key' => 'commercial',
                    'keywords' => ['коммерческая недвижимость', 'помещение'],
                ],
                [
                    'title' => 'Коммерческое помещение 120 м²',
                    'price' => 95000,
                    'desc' => 'Первый этаж, витринные окна и парковка рядом со входом',
                    'image_key' => 'commercial',
                    'keywords' => ['коммерческая недвижимость', 'помещение'],
                ],
                [
                    'title' => 'Помещение под офис или услуги',
                    'price' => 65000,
                    'desc' => 'Готовое помещение с ремонтом и отдельными кабинетами',
                    'image_key' => 'commercial',
                    'keywords' => ['коммерческая недвижимость', 'офис'],
                ],
                [
                    'title' => 'Аренда помещения на первой линии',
                    'price' => 120000,
                    'desc' => 'Высокий пешеходный трафик, отдельный вход и место для вывески',
                    'image_key' => 'commercial',
                    'keywords' => ['коммерческая недвижимость', 'помещение'],
                ],
            ],

            'конференц-залы' => [
                [
                    'title' => 'Аренда конференц-зала на 30 человек',
                    'price' => 12000,
                    'desc' => 'Зал с проектором, экраном, микрофонами и быстрым интернетом',
                    'image_key' => 'office',
                    'keywords' => ['конференц-зал', 'зал'],
                ],
                [
                    'title' => 'Конференц-зал с проектором',
                    'price' => 9000,
                    'desc' => 'Почасовая аренда для презентаций, переговоров и обучения',
                    'image_key' => 'office',
                    'keywords' => ['конференц-зал', 'проектор'],
                ],
                [
                    'title' => 'Зал для семинаров и тренингов',
                    'price' => 15000,
                    'desc' => 'Трансформируемая рассадка, флипчарт и зона кофе-брейка',
                    'image_key' => 'office',
                    'keywords' => ['конференц-зал', 'семинар'],
                ],
                [
                    'title' => 'Переговорная комната почасово',
                    'price' => 2500,
                    'desc' => 'Тихая переговорная на 10 человек с телевизором и Wi-Fi',
                    'image_key' => 'office',
                    'keywords' => ['конференц-зал', 'переговорная'],
                ],
            ],

            'легковые автомобили' => [
                [
                    'title' => 'Прокат Toyota Camry 2020',
                    'price' => 4500,
                    'desc' => 'Автоматическая коробка, климат-контроль и чистый салон',
                    'image_key' => 'car',
                    'keywords' => ['легковые автомобили', 'автомобиль'],
                ],
                [
                    'title' => 'Аренда Hyundai Solaris без водителя',
                    'price' => 2800,
                    'desc' => 'Экономичный автомобиль для города, автоматическая коробка',
                    'image_key' => 'car',
                    'keywords' => ['легковые автомобили', 'автомобиль'],
                ],
                [
                    'title' => 'Прокат Kia Rio на сутки',
                    'price' => 3000,
                    'desc' => 'Исправный автомобиль, кондиционер и небольшой расход топлива',
                    'image_key' => 'car',
                    'keywords' => ['легковые автомобили', 'автомобиль'],
                ],
                [
                    'title' => 'Аренда Volkswagen Tiguan',
                    'price' => 5200,
                    'desc' => 'Кроссовер с полным приводом и вместительным багажником',
                    'image_key' => 'car',
                    'keywords' => ['легковые автомобили', 'автомобиль'],
                ],
            ],

            'места для хранения и стоянки транспорта' => [
                [
                    'title' => 'Аренда бокса для хранения вещей',
                    'price' => 6500,
                    'desc' => 'Сухой закрытый бокс с круглосуточным доступом',
                    'image_key' => 'commercial',
                    'keywords' => ['хранение', 'стоянка'],
                ],
                [
                    'title' => 'Складской контейнер для хранения',
                    'price' => 9000,
                    'desc' => 'Металлический контейнер на охраняемой территории',
                    'image_key' => 'commercial',
                    'keywords' => ['хранение', 'стоянка'],
                ],
                [
                    'title' => 'Место для хранения автомобиля',
                    'price' => 7500,
                    'desc' => 'Закрытая охраняемая площадка с видеонаблюдением',
                    'image_key' => 'commercial',
                    'keywords' => ['хранение', 'стоянка'],
                ],
                [
                    'title' => 'Аренда тёплого склада-бокса',
                    'price' => 14000,
                    'desc' => 'Отапливаемый бокс для сезонного хранения имущества',
                    'image_key' => 'commercial',
                    'keywords' => ['хранение', 'стоянка'],
                ],
            ],

            'спецтранспорт' => [
                [
                    'title' => 'Аренда автовышки 22 метра',
                    'price' => 3500,
                    'desc' => 'Автовышка с оператором для высотных монтажных работ',
                    'image_key' => 'construction',
                    'keywords' => ['спецтранспорт', 'автовышка'],
                ],
                [
                    'title' => 'Услуги эвакуатора 24/7',
                    'price' => 3000,
                    'desc' => 'Эвакуация легковых автомобилей по городу и области',
                    'image_key' => 'truck',
                    'keywords' => ['спецтранспорт', 'эвакуатор'],
                ],
                [
                    'title' => 'Аренда манипулятора 5 тонн',
                    'price' => 4500,
                    'desc' => 'Перевозка и погрузка стройматериалов и оборудования',
                    'image_key' => 'truck',
                    'keywords' => ['спецтранспорт', 'манипулятор'],
                ],
                [
                    'title' => 'Вакуумная машина для откачки',
                    'price' => 5000,
                    'desc' => 'Откачка септиков, колодцев и технических ёмкостей',
                    'image_key' => 'truck',
                    'keywords' => ['спецтранспорт', 'вакуумная машина'],
                ],
            ],

            'строительная техника' => [
                [
                    'title' => 'Аренда экскаватора-погрузчика JCB 3CX',
                    'price' => 3500,
                    'desc' => 'Земляные работы, погрузка грунта и планировка участка',
                    'image_key' => 'construction',
                    'keywords' => ['строительная техника', 'экскаватор'],
                ],
                [
                    'title' => 'Аренда мини-экскаватора',
                    'price' => 2800,
                    'desc' => 'Компактная техника для траншей и работ на небольших участках',
                    'image_key' => 'construction',
                    'keywords' => ['строительная техника', 'экскаватор'],
                ],
                [
                    'title' => 'Аренда фронтального погрузчика',
                    'price' => 4200,
                    'desc' => 'Погрузка сыпучих материалов и расчистка территории',
                    'image_key' => 'construction',
                    'keywords' => ['строительная техника', 'погрузчик'],
                ],
                [
                    'title' => 'Аренда дорожного катка',
                    'price' => 5000,
                    'desc' => 'Уплотнение грунта, щебня и асфальтового покрытия',
                    'image_key' => 'construction',
                    'keywords' => ['строительная техника', 'каток'],
                ],
            ],

            'торговые помещения' => [
                [
                    'title' => 'Аренда магазина 60 м²',
                    'price' => 50000,
                    'desc' => 'Готовое торговое помещение с витринами и отдельным входом',
                    'image_key' => 'commercial',
                    'keywords' => ['торговые помещения', 'магазин'],
                ],
                [
                    'title' => 'Торговое помещение на первой линии',
                    'price' => 90000,
                    'desc' => 'Высокий трафик, панорамные окна и место для наружной рекламы',
                    'image_key' => 'commercial',
                    'keywords' => ['торговые помещения', 'помещение'],
                ],
                [
                    'title' => 'Павильон в торговом центре',
                    'price' => 70000,
                    'desc' => 'Павильон рядом с центральным входом и зоной высокого трафика',
                    'image_key' => 'commercial',
                    'keywords' => ['торговые помещения', 'павильон'],
                ],
                [
                    'title' => 'Помещение под продуктовый магазин',
                    'price' => 85000,
                    'desc' => 'Первый этаж, разгрузочная зона и необходимые коммуникации',
                    'image_key' => 'commercial',
                    'keywords' => ['торговые помещения', 'магазин'],
                ],
            ],

            default => [],
        };
    }

    private function findBestMatchingListings(string $categoryName): array
    {
        $exactListings = $this->getExactCategoryListings($categoryName);

        if ($exactListings !== []) {
            return $exactListings;
        }

        $categoryNameLower = mb_strtolower($categoryName);
        $matched = [];

        foreach ($this->allListings as $listing) {
            foreach ($listing['keywords'] as $keyword) {
                if (
                    mb_strpos(
                        $categoryNameLower,
                        mb_strtolower($keyword)
                    ) !== false
                ) {
                    $matched[] = $listing;
                    break;
                }
            }
        }

        if ($matched !== []) {
            return $matched;
        }

        return [
            [
                'title' => 'Предложение в категории «' . $categoryName . '»',
                'price' => rand(3000, 50000),
                'desc' => 'Актуальное предложение в категории «' . $categoryName . '»',
                'image_key' => 'default',
                'keywords' => [$categoryName],
            ],
        ];
    }

    private function getDefaultListings(): array
    {
        return [
            ['title' => 'Услуга профессионала', 'price' => rand(1000, 50000), 'desc' => 'Качественно и в срок', 'image_key' => 'default', 'keywords' => ['услуга']],
            ['title' => 'Профессиональные услуги', 'price' => rand(1000, 50000), 'desc' => 'Опыт работы более 5 лет', 'image_key' => 'default', 'keywords' => ['услуга']],
            ['title' => 'Выполню работу качественно', 'price' => rand(1000, 50000), 'desc' => 'Гарантия результата', 'image_key' => 'default', 'keywords' => ['услуга']],
        ];
    }

    private function getRandomLocation(): string
    {
        $locations = ['г. Москва, ул. Ленина, д. 10', 'г. Екатеринбург, пр. Ленина, д. 25', 'г. Санкт-Петербург, Невский пр., д. 50', 'г. Казань, ул. Баумана, д. 15', 'г. Новосибирск, Красный пр., д. 30', 'Адрес не указан'];
        return $locations[array_rand($locations)];
    }

private function getAttributesForCategory(int $categoryId): array
{
    // НЕДВИЖИМОСТЬ (ID 2-13)
    if (in_array($categoryId, [2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13])) {
        return [
            'property_type' => ['apartment', 'house', 'land', 'commercial'][array_rand([0, 1, 2, 3])],
            'area' => rand(20, 300),
            'floor' => rand(1, 25),
            'rooms' => rand(1, 6),
            'condition' => ['rough', 'pre_finish', 'finish', 'furnished'][array_rand([0, 1, 2, 3])],
            'furnished' => rand(0, 1) == 1,
        ];
    }

    // ЛЕГКОВЫЕ АВТО (ID 15)
    if ($categoryId == 15) {
        $brands = ['Toyota' => ['Camry', 'Corolla', 'Land Cruiser'], 'BMW' => ['X5', 'X3', '3 Series'], 'Hyundai' => ['Solaris', 'Tucson', 'Creta'], 'Kia' => ['Rio', 'Sportage', 'Sorento'], 'Mercedes-Benz' => ['E-Class', 'C-Class', 'GLE']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'year' => rand(2015, 2024),
            'mileage' => rand(10000, 200000),
            'fuel_type' => ['petrol', 'diesel', 'electric', 'hybrid'][array_rand([0, 1, 2, 3])],
            'transmission' => ['manual', 'automatic', 'cvt', 'robot'][array_rand([0, 1, 2, 3])],
            'drive' => ['fwd', 'rwd', 'awd'][array_rand([0, 1, 2])],
            'condition' => ['new', 'used'][array_rand([0, 1])],
        ];
    }

    // МОТОЦИКЛЫ (ID 16)
    if ($categoryId == 16) {
        $brands = ['Honda' => ['CBR 600', 'CB 1000'], 'Yamaha' => ['YZF-R6', 'MT-09'], 'Kawasaki' => ['Ninja 650', 'Z900'], 'Suzuki' => ['GSX-R600', 'V-Strom']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'year' => rand(2015, 2024),
            'mileage' => rand(1000, 50000),
            'engine_capacity' => [600, 750, 1000, 1200][array_rand([0, 1, 2, 3])],
            'moto_type' => ['sport', 'touring', 'cruiser', 'enduro', 'scooter', 'naked'][array_rand([0, 1, 2, 3, 4, 5])],
        ];
    }

    // ГРУЗОВИК (ID 17)
    if ($categoryId == 17) {
        $brands = ['ГАЗ' => ['ГАЗель NEXT', 'ГАЗон NEXT'], 'КАМАЗ' => ['65115', '5490'], 'МАЗ' => ['5440', '6312'], 'Volvo' => ['FH', 'FM']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'year' => rand(2015, 2024),
            'mileage' => rand(50000, 500000),
            'capacity' => [1.5, 3, 5, 10, 20, 40][array_rand([0, 1, 2, 3, 4, 5])],
            'body_type' => ['tent', 'refrigerator', 'van', 'flatbed', 'dump', 'container'][array_rand([0, 1, 2, 3, 4, 5])],
        ];
    }

    // СПЕЦТЕХНИКА (ID 18, 20)
    if ($categoryId == 18 || $categoryId == 20) {
        $brands = ['JCB' => ['3CX', '4CX'], 'CAT' => ['320', '330'], 'Komatsu' => ['PC200', 'PC300'], 'Volvo CE' => ['EC220', 'EC300'], 'Liebherr' => ['LTM 1100', 'LTM 1200']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'year' => rand(2015, 2024),
            'hours' => rand(1000, 10000),
            'power' => rand(100, 500),
            'condition' => ['new', 'used_excellent', 'used_good', 'used_fair'][array_rand([0, 1, 2, 3])],
        ];
    }

    // ИНСТРУМЕНТЫ (ID 21)
    if ($categoryId == 21) {
        $brands = ['Bosch' => ['GBH 2-26', 'GSR 120'], 'Makita' => ['GA4030', 'HP2071'], 'DeWalt' => ['DCD791', 'DWE4257'], 'Metabo' => ['BS 18 L', 'WEV 850']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'tool_type' => ['drill', 'grinder', 'saw', 'hammer'][array_rand([0, 1, 2, 3])],
            'power_type' => ['corded', 'cordless', 'pneumatic'][array_rand([0, 1, 2])],
            'condition' => ['new', 'used_excellent', 'used_good'][array_rand([0, 1, 2])],
        ];
    }

    // ГЕНЕРАТОРЫ (ID 22)
    if ($categoryId == 22) {
        $brands = ['Honda' => ['EU22i', 'EU30is'], 'Huter' => ['DY3000L', 'DY6500LX'], 'Fubag' => ['BS 3300', 'BS 5500'], 'Champion' => ['GG3300', 'GG6500']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'power' => [2.2, 3, 5, 7, 10][array_rand([0, 1, 2, 3, 4])],
            'fuel_type' => ['petrol', 'diesel', 'gas', 'inverter'][array_rand([0, 1, 2, 3])],
            'condition' => ['new', 'used_excellent', 'used_good'][array_rand([0, 1, 2])],
        ];
    }

    // ФОТОТЕХНИКА (ID 23)
    if ($categoryId == 23) {
        $brands = ['Canon' => ['EOS 90D', 'EOS R6'], 'Nikon' => ['D7500', 'Z6 II'], 'Sony' => ['Alpha a7 III', 'Alpha a6400'], 'Fujifilm' => ['X-T4', 'X-S10']];
        $brand = array_rand($brands);
        return [
            'brand' => $brand,
            'model' => $brands[$brand][array_rand($brands[$brand])],
            'device_type' => ['camera', 'video', 'lens', 'action', 'light', 'accessories'][array_rand([0, 1, 2, 3, 4, 5])],
            'condition' => ['new', 'used_excellent', 'used_good'][array_rand([0, 1, 2])],
        ];
    }

    // УСЛУГИ (ID 25-30)
    if (in_array($categoryId, [25, 26, 27, 28, 29, 30])) {
        return [
            'experience_years' => rand(1, 20),
            'service_area' => ['Москва и МО', 'Санкт-Петербург', 'Екатеринбург', 'Вся Россия'][array_rand([0, 1, 2, 3])],
            'work_schedule' => ['full_time', 'part_time', 'project', '24_7'][array_rand([0, 1, 2, 3])],
            'warranty' => rand(0, 1) == 1,
            'warranty_months' => rand(1, 24),
            'emergency_service' => rand(0, 1) == 1,
        ];
    }

    return ['condition' => 'new'];
}
}