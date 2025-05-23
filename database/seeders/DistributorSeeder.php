<?php

namespace Database\Seeders;

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
    }
}
