<?php

namespace Database\Seeders;

use App\Models\CryptoCotation;
use App\Models\CryptoCurrency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

require_once(base_path('scripts/cotation_generator.php'));

class CryptoCotationSeeder extends Seeder
{
    public function run()
    {
        $cryptocurrencies = CryptoCurrency::all();

        foreach ($cryptocurrencies as $crypto) {
            $cryptoName = $crypto->name;
            $cryptoId = $crypto->id;

            $firstCotation = getFirstCotation($cryptoName); // Génère la première cotation pour chaque cryptomonnaie

            // Enregistre la première cotation dans la table crypto_cotations
            CryptoCotation::create([
                'crypto_currency_id' => $cryptoId,
                'price' => $firstCotation,
                'timestamp' => now()->subDays(30),
                'created_at' => now()->subDays(30),
            ]);

            // Génère les cotations aléatoires sur une période donnée
            for ($daysAgo = 29; $daysAgo >= 1; $daysAgo--) {
                $cotationChange = getCotationFor($cryptoName);
                $previousCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
                    ->latest('created_at')
                    ->first();

                // Calcul de la nouvelle cotation basée sur la précédente
                $newCotation = max($previousCotation->price + $cotationChange, 0);

                CryptoCotation::create([
                    'crypto_currency_id' => $cryptoId,
                    'price' => $newCotation,
                    'timestamp' => now()->subDays($daysAgo),
                    'created_at' => now()->subDays($daysAgo),
                ]);
            }
        }
    }
}
