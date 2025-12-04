<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use App\Mail\Confirm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'admin' => 'Администратор',
        'manager' => 'Менеджер',
        'user' => 'Пользователь',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
      'password' => 'hashed',
    ];

    public function phone(): Attribute
    {
      return Attribute::make(
        set: fn($val) => preg_replace('/[^0-9]+/is', '', $val),
      );
    }

    public function sendEmailVerification()
    {
      $mail = new Confirm($this);
      // Mail::to($this)->send($mail);
    }

    public function getVerificationUrl()
    {
      return url('/auth/verify', ['u' => Crypt::encrypt($this->id)]);
    }

    public static function validatePassword(string $password): bool
    {
      return preg_match( '/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{6,}$/is', $password);
    }

    public function makeResetUrl(): string
    {
      return url('/auth/reset') . '?p=' . Crypt::encrypt(['id' => $this->id, 'expires' => Carbon::now()->modify('+5 minutes')->timestamp]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
      return in_array($this->role, ['admin', 'manager'], true);
    }
}
