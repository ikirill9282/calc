<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Revolution\Google\Sheets\Facades\Sheets;


class WriteSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:write-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $sheet = Sheets::spreadsheet('1RCZPm9Q-A-1osteZkMlwYYCkuLJ0em1zKpKZGOnC6is')
        ->sheet("Лист1")
        ->range('')
        ;
      
      foreach (Order::all() as $order) {
        $data = $order->prepareSheetData();
        $sheet->append($data, 'USER_ENTERED');
        $order->print()->firstOrCreate();
      }
    }
}
