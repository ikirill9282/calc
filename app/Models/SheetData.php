<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetData extends Model
{
    public $timestamps = false;

    protected $table = 'sheet_data';

    protected $guarded = ['id'];

    protected $casts = [
        'distributor_center_delivery_date' => 'date',
        'delivery_weekdays_config' => 'array',
        'shipment_weekdays_config' => 'array',
        'delivery_diff' => 'datetime',
        'pick_diff' => 'datetime',
        'delivery_weekend' => 'integer',
        'pick_tariff_min' => 'decimal:2',
        'pick_tariff_vol' => 'decimal:2',
        'pick_tariff_pallete' => 'decimal:2',
        'delivery_tariff_min' => 'decimal:2',
        'delivery_tariff_vol' => 'decimal:2',
        'delivery_tariff_pallete' => 'decimal:2',
    ];
}
