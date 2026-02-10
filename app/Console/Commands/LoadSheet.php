<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SheetData;
use Illuminate\Support\Facades\Log;

class LoadSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    protected static array $headers = [
      0 => "wh",
      1 => "wh_address",
      2 => "map",
      3 => "distributor",
      4 => "distributor_center",
      5 => "distributor_address",
      6 => "distributor_center_delivery_date",
      7 => "delivery_diff",
      8 => "delivery_weekend",
      9 => "pick_diff",
      10 => "pick_weekend",
      11 => "pick_tariff_min",
      12 => "pick_tariff_vol",
      13 => "pick_tariff_pallete",
      14 => "pick_additional",
      15 => "delivery_tariff_min",
      16 => "delivery_tariff_vol",
      17 => "delivery_tariff_pallete",
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sid = '1ZOkCAKId9W5nAQya3ZFC1GyYeeGM-Mbne7U-F44Zw-E';
        $rows = Sheets::spreadsheet($sid)->sheet('Лист1')->get();
        $rows->forget([0, 1, 2]);

        $data = Sheets::collection(static::$headers, $rows)->toArray();

        $data = array_map(function($item) {
            try {
              $item['distributor_center_delivery_date'] = Carbon::parse($item['distributor_center_delivery_date'])->format('Y-m-d H:i:s');
              $item['delivery_diff'] = Carbon::parse($item['delivery_diff'])->format('Y-m-d H:i:s');
              $item['pick_diff'] = Carbon::parse($item['pick_diff'])->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
              dd($item);
            }

            $item['delivery_weekend'] = empty($item['delivery_weekend']) ? 0 : $item['delivery_weekend'];
            $item['pick_weekend'] = empty($item['pick_weekend']) ? 0 : $item['pick_weekend'];

            return array_map(fn($val) => trim($val), $item);
          }, $data);

        DB::beginTransaction();
        try {
          SheetData::where('id', '>', 0)->delete();
          foreach ($data as $item) {
            unset($item['phone'], $item['opened']);
            SheetData::create($item);
          }
        } catch (\Exception $e) {
          Log::error('Error while loading sheet data', ['error' => $e]);
          DB::rollBack();
          return 500;
        }

        DB::commit();
        Log::info('Sheet data loaded.');
        return 200;
    }
}
