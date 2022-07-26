<?php

namespace App\Http\Requests\Transfer;

use App\Rules\PersianCreditCardNumber;
use Illuminate\Foundation\Http\FormRequest;

class CreditTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; //for test . in production must check
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //remove dash from inputs
        $this->merge([
            'receiver' => (string)preg_replace('/\D/', '', $this->receiver),
            'sender' => (string)preg_replace('/\D/', '', $this->sender),
        ]);
     }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'receiver'   => ['required','numeric','digits:16',new PersianCreditCardNumber],
            'sender'   => ['required','numeric','digits:16',new PersianCreditCardNumber],
        ];
    }
}
