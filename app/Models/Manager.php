<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    public function distributorCenters()
    {
      return $this->hasMany(DistributorCenter::class);
    }
}
