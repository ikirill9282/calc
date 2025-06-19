<?php

namespace App\Models;

use App\Helpers\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Warehouse extends Model
{
    public static function boot()
    {
      parent::boot();
      
      static::saving(function($model) {
        if (!isset($model->slug)) {
          $model->slug = Slug::make($model->title);
        }
        return $model;
      });
      
      static::creating(function($model) {
        if (!isset($model->slug)) {
          $model->slug = Slug::make($model->title);
        }
        return $model;
      });
    }


    public function phone(): Attribute
    {
      return Attribute::make(
        set: fn($val) => preg_replace('/[^0-9]+/is', '', $val),
      );
    }
}
