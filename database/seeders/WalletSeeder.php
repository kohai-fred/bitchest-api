<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{

    public function run(): void
    {
        $clients = User::where('role', 'client')->get();

        foreach ($clients as $client) {
            Wallet::create([
                'user_id' => $client->id,
                'balance' => rand(10000, 100000) / 100, // Solde aléatoire arbitraire entre 100 et 1000
            ]);
        }
    }
}
