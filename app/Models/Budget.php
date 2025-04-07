<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'period_type',
        'start_date',
        'end_date',
        'total_amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the budget items for the budget.
     */
    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    /**
     * Calculate the total planned amount from all budget items.
     */
    public function calculateTotalPlannedAmount()
    {
        return $this->budgetItems()->sum('planned_amount');
    }

    /**
     * Calculate the total actual amount spent from all budget items.
     */
    public function calculateTotalActualAmount()
    {
        return $this->budgetItems()->sum('actual_amount');
    }
}
