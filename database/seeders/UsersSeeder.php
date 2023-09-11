<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        User::create([
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => 'admin1@example.com',
            'password' => Hash::make('admin1@example.comA1'),
            'role' => 'admin',
            'presentation' => $faker->realTextBetween(160, 200)
        ]);
        User::create([
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => 'admin2@example.com',
            'password' => Hash::make('admin2@example.comA1'),
            'role' => 'admin',
            'presentation' => $faker->realTextBetween(160, 200)
        ]);
        for ($i = 0; $i < 10; $i++) {
            $email = "client$i@example.com";
            $password = Hash::make($email . 'A1');

            User::create([
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $email,
                'password' => $password,
                'role' => 'client',
                'presentation' => $faker->realTextBetween(160, 200)
            ]);
        }
    }
}
