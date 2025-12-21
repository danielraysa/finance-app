<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_date',
        'reference_number',
        'description',
        'attachment',
        'verified_by',
        'verified_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'verified_date' => 'date',
    ];


    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the user that owns the transaction.
     */
    public function verificator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * The transactions (details) for this cash flow.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cash_flow_id');
    }

}
