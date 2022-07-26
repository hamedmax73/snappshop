<?php

namespace Database\Seeders;

use App\Models\User\AccountNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountNumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountNumber::factory()->count(30)->create();
    }
}
