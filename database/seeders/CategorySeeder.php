<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::query()->delete();

        // === НЕДВИЖИМОСТЬ ===
        $realty = Category::create([
            'name' => 'Недвижимость',
            'slug' => 'realty',
            'parent_id' => null,
            'description' => 'Аренда и продажа недвижимости',
            'type' => 'main',
        ]);

        // Квартиры
        $apartments = Category::create([
            'name' => 'Квартиры',
            'slug' => 'apartments',
            'parent_id' => $realty->id,
            'description' => 'Аренда квартир',
            'type' => 'sub',
        ]);

        Category::create(['name' => 'Посуточно и на длительный срок', 'slug' => 'apartments-long-term', 'parent_id' => $apartments->id, 'type' => 'sub']);
        Category::create(['name' => 'Дома и коттеджи', 'slug' => 'houses', 'parent_id' => $apartments->id, 'type' => 'sub']);
        Category::create(['name' => 'Для отдыха, мероприятий и проживания', 'slug' => 'vacation-rentals', 'parent_id' => $apartments->id, 'type' => 'sub']);

        // Коммерческая недвижимость
        $commercial = Category::create([
            'name' => 'Коммерческая недвижимость',
            'slug' => 'commercial',
            'parent_id' => $realty->id,
            'description' => 'Коммерческая недвижимость',
            'type' => 'sub',
        ]);

        Category::create(['name' => 'Офисы', 'slug' => 'offices', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Склады', 'slug' => 'warehouses', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Торговые помещения', 'slug' => 'retail-spaces', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Гаражи и парковки', 'slug' => 'garages-parking', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Места для хранения и стоянки транспорта', 'slug' => 'storage-parking', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Банкетные залы', 'slug' => 'banquet-halls', 'parent_id' => $commercial->id, 'type' => 'sub']);
        Category::create(['name' => 'Конференц-залы', 'slug' => 'conference-halls', 'parent_id' => $commercial->id, 'type' => 'sub']);

        // === ТРАНСПОРТ ===
        $transport = Category::create([
            'name' => 'Транспорт',
            'slug' => 'transport',
            'parent_id' => null,
            'description' => 'Аренда транспорта',
            'type' => 'main',
        ]);

        Category::create(['name' => 'Легковые автомобили', 'slug' => 'cars', 'parent_id' => $transport->id, 'type' => 'sub']);
        Category::create(['name' => 'Мотоциклы', 'slug' => 'motorcycles', 'parent_id' => $transport->id, 'type' => 'sub']);
        Category::create(['name' => 'Грузовой транспорт', 'slug' => 'trucks', 'parent_id' => $transport->id, 'type' => 'sub']);
        Category::create(['name' => 'Спецтранспорт', 'slug' => 'special-vehicles', 'parent_id' => $transport->id, 'type' => 'sub']);

        // === ТЕХНИКА И ОБОРУДОВАНИЕ ===
        $equipment = Category::create([
            'name' => 'Техника и оборудование',
            'slug' => 'equipment',
            'parent_id' => null,
            'description' => 'Аренда техники и оборудования',
            'type' => 'main',
        ]);

        Category::create(['name' => 'Строительная техника', 'slug' => 'construction-equipment', 'parent_id' => $equipment->id, 'type' => 'sub']);
        Category::create(['name' => 'Инструменты', 'slug' => 'tools', 'parent_id' => $equipment->id, 'type' => 'sub']);
        Category::create(['name' => 'Генераторы', 'slug' => 'generators', 'parent_id' => $equipment->id, 'type' => 'sub']);
        Category::create(['name' => 'Фото- и видеотехника', 'slug' => 'photo-video-equipment', 'parent_id' => $equipment->id, 'type' => 'sub']);

        // === УСЛУГИ ===
        $services = Category::create([
            'name' => 'Услуги',
            'slug' => 'services',
            'parent_id' => null,
            'description' => 'Различные услуги',
            'type' => 'main',
        ]);

        Category::create(['name' => 'Ремонт и строительство', 'slug' => 'repair-construction', 'parent_id' => $services->id, 'type' => 'sub']);
        Category::create(['name' => 'Уборка помещений', 'slug' => 'cleaning', 'parent_id' => $services->id, 'type' => 'sub']);
        Category::create(['name' => 'Грузоперевозки', 'slug' => 'cargo-transport', 'parent_id' => $services->id, 'type' => 'sub']);
        Category::create(['name' => 'Красота и здоровье', 'slug' => 'beauty-health', 'parent_id' => $services->id, 'type' => 'sub']);
        Category::create(['name' => 'Обучение и репетиторство', 'slug' => 'education-tutoring', 'parent_id' => $services->id, 'type' => 'sub']);
        Category::create(['name' => 'IT и дизайн', 'slug' => 'it-design', 'parent_id' => $services->id, 'type' => 'sub']);

        $this->command->info('✅ Категории созданы!');
    }
}