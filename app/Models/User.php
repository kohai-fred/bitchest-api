<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'role',
        'password',
        'presentation'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function totalCryptoBuy($cryptoId)
    {
        return $this->transactions()
            ->where('crypto_currency_id', $cryptoId)
            ->where('type', 'buy')
            ->sum('quantity');
    }

    public function totalCryptoSell($cryptoId)
    {
        return $this->transactions()
            ->where('crypto_currency_id', $cryptoId)
            ->where('type', 'sell')
            ->sum('quantity');
    }

    public function totalQuantityToSell($cryptoId)
    {
        $quantityBuy = $this->totalCryptoBuy($cryptoId);
        $quantitySell = $this->totalCryptoSell($cryptoId);
        return round($quantityBuy - $quantitySell, 2);
    }
}
