<?php

namespace App\Models\Transaction;

use App\Models\Account\CreditCardNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\Pure;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=[
        'user_id',
        'sender_card_id',
        'receiver_card_id',
        'amount',
        'status'
    ];
    //Const's ===========================================================
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_BLOCKED = 3;

    const MINIMUM_TRANSACTION=10000; //UNIT:RIAL
    const MAXIMUM_TRANSACTION=500000000; //UNIT:RIAL
    const TRANSACTION_FEE = 5000; // UNIT:RIAL


    //Functions =========================================================
    /**
     *  return status name for given status id
     *
     * @param $status
     * @return string
     */
    public static function statusNameFor($status): string
    {
        switch ($status) {
            case static::STATUS_SUCCESS:
                return 'موفق';
            case static::STATUS_FAILED:
                return 'ناموفق';
            case static::STATUS_PENDING:
                return 'در حال رسیدگی';
            case static::STATUS_BLOCKED:
                return 'مسدود شده';
        }
        return "تعریف نشده";
    }

    #[Pure] public function statusName(): string
    {
        return static::statusNameFor($this->status);
    }


    //Relations =========================================================

    /**
     * return sender of this transaction
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(CreditCardNumber::class,'sender_card_id');
    }

    /**
     * return receiver of this transaction
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(CreditCardNumber::class,'receiver_card_id');
    }
}
