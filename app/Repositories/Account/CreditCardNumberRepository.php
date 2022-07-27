<?php

namespace App\Repositories\Account;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Account\CreditCardNumber;

class CreditCardNumberRepository implements BaseRepositoryInterface
{

    public function getWhere($column, $value, array $related = null)
    {
        return CreditCardNumber::where($column, '=', $value)->with($related)->get();
    }

    public function create(array $data)
    {
        return CreditCardNumber::create($data);
    }

    public function update($id, array $data)
    {
        return CreditCardNumber::whereId($id)->update($data);
    }

    public function delete($id)
    {
        return CreditCardNumber::destroy($id);
    }

    public function deleteWhere($column, $value)
    {
        return CreditCardNumber::where($column, '=', $value)->delete();
    }

}
