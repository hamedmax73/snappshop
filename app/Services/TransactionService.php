<?php

namespace App\Services;

use App\Interfaces\BaseRepositoryInterface;
use App\Interfaces\BaseTransactionServiceInterface;
use App\Models\Account\CreditCardNumber;
use App\Models\Transaction\Transaction;
use App\Models\User\AccountNumber;
use App\Repositories\Account\CreditCardNumberRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\User\AccountNumberRepository;
use Illuminate\Support\Facades\DB;

class TransactionService implements BaseTransactionServiceInterface
{
    public function __construct(private TransactionRepository      $transactionRepository,
                                private CreditCardNumberRepository $creditCardNumberRepository,
                                private AccountNumberRepository    $accountNumberRepository,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function get_balance($card_number): int
    {
        $credit_card = $this->creditCardNumberRepository->getWhere('card_number', $card_number, ['account'])->first();
        if (!empty($credit_card) && !empty($credit_card->account)) {
            return (int)$credit_card->account->balance;
        }
        return throw new \Exception('حساب کاربری متعلق به این شماره کارت یافت نشد.');
    }

    public function check_balance($card_number, $amount): bool
    {
        $current_balance = $this->get_balance($card_number);
        return $current_balance >= $amount;
    }

    /**
     * @throws \Exception
     */
    public function find_account(CreditCardNumber $credit_card_number): AccountNumber
    {
        if (!empty($credit_card_number) && !empty($credit_card_number->account)) {
            return $credit_card_number->account;
        }
        return throw new \Exception('حساب کاربری متعلق به این شماره کارت یافت نشد.');
    }
    /**
     * @throws \Exception
     */
    public function find_card($credit_card_number): CreditCardNumber
    {
        $credit_card = $this->creditCardNumberRepository->getWhere('card_number', $credit_card_number, ['account'])->first();
        if (!empty($credit_card)) {
            return $credit_card;
        }
        return throw new \Exception('این شماره کارت معتبر نیست.');
    }

    public function change_account_balance($account_id, $new_balance): mixed
    {
        return $this->accountNumberRepository->update($account_id, [
            'balance' => $new_balance
        ]);
    }

    /**
     * @throws \Exception
     */
    public function send_money($sender, $receiver, $amount)
    {
        //get card details
        $sender_card = $this->find_card($sender);
        $receiver_card = $this->find_card($receiver);
        //get account details
        $sender_account = $this->find_account($sender_card);
        $receiver_account = $this->find_account($receiver_card);

        //save transaction for records
        $transaction = $this->save_transaction($sender_card->id, $receiver_card->id, $amount, Transaction::STATUS_PENDING);

        //check sender has enough money
        $amount_with_fee = $amount + Transaction::TRANSACTION_FEE;
        if (!$this->check_balance($sender, $amount_with_fee)) {
            $this->update_transaction($transaction->id, [
                'status' => Transaction::STATUS_FAILED
            ]);
            return throw new \Exception('موجودی حساب کافی نیست.');
        }
        try {
            DB::transaction(function () use ($transaction, $amount, $sender_account, $receiver_account, $amount_with_fee) {
                //update account balances
                //decrease balance from sender with fee
                $this->change_account_balance($sender_account->id, $sender_account->balance - $amount_with_fee);
                //increase balance from receiver
                $this->change_account_balance($receiver_account->id, $receiver_account->balance + $amount);
                //save fee details

                //update transaction
                $this->update_transaction($transaction->id, [
                    'status' => Transaction::STATUS_SUCCESS
                ]);


            }, 3);
            return $transaction->id;
        } catch (\Exception $e) {
            //update transaction
            $this->update_transaction($transaction->id, [
                'status' => Transaction::STATUS_FAILED
            ]);
            return $e;
        }
    }

    public function save_transaction($sender_card_id, $receiver_card_id, $amount, $status): mixed
    {
        return $this->transactionRepository->create([
            'user_id' => '1',//this must be complete with auth
            'sender_card_id' => $sender_card_id,
            'receiver_card_id' => $receiver_card_id,
            'amount' => $amount,
            'status' => $status
        ]);
    }

    /**
     * @throws \Exception
     */
    public function update_transaction($transaction_id, array $data): bool
    {
        if (!$this->transactionRepository->update($transaction_id, $data)) {
            return throw new \Exception('در بروزرسانی حساب با مشکل روبرو شدیم');
        }
        return true;
    }
}
