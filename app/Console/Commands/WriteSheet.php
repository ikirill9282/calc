<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Log;


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
      $orders = Order::whereDoesntHave('print')
        ->where('created_at', '<', Carbon::now()->modify('-1 minute'))
        ->get();

      foreach ($orders as $order) {
        $data = $order->prepareSheetData();
        $sheet->append($data, 'USER_ENTERED');
        $order->print()->firstOrCreate();
      }
    }
}
