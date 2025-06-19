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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sid = '1ZOkCAKId9W5nAQya3ZFC1GyYeeGM-Mbne7U-F44Zw-E';
        $attrs = Schema::getColumnListing('sheet_data');
        $header = array_values(array_filter($attrs, fn($val) => !in_array($val, ['id', 'created_at', 'updated_at'])));
        $rows = Sheets::spreadsheet($sid)->sheet('Лист1')->get();
        $rows->forget([0, 1, 2]);

        $data = Sheets::collection($header, $rows)->toArray();
        $data = array_map(function($item) {
          $item['distributor_center_delivery_date'] = Carbon::parse($item['distributor_center_delivery_date'])->format('Y-m-d H:i:s');
          $item['delivery_diff'] = Carbon::parse($item['delivery_diff'])->format('Y-m-d H:i:s');
          $item['pick_diff'] = Carbon::parse($item['pick_diff'])->format('Y-m-d H:i:s');

          return array_map(fn($val) => trim($val), $item);
        }, $data);

        DB::beginTransaction();
        try {
          SheetData::where('id', '>', 0)->delete();
          foreach ($data as $item) {
            SheetData::create($item);
          }
        } catch (\Exception $e) {
          Log::error('Error while loading sheet data', ['error' => $e]);
          DB::rollBack();
          return 500;
        }

        DB::commit();
        return 200;
    }
}
