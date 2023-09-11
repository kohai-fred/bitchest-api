<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CryptoCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $cryptocurrencies = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ripple', 'symbol' => 'XRP', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bitcoin Cash', 'symbol' => 'BCH', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cardano', 'symbol' => 'ADA', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Litecoin', 'symbol' => 'LTC', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'NEM', 'symbol' => 'XEM', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Stellar', 'symbol' => 'XLM', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IOTA', 'symbol' => 'MIOTA', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dash', 'symbol' => 'DASH', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($cryptocurrencies as $crypto) {
            DB::table('crypto_currencies')->insert($crypto);
        }
    }
}
