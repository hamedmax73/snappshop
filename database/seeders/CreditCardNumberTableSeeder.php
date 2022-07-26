<?php

namespace Database\Seeders;

use App\Models\Account\CreditCardNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreditCardNumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create valid card numbers
        CreditCardNumber::create([
            'account_number_id' => '1',
            'card_number'   => '6104337401657323',
            'expiry'    => '2025-01-01',
            'cvv'   => '2233'
        ]);
        CreditCardNumber::create([
            'account_number_id' => '2',
            'card_number'   => '5859831160249800',
            'expiry'    => '2025-01-01',
            'cvv'   => '2433'
        ]);

        CreditCardNumber::factory()->count('10')->create();
    }
}
