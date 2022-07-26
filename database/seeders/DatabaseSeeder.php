<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Account\CreditCardNumber;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class); // seed user data
        $this->call(AccountNumberTableSeeder::class);
        $this->call(CreditCardNumberTableSeeder::class);
    }
}
