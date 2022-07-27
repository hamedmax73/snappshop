<?php

namespace App\Http\Requests\Transfer;

use App\Models\Transaction\Transaction;
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
            'sender' => ['required', 'numeric', 'digits:16', new PersianCreditCardNumber, 'exists:credit_card_numbers,card_number'],
            'receiver' => ['different:sender', 'required', 'numeric', 'digits:16', new PersianCreditCardNumber, 'exists:credit_card_numbers,card_number'],
            'amount' => ['required', 'numeric', 'min:' . Transaction::MINIMUM_TRANSACTION, 'max:' . Transaction::MAXIMUM_TRANSACTION]
        ];
    }

    public function messages()
    {
        return [
            '*.exists' => 'شماره کارت در این بانک موجود نیست',
            'amount.min' => 'حداقل مبلغ تراکنش ' . Transaction::MINIMUM_TRANSACTION . ' تومان است',
            'amount.max' => 'حداکثر مبلغ تراکنش ' . Transaction::MAXIMUM_TRANSACTION . ' تومان است',
        ];

    }
}
