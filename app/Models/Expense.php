<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = [
        'Food',
        'Transportation',
        'Bills',
        'Shopping',
        'Entertainment',
        'Other',
    ];

    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'category',
        'expense_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
