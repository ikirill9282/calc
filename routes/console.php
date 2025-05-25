<?php

use App\Helpers\Slug;
use App\Models\Warehouse;
use App\Services\DadataClient;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('tt', function() {
  $client = new DadataClient();
  $res = $client->suggest("address", "мск балаклавский 36");
});