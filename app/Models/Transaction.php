<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crypto_currency_id',
        'type',
        'quantity',
        'price',
        'timestamp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cryptoCurrency()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }

    public function sellCrypto()
    {
        // Vérifie s'il reste de la crypto à vendre
        if ($this->quantity > 0) {
            // Récupére la cotation la plus récente de la crypto
            $latestCotation = CryptoCotation::where('crypto_currency_id', $this->crypto_currency_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Vérifie si une cotation est disponible
            if ($latestCotation) {
                // Calcule le montant de la vente au prix de la cotation la plus récente
                $sellAmount = $this->quantity * $latestCotation->price;

                Transaction::create([
                    'user_id' => $this->user_id,
                    'crypto_currency_id' => $this->crypto_currency_id,
                    'quantity' => -$this->quantity, // Quantité négative pour la vente
                    'price' => $latestCotation->price,
                    'type' => 'sell',
                ]);

                // Met à jour la quantité de la transaction d'achat
                $this->quantity = 0;
                $this->save();

                return $sellAmount;
            }
        }

        return 0;
    }
}
