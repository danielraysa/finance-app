<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_flow_id',
        'cash_account_id',
        'transaction_category_id',
        'type',
        'amount',
        'transaction_date',
        'reference_number',
        'description',
        'attachment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the cash flow that owns the transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashFlow()
    {
        return $this->belongsTo(CashFlow::class, 'cash_flow_id');
    }

    /**
     * Get the cash account that owns the transaction.
     */
    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }
}
