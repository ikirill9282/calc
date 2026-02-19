<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SheetData extends Model
{
    protected static ?bool $hasWeekdayConfigColumns = null;

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

    public static function hasWeekdayConfigColumns(): bool
    {
        if (self::$hasWeekdayConfigColumns !== null) {
            return self::$hasWeekdayConfigColumns;
        }

        self::$hasWeekdayConfigColumns = Schema::hasColumns(
            (new static)->getTable(),
            ['delivery_weekdays_config', 'shipment_weekdays_config']
        );

        return self::$hasWeekdayConfigColumns;
    }
}
