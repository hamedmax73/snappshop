<?php

namespace App\Models\Account;

use App\Models\User\AccountNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCardNumber extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_number_id',
        'card_number',
        'expiry',
        'cvv',
    ];



    //Relations ===========================================================

    /**
     * return this credit card account relationship
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountNumber::class,'account_number_id');
    }
}
