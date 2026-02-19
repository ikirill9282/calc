<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class ManagerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $people = [
      [
        'name' => 'Симонова Лукия Богдановна',
        'email' => 'vasilevereme@example.com',
        'phone' => '+70753295066'
      ],
      [
        'name' => 'Хохлов Фадей Григорьевич',
        'email' => 'pahomovaakulina@example.org',
        'phone' => '+76921567645'
      ],
      [
        'name' => 'Белоусов Капитон Артёмович',
        'email' => 'ratibor69@example.net',
        'phone' => '+74002642130'
      ],
      [
        'name' => 'Вероника Максимовна Воронова',
        'email' => 'erast_37@example.org',
        'phone' => '+72724159534'
      ],
      [
        'name' => 'Кириллов Епифан Фомич',
        'email' => 'sidorovaregina@example.net',
        'phone' => '+79209341210'
      ],
    ];

    foreach ($people as $person) {
      \App\Models\Manager::firstOrCreate([
        'name' => $person['name'],
        'email' => $person['email'],
        'phone' => $person['phone'],
      ]);
    }
  }
}
