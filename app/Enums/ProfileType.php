<?php

namespace App\Enums;

use App\Models\Expense;
use App\Models\Income;

enum ProfileType: string
{
    case StudentAllowance = 'student_allowance';
    case Working = 'working';
    case WorkingStudent = 'working_student';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::StudentAllowance => 'Student',
            self::Working => 'Employee',
            self::WorkingStudent => 'Working student',
            self::Other => 'Something else',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::StudentAllowance => 'bi-mortarboard',
            self::Working => 'bi-briefcase',
            self::WorkingStudent => 'bi-person-workspace',
            self::Other => 'bi-three-dots',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::StudentAllowance => 'Allowance, school stuff, and weekly budgets.',
            self::Working => 'Salary, paydays, and everyday bills.',
            self::WorkingStudent => 'A salary and an allowance, tracked together.',
            self::Other => 'For freelancers, gigs, and everything in between.',
        };
    }

    public function defaultIncomeSource(): ?string
    {
        return match ($this) {
            self::StudentAllowance => 'Allowance',
            self::Working => 'Salary',
            default => null,
        };
    }

    public function defaultIncomeFrequency(): string
    {
        return match ($this) {
            self::StudentAllowance => 'weekly',
            self::Working, self::WorkingStudent => 'monthly',
            self::Other => 'one_time',
        };
    }

    public function budgetLabel(): string
    {
        return match ($this) {
            self::StudentAllowance => 'Daily allowance',
            default => 'Daily budget',
        };
    }

    /**
     * @return list<string>
     */
    public function expenseCategories(): array
    {
        return match ($this) {
            self::StudentAllowance => array_values(array_diff(Expense::CATEGORIES, ['Bills'])),
            self::Working => array_values(array_diff(Expense::CATEGORIES, [
                'School Supplies',
                'Load & Data',
                'Printing',
                'Org & Projects',
            ])),
            self::WorkingStudent, self::Other => Expense::CATEGORIES,
        };
    }

    /**
     * @return list<string>
     */
    public function incomeSources(): array
    {
        return match ($this) {
            self::StudentAllowance => [
                'Allowance',
                'Scholarship',
                'Stipend',
                'Part-time',
                'Freelance',
                'Gift',
                'Other',
            ],
            self::Working => [
                'Salary',
                'Business',
                'Freelance',
                'Part-time',
                'Gift',
                'Refund',
                'Other',
            ],
            self::WorkingStudent, self::Other => Income::SOURCES,
        };
    }
}
