<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PersianCreditCardNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     * based on http://www.aliarash.com/article/creditcart/credit-debit-cart.htm
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $card = (string)preg_replace('/\D/', '', $value);
        $res = null;
        $card_number_length = strlen($card);
        if ($card_number_length != 16)
            return false;
        if (!in_array($card[0], [2, 4, 5, 6, 9]))
            //mean this card is not a credit card
            return false;
        for ($i = 0; $i < $card_number_length; $i++) {
            $res[$i] = $card[$i];
            if (($card_number_length % 2) == ($i % 2)) {
                $res[$i] *= 2;
                if ($res[$i] > 9)
                    $res[$i] -= 9;
            }
        }

        if (array_sum($res) % 10 !== 0) {
           return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('validation.persian_card_number');
    }
}
