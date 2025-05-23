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
          ],
          [
            'title' => 'Ozon',
          ],
          [
            'title' => 'ЯндексМаркет',
          ],
          [
            'title' => 'Магнит Маркет',
          ],
          [
            'title' => 'КазаньЭкспресс',
          ],
          [
            'title' => 'Детский мир',
          ],
        ];

        foreach ($data as $item) {
          Distributor::firstOrCreate(
            ['title' => $item['title']],
            ['title' => $item['title']],
          );
        }
    }
}
