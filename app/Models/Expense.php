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
        'School Supplies',
        'Load & Data',
        'Printing',
        'Org & Projects',
        'Savings',
        'Other',
    ];

    public const CATEGORY_ICONS = [
        'Food' => 'bi-cup-hot',
        'Transportation' => 'bi-bus-front',
        'Bills' => 'bi-receipt',
        'Shopping' => 'bi-bag',
        'Entertainment' => 'bi-controller',
        'School Supplies' => 'bi-pencil-square',
        'Load & Data' => 'bi-phone',
        'Printing' => 'bi-printer',
        'Org & Projects' => 'bi-people',
        'Savings' => 'bi-piggy-bank',
        'Other' => 'bi-three-dots',
    ];

    public const PAYMENT_METHODS = [
        'Cash',
        'GCash',
        'Maya',
        'Bank Transfer',
        'Debit Card',
        'Credit Card',
        'Other',
    ];

    public const PAYMENT_METHOD_ICONS = [
        'Cash' => 'bi-cash-coin',
        'GCash' => 'bi-phone',
        'Maya' => 'bi-wallet2',
        'Bank Transfer' => 'bi-bank',
        'Debit Card' => 'bi-credit-card-2-front',
        'Credit Card' => 'bi-credit-card',
        'Other' => 'bi-three-dots',
    ];

    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'category',
        'payment_method',
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
