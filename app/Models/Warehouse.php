<?php

namespace App\Models;

use App\Helpers\Slug;
use Illuminate\Database\Eloquent\Model;

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
}
