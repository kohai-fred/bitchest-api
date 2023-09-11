<?php

namespace Database\Seeders;

use App\Models\CryptoCotation;
use App\Models\CryptoCurrency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class TransactionSeeder extends Seeder
{

    public function run()
    {
        $clients = User::where('role', 'client')->get();
        $cryptoCurrencies = CryptoCurrency::all();

        foreach ($clients as $client) {
            $cryptoCount = mt_rand(2, 5);
            $cryptoIds = $cryptoCurrencies->random($cryptoCount)->pluck('id')->toArray();
            $startDate = Carbon::parse(CryptoCotation::orderBy('timestamp', 'asc')->first()->timestamp);

            for ($i = 0; $i < 30; $i++) {
                $cryptoId = $cryptoIds[array_rand($cryptoIds)];
                $crypto = CryptoCurrency::find($cryptoId);
                $quantity = mt_rand(1, 100) / 10; // Quantité entre 0.01 et 10.00

                // Vérifie si le client possède cette crypto-monnaie
                $hasCrypto = $client->transactions()->where('crypto_currency_id', $cryptoId)->sum('quantity');

                if ($i === 0 || ($i > 0 && $hasCrypto === 0)) {
                    // La première transaction est un achat
                    $cryptoCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
                        ->where('timestamp', $startDate)
                        ->first();

                    if ($cryptoCotation) {
                        $price = $quantity * $cryptoCotation->price;

                        Transaction::create([
                            'user_id' => $client->id,
                            'crypto_currency_id' => $cryptoId,
                            'quantity' => $quantity,
                            'price' => $price,
                            'type' => 'buy',
                            'created_at' => $startDate,
                        ]);
                    }
                } else {
                    // Les transactions suivantes peuvent être des achats ou des ventes
                    $transactionType = mt_rand(0, 1) ? 'buy' : 'sell';

                    if ($transactionType === 'buy') {
                        $cryptoCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
                            ->where('timestamp', $startDate)
                            ->first();

                        if ($cryptoCotation) {
                            $price = $quantity * $cryptoCotation->price;

                            Transaction::create([
                                'user_id' => $client->id,
                                'crypto_currency_id' => $cryptoId,
                                'quantity' => $quantity,
                                'price' => $price,
                                'type' => 'buy',
                                'created_at' => $startDate,
                            ]);
                        }
                    } else {
                        // Vérifie si le client peut vendre cette quantité de crypto-monnaie
                        $maxSaleQuantity = $client->transactions()
                            ->where('crypto_currency_id', $cryptoId)
                            ->where('type', 'buy')
                            ->sum('quantity') - $client->transactions()
                            ->where('crypto_currency_id', $cryptoId)
                            ->where('type', 'sell')
                            ->sum('quantity');

                        if ($maxSaleQuantity > 0) {
                            // Vend la totalité de la crypto non vendue
                            $quantity = $maxSaleQuantity;

                            $cryptoCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
                                ->where('timestamp', $startDate)
                                ->first();

                            if ($cryptoCotation) {
                                $price = $quantity * $cryptoCotation->price;

                                Transaction::create([
                                    'user_id' => $client->id,
                                    'crypto_currency_id' => $cryptoId,
                                    'quantity' => $quantity,
                                    'price' => $price,
                                    'type' => 'sell',
                                    'created_at' => $startDate,
                                ]);
                            }
                        }
                    }
                }

                $startDate->addDay(); // Passe au jour suivant
            }
        }
    }
}
