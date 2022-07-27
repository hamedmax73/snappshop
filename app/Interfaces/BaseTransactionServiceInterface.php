<?php

namespace App\Interfaces;

use App\Models\Account\CreditCardNumber;
use App\Models\User\AccountNumber;

interface BaseTransactionServiceInterface
{
    /**
     * get balance from credit card number
     * @param $card_number
     * @return int
     */
    public function get_balance($card_number):int;

    /**
     * check this card number has enough balance
     * @param $card_number
     * @param $amount
     * @return bool
     */
    public function check_balance($card_number,$amount):bool;

    /**
     * send money from credit card sender into receiver
     * @param $sender
     * @param $receiver
     * @param $amount
     * @return mixed
     */
    public function send_money($sender,$receiver,$amount);

    /**
     * save transaction in database
     * @param $sender_card_id
     * @param $receiver_card_id
     * @param $amount
     * @param $status
     * @return mixed
     */
    public function save_transaction($sender_card_id, $receiver_card_id, $amount, $status): mixed;

    /**
     * update a translation with given data
     * @param $transaction_id
     * @param array $data
     * @throws \Exception
     * @return bool
     */
    public function update_transaction($transaction_id,array $data): bool;

    /**
     * find account with card number
     * @param CreditCardNumber $credit_card_number
     * @return AccountNumber
     */
    public function find_account(CreditCardNumber $credit_card_number): AccountNumber;

    /**
     * find account with card number
     * @param $credit_card_number
     * @return CreditCardNumber
     */
    public function find_card($credit_card_number): CreditCardNumber;


    /**
     * change account balance
     * @param $account_id
     * @param $new_balance
     * @return mixed
     */
    public function change_account_balance($account_id,$new_balance): mixed;


}
