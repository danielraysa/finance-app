<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventProject extends Model
{
    protected $fillable = [
        'event_name',
        'event_date',
        'location',
        'description',
        'note',
        'user_id',
        'status',
        'verified_by',
        'verified_date',
        'complete_date',
        'attachment',
        'cash_flow_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'verified_date' => 'date',
        'complete_date' => 'date',
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
     * Get all of the details for the EventProject
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(EventProjectDetail::class);
    }

    /**
     * The transactions (details) for this cash flow.
     */
    public function cashFlow()
    {
        return $this->belongsTo(CashFlow::class);
    }
}
