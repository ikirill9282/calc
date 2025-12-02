<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'inn',
        'ogrn',
        'address',
        'name',
        'phone',
        'email',
        'disabled',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    /**
     * Получить пользователя, которому принадлежит контрагент
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
