<?php

namespace App\Models\User;

use App\Models\Account\CreditCardNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountNumber extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_number',
    ];



    //Relations ===========================================================

    /**
     * return relationship for this account number's user
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * return relationship for this account credit card's
     * @return HasMany
     */
    public function creditCardNumbers(): HasMany
    {
        return $this->hasMany(CreditCardNumber::class);
    }
}
