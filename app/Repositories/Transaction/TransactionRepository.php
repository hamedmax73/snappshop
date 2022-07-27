<?php

namespace App\Repositories\Transaction;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Transaction\Transaction;

class TransactionRepository implements BaseRepositoryInterface
{
    public function getWhere($column, $value, array $related = null)
    {
        return Transaction::where($column, '=', $value)->with($related)->get();
    }

    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function update($id, array $data)
    {
        return Transaction::whereId($id)->update($data);
    }

    public function delete($id)
    {
        return Transaction::destroy($id);
    }

    public function deleteWhere($column, $value)
    {
        return Transaction::where($column, '=', $value)->delete();
    }
}
