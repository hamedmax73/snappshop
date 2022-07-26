<?php

namespace Database\Factories\Account;

use App\Models\User\AccountNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account\CreditCardNumber>
 */
class CreditCardNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'account_number_id' => $this->faker->randomElement(AccountNumber::all())['id'],
            'card_number'    => $this->faker->unique()->creditCardNumber(),
            'expiry'    => $this->faker->creditCardExpirationDateString(true, 'Y-m-01'),
            'cvv'   => $this->faker->numerify('####')
        ];
    }
}
