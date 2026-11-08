<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ProfileType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PROFILE_TYPES = [
        'student_allowance' => 'Student using allowance',
        'working' => 'Working',
        'working_student' => 'Working student',
        'other' => 'Other',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_type',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function dailyBudgets()
    {
        return $this->hasMany(DailyBudget::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function profile(): ?ProfileType
    {
        return ProfileType::tryFrom((string) $this->profile_type);
    }

    public function defaultIncomeSource(): ?string
    {
        return $this->profile()?->defaultIncomeSource();
    }

    public function defaultIncomeFrequency(): string
    {
        return $this->profile()?->defaultIncomeFrequency() ?? 'one_time';
    }

    public function budgetLabel(): string
    {
        return $this->profile()?->budgetLabel() ?? 'Daily budget';
    }

    /**
     * @return list<string>
     */
    public function expenseCategories(): array
    {
        return $this->profile()?->expenseCategories() ?? Expense::CATEGORIES;
    }

    /**
     * @return list<string>
     */
    public function incomeSources(): array
    {
        return $this->profile()?->incomeSources() ?? Income::SOURCES;
    }
}
