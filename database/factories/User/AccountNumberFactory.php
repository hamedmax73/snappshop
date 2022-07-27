<?php

namespace Database\Factories\User;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\AccountNumber>
 */
class AccountNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id'   => $this->faker->randomElement(User::all())['id'],
            'account_number'    => $this->faker->unique()->numerify('################'),
            'balance'   => $this->faker->numberBetween(10000,600000000)
        ];
    }
}
