<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\DistributorCenter;
use App\Models\Manager;
use Illuminate\Database\Seeder;

class DistributorCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
          ['title' => 'Волгоград (WB)', 'distributor_id' => 1],
          ['title' => 'Казань (WB)', 'distributor_id' => 1],
          ['title' => 'Коледино (WB)', 'distributor_id' => 1],
          ['title' => 'Краснодар (WB)', 'distributor_id' => 1],
          ['title' => 'Крыловская (WB) СЦ', 'distributor_id' => 1],
          ['title' => 'Невинномысск (WB)', 'distributor_id' => 1],
          ['title' => 'Нижний Новгород (WB)', 'distributor_id' => 1],
          ['title' => 'Обухово (WB)', 'distributor_id' => 1],
          ['title' => 'Подольск (WB)', 'distributor_id' => 1],
          ['title' => 'Подольск 3 (WB)', 'distributor_id' => 1],
          ['title' => 'Подольск 4 (WB)', 'distributor_id' => 1],
          ['title' => 'Пушкино (WB)', 'distributor_id' => 1],
          ['title' => 'Ростов-на-Дону (WB) СЦ', 'distributor_id' => 1],
          ['title' => 'Рязань (WB) Тюшевское', 'distributor_id' => 1],
          ['title' => 'Самара (WB) Новосемейкино', 'distributor_id' => 1],
          ['title' => 'Самара (WB) СЦ', 'distributor_id' => 1],
          ['title' => 'СПБ Уткина Заводь (WB)', 'distributor_id' => 1],
          ['title' => 'СПБ Шушары (WB)', 'distributor_id' => 1],
          ['title' => 'Тамбов WB (Котовск)', 'distributor_id' => 1],
          ['title' => 'Тула Алексин (WB)', 'distributor_id' => 1],
          ['title' => 'Электросталь (WB)', 'distributor_id' => 1],
          
          ['title' => 'Адыгейск (OZON)', 'distributor_id' => 2],
          ['title' => 'Гривно (OZON)', 'distributor_id' => 2],
          ['title' => 'Жуковский (OZON)', 'distributor_id' => 2],
          ['title' => 'Казань, Зеленодольск (OZON)', 'distributor_id' => 2],
          ['title' => 'Невинномысск (Озон)', 'distributor_id' => 2],
          ['title' => 'Нижний Новгород (OZON)', 'distributor_id' => 2],
          ['title' => 'Ногинск (OZON)', 'distributor_id' => 2],
          ['title' => 'Петровское (OZON)', 'distributor_id' => 2],
          ['title' => 'Пушкино (OZON)', 'distributor_id' => 2],
          ['title' => 'Пушкино 2 (OZON)', 'distributor_id' => 2],
          ['title' => 'Ростов-на-Дону (OZON)', 'distributor_id' => 2],
          ['title' => 'Самара (OZON) (Чапаевск)', 'distributor_id' => 2],
          ['title' => 'Софьино (ОЗОН)', 'distributor_id' => 2],
          ['title' => 'СПБ Бугры (OZON)', 'distributor_id' => 2],
          ['title' => 'СПБ Петро-Славянка (ОЗОН)', 'distributor_id' => 2],
          ['title' => 'СПБ РФЦ Колпино (OZON)', 'distributor_id' => 2],
          ['title' => 'СПБ Шушары (OZON)', 'distributor_id' => 2],
          ['title' => 'Хоругвино (OZON)', 'distributor_id' => 2],

          ['title' => 'Ростов-на-Дону (ЯндексМаркет)', 'distributor_id' => 3],
          ['title' => 'Софьино (Яндекс)', 'distributor_id' => 3],
          ['title' => 'СПБ Парголово', 'distributor_id' => 3],
          
          // ['title' => 'Казань (МагнитМаркет) Зеленодольск', 'distributor_id' => 4],
          
          // ['title' => 'Казань, Зеленодольск (Казань Экспресс)', 'distributor_id' => 4],
        ];

        $distributorTitles = [
            1 => 'Wildberries',
            2 => 'Ozon',
            3 => 'ЯндексМаркет',
        ];

        $distributorIds = Distributor::query()
            ->whereIn('title', array_values($distributorTitles))
            ->pluck('id', 'title');

        $managerIds = Manager::query()
            ->pluck('id')
            ->all();

        if (empty($managerIds)) {
            return;
        }

        $faker = \Faker\Factory::create('ru_RU');

        foreach ($data as $item) {
            $distributorTitle = $distributorTitles[$item['distributor_id']] ?? null;
            $distributorId = $distributorTitle ? ($distributorIds[$distributorTitle] ?? null) : null;

            if (! $distributorId) {
                continue;
            }

            DistributorCenter::firstOrCreate(
                [
                    'distributor_id' => $distributorId,
                    'title' => $item['title'],
                ],
                [
                    'manager_id' => $managerIds[array_rand($managerIds)],
                    'address' => $faker->address(),
                ],
            );
        }
    }
}
