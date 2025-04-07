<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'transaction_category_id',
        'planned_amount',
        'actual_amount',
        'notes',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    /**
     * Get the budget that owns the budget item.
     */
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the transaction category associated with the budget item.
     */
    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }

    /**
     * Calculate the percentage of the budget used.
     */
    public function getPercentageUsedAttribute()
    {
        if ($this->planned_amount <= 0) {
            return 0;
        }
        
        return min(100, round(($this->actual_amount / $this->planned_amount) * 100, 2));
    }

    /**
     * Calculate the remaining amount in the budget.
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->planned_amount - $this->actual_amount);
    }
}
