<?php

namespace App\Repositories\User;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\User\User;

class UserRepository implements BaseRepositoryInterface
{

    public function all()
    {
        return User::all();
    }

}
