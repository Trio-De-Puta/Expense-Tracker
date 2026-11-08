<?php

namespace App\Support;

use App\Models\Income;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class IncomeSchedule
{
    public static function nextOccurrence(Income $income): ?Carbon
    {
        $today = Carbon::today();

        return match ($income->frequency) {
            'weekly' => self::nextWeekly($income, $today),
            'monthly' => self::nextMonthly($income, $today),
            'bi_monthly' => self::nextBiMonthly($income, $today),
            default => null,
        };
    }

    private static function nextWeekly(Income $income, Carbon $today): Carbon
    {
        $date = $income->income_date
            ? $income->income_date->copy()->startOfDay()
            : $today->copy();

        while ($date->lt($today)) {
            $date->addWeek();
        }

        return $date;
    }

    private static function nextMonthly(Income $income, Carbon $today): Carbon
    {
        $payDay = (int) ($income->pay_day ?? 0);

        $candidate = self::dateForDay($today->year, $today->month, $payDay);

        if ($candidate->lt($today)) {
            $candidate = self::dateForDay(
                $today->copy()->addMonthNoOverflow()->year,
                $today->copy()->addMonthNoOverflow()->month,
                $payDay
            );
        }

        return $candidate;
    }

    private static function nextBiMonthly(Income $income, Carbon $today): Carbon
    {
        $payDays = [(int) ($income->pay_day ?? 0)];

        if ($income->pay_day_2 !== null) {
            $payDays[] = (int) $income->pay_day_2;
        }

        $candidates = collect($payDays)
            ->unique()
            ->map(fn (int $day) => self::dateForDay($today->year, $today->month, $day))
            ->filter(fn (Carbon $date) => $date->gte($today))
            ->sort()
            ->values();

        if ($candidates->isNotEmpty()) {
            return $candidates->first();
        }

        $nextMonth = $today->copy()->addMonthNoOverflow();

        return collect($payDays)
            ->unique()
            ->map(fn (int $day) => self::dateForDay($nextMonth->year, $nextMonth->month, $day))
            ->sort()
            ->values()
            ->first();
    }

    private static function dateForDay(int $year, int $month, int $day): Carbon
    {
        $firstOfMonth = Carbon::create($year, $month, 1)->startOfDay();

        if ($day === 0) {
            return $firstOfMonth->copy()->endOfMonth()->startOfDay();
        }

        return $firstOfMonth->copy()->day(min($day, $firstOfMonth->daysInMonth));
    }
}
