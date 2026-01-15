<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProjectDetail extends Model
{
    protected $fillable = [
        'event_project_id',
        'budget_item_id',
        'allocated_amount',
        'approved_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    /**
     * Get the BudgetItem that owns the EventProjectDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }
}
