<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoCotation extends Model
{
    use HasFactory;

    protected $fillable = ['crypto_currency_id', 'price', 'timestamp'];

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class, 'crypto_currency_id');
    }
}
