<?php

namespace App\Repositories\User;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\User\User;

class UserRepository implements BaseRepositoryInterface
{

    public function getWhere($column, $value, array $related = null)
    {
        return User::where($column, '=', $value)->with($related)->get();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        return User::whereId($id)->update($data);
    }

    public function delete($id)
    {
        return User::destroy($id);
    }

    public function deleteWhere($column, $value)
    {
        return User::where($column, '=', $value)->delete();
    }

}
