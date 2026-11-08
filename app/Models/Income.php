<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    public const SOURCES = [
        'Salary',
        'Allowance',
        'Freelance',
        'Business',
        'Scholarship',
        'Stipend',
        'Part-time',
        'Gift',
        'Refund',
        'Other',
    ];

    public const SOURCE_ICONS = [
        'Salary' => 'bi-cash-stack',
        'Allowance' => 'bi-wallet2',
        'Freelance' => 'bi-laptop',
        'Business' => 'bi-shop',
        'Scholarship' => 'bi-mortarboard',
        'Stipend' => 'bi-award',
        'Part-time' => 'bi-clock',
        'Gift' => 'bi-gift',
        'Refund' => 'bi-arrow-counterclockwise',
        'Other' => 'bi-three-dots',
    ];

    public const FREQUENCIES = [
        'one_time' => 'One-time',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'bi_monthly' => 'Bi-monthly',
    ];

    protected $fillable = [
        'user_id',
        'amount',
        'source',
        'description',
        'income_date',
        'frequency',
        'pay_day',
        'pay_day_2',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'pay_day' => 'integer',
        'pay_day_2' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return $this->description ?: $this->source;
    }

    public function frequencyLabel(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? self::FREQUENCIES['one_time'];
    }

    public function paydayLabel(): ?string
    {
        if ($this->frequency === 'one_time' || $this->pay_day === null) {
            return null;
        }

        $days = [$this->pay_day];

        if ($this->frequency === 'bi_monthly' && $this->pay_day_2 !== null) {
            $days[] = $this->pay_day_2;
        }

        $labels = array_map(
            fn ($day) => (int) $day === 0 ? 'Last day' : $this->ordinal((int) $day),
            $days
        );

        return implode(' & ', $labels);
    }

    private function ordinal(int $day): string
    {
        if ($day % 100 >= 11 && $day % 100 <= 13) {
            return $day.'th';
        }

        return $day.match ($day % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
