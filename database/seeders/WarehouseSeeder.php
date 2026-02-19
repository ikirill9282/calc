<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('ru_RU');
        $data = [
          ['title' => 'Склад Екатеринбург', 'address' => 'г.Екатеринбург, ул Хлебная дом напротив дома №1'],
          ['title' => 'Склад Иваново', 'address' => '153031, Ивановская обл, г Иваново, линия 25-я, д. 3'],
          ['title' => 'Склад Казань', 'address' => 'г. Казань, ул. Крутовская, 26 склад №6'],
          ['title' => 'Склад Краснодар', 'address' => '350018, Краснодарский край, г Краснодар, ул Текстильная, д. 21'],
          ['title' => 'Склад Москва', 'address' => 'Каширское шоссе, 33-й километр, 5'],
          ['title' => 'Склад Ростов-на-Дону', 'address' => '344020, Ростовская обл, г Ростов-на-Дону, ул Механизаторов, д. 5'],
          ['title' => 'Склад Санкт-Петербург', 'address' => 'г. Санкт-Петербург, м.р-н Всеволожский, г.п. Свердловское, Покровская дорога (Уткина Заводь), ул Покровская дорога 59,865568 / 30,544388'],
        ];

        foreach ($data as $item) {
          Warehouse::firstOrCreate(
            ['title' => $item['title']],
            ['address' => $item['address'], 'phone' => $faker->phoneNumber(), 'tariff_pick' => 0, 'tariff_delivery' => 0],
          );
        }
    }
}
