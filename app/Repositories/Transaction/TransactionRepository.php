<?php

namespace App\Repositories\Transaction;

use App\Interfaces\BaseRepositoryInterface;
use App\Models\Transaction\Transaction;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

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

    /**
     * return transaction with last time offset from now
     * @param $time_offset | unit:minute
     * @param null $limit
     * @return mixed
     */
    public function get_recent_users_with_transactions($time_offset, $limit = null): mixed
    {
        $user_ids = Transaction::activityOlderThan($time_offset)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->limit($limit)
            ->get()->pluck('user_id')->toArray();
        $users = User::whereIn('id', $user_ids)->get();
        foreach ($users as $user) {
            $user->load('latest_transactions');
        }
        return $users;
    }
}
