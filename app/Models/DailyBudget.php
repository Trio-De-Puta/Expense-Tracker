<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyBudget extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'budget_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'budget_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
