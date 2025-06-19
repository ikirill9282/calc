<?php

use App\Models\Distributor;
use App\Models\SheetData;
use App\Models\Warehouse;
use Google\Service\Sheets\Sheet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\DB;
use App\Models\User;

Artisan::command('tt', function() {
  $sid = '1wDwv2bm9XWBuCfsxJD5DJbht7qKPDVkdwL_Agem0JKU';
  $attrs = Schema::getColumnListing('sheet_data');
  $header = array_values(array_filter($attrs, fn($val) => !in_array($val, ['id', 'created_at', 'updated_at'])));
  $rows = Sheets::spreadsheet($sid)->sheet('Лист1')->get();
  $rows->forget([0, 1]);

  $data = Sheets::collection($header, $rows)->toArray();
  $data = array_map(function($item) {
    $item['distributor_center_delivery_date'] = Carbon::parse($item['distributor_center_delivery_date'])->format('Y-m-d H:i:s');
    return $item;
  }, $data);

  DB::transaction(function() use ($data) {
    DB::table('sheet_data')->truncate();
    SheetData::query()->upsert($data, [], []);
  });
});