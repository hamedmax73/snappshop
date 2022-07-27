<?php

namespace App\Repositories\User;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\User\AccountNumber;

class AccountNumberRepository implements BaseRepositoryInterface
{

    public function getWhere($column, $value, array $related = null)
    {
        return AccountNumber::where($column, '=', $value)->with($related)->get();
    }

    public function create(array $data)
    {
        return AccountNumber::create($data);
    }

    public function update($id, array $data)
    {
        return AccountNumber::findOrFail($id)->update($data);
    }

    public function delete($id)
    {
        return AccountNumber::destroy($id);
    }

    public function deleteWhere($column, $value)
    {
        return AccountNumber::where($column, '=', $value)->delete();
    }
}
