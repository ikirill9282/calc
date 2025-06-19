<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
          [
            'title' => 'Wildberries',
            'logo' => 'wb',
          ],
          [
            'title' => 'Ozon',
            'logo' => 'ozon',
          ],
          [
            'title' => 'ЯндексМаркет',
            'logo' => 'ymarket',
          ],
          // [
          //   'title' => 'Магнит Маркет',
          //   'logo' => 'mm',
          // ],
          // // [
          // //   'title' => 'КазаньЭкспресс',
          // // ],
          // [
          //   'title' => 'Детский мир',
          //   'logo' => 'mir',
          // ],
        ];

        foreach ($data as $item) {
          Distributor::firstOrCreate(
            ['title' => $item['title']],
            $item,
          );
        }
    }
}
