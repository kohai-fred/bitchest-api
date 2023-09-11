<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoCurrency extends Model
{
    use HasFactory;

    public function latestCotation()
    {
        return $this->hasOne(CryptoCotation::class)
            ->orderByDesc('timestamp')
            ->latest();
    }
}
