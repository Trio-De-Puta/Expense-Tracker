<?php

namespace Tests\Feature;

use App\Models\DailyBudget;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_income(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/incomes', [
                'amount' => '2500.00',
                'source' => 'Salary',
                'description' => 'Monthly salary',
                'income_date' => now()->toDateString(),
                'frequency' => 'one_time',
            ])
            ->assertRedirect('/dashboard');

        $income = Income::where('user_id', $user->id)->first();

        $this->assertNotNull($income);
        $this->assertSame('2500.00', $income->amount);
        $this->assertSame('Salary', $income->source);
        $this->assertSame('Monthly salary', $income->description);
        $this->assertSame(now()->toDateString(), $income->income_date->toDateString());
        $this->assertSame('one_time', $income->frequency);
        $this->assertNull($income->pay_day);
        $this->assertNull($income->pay_day_2);
    }

    public function test_backdated_income_redirects_to_that_day_in_history(): void
    {
        $user = User::factory()->create();
        $yesterday = now()->subDay()->toDateString();

        $this->actingAs($user)
            ->post('/incomes', [
                'amount' => '500.00',
                'source' => 'Freelance',
                'description' => 'Side project',
                'income_date' => $yesterday,
                'frequency' => 'one_time',
            ])
            ->assertRedirect(route('expenses.daily', ['from' => $yesterday, 'to' => $yesterday]));

        $income = Income::where('user_id', $user->id)->first();

        $this->assertNotNull($income);
        $this->assertSame($yesterday, $income->income_date->toDateString());
    }

    public function test_income_amount_must_be_positive(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/incomes', [
            'amount' => '0.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ])->assertSessionHasErrors('amount');

        $this->post('/incomes', [
            'amount' => '-5.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, Income::where('user_id', $user->id)->count());
    }

    public function test_income_source_and_date_are_validated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Lottery',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ])->assertSessionHasErrors('source');

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Gift',
            'income_date' => now()->addDay()->toDateString(),
            'frequency' => 'one_time',
        ])->assertSessionHasErrors('income_date');

        $this->assertSame(0, Income::where('user_id', $user->id)->count());
    }

    public function test_user_can_edit_their_own_income(): void
    {
        $user = User::factory()->create();

        $income = Income::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'source' => 'Allowance',
            'description' => 'Weekly allowance',
            'income_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/incomes/'.$income->id.'/edit')
            ->assertOk()
            ->assertSee('Weekly allowance');

        $this->actingAs($user)
            ->put('/incomes/'.$income->id, [
                'amount' => '750.00',
                'source' => 'Freelance',
                'description' => 'Side project',
                'income_date' => now()->toDateString(),
                'frequency' => 'bi_monthly',
                'pay_day' => '15',
                'pay_day_2' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'amount' => '750.00',
            'source' => 'Freelance',
            'description' => 'Side project',
            'frequency' => 'bi_monthly',
            'pay_day' => 15,
            'pay_day_2' => 0,
        ]);
    }

    public function test_user_can_delete_their_own_income(): void
    {
        $user = User::factory()->create();

        $income = Income::create([
            'user_id' => $user->id,
            'amount' => 250.00,
            'source' => 'Gift',
            'income_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->delete('/incomes/'.$income->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_users_cannot_modify_each_others_income(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $income = Income::create([
            'user_id' => $owner->id,
            'amount' => 900.00,
            'source' => 'Salary',
            'description' => 'Private income',
            'income_date' => now()->toDateString(),
        ]);

        $this->actingAs($other)
            ->get('/incomes/'.$income->id.'/edit')
            ->assertForbidden();

        $this->actingAs($other)
            ->put('/incomes/'.$income->id, [
                'amount' => '1.00',
                'source' => 'Other',
                'income_date' => now()->toDateString(),
                'frequency' => 'one_time',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete('/incomes/'.$income->id)
            ->assertForbidden();

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'description' => 'Private income',
        ]);
    }

    public function test_dashboard_shows_today_activity(): void
    {
        $user = User::factory()->create();

        Income::create([
            'user_id' => $user->id,
            'amount' => 1000.00,
            'source' => 'Salary',
            'description' => 'Monthly salary',
            'income_date' => now()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 250.00,
            'description' => 'Lunch',
            'category' => 'Food',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Income Today')
            ->assertDontSee('Net Today')
            ->assertSee('+₱1,000.00')
            ->assertSee('Monthly salary')
            ->assertSee('Lunch')
            ->assertSee('data-greeting', false);
    }

    public function test_history_shows_income_and_net_totals(): void
    {
        $user = User::factory()->create();
        $yesterday = now()->subDay()->toDateString();

        Income::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'source' => 'Freelance',
            'description' => 'Paid invoice',
            'income_date' => $yesterday,
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'description' => 'Groceries',
            'category' => 'Food',
            'expense_date' => $yesterday,
        ]);

        DailyBudget::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'budget_date' => $yesterday,
        ]);

        $this->actingAs($user)
            ->get('/daily')
            ->assertOk()
            ->assertSee('Total Income')
            ->assertSee('Net')
            ->assertSee('Paid invoice')
            ->assertSee('Groceries')
            ->assertSee('+₱500.00')
            ->assertSee('₱300.00');
    }

    public function test_history_filters_by_type_and_income_source(): void
    {
        $user = User::factory()->create();

        Income::create([
            'user_id' => $user->id,
            'amount' => 2000.00,
            'source' => 'Salary',
            'description' => 'Salary credited',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
            'pay_day' => 15,
        ]);

        Income::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'source' => 'Gift',
            'description' => 'Birthday money',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'description' => 'Lunch',
            'category' => 'Food',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/daily?type=income')
            ->assertOk()
            ->assertSee('Salary credited')
            ->assertSee('Birthday money')
            ->assertDontSee('Lunch');

        $this->actingAs($user)
            ->get('/daily?type=income&source=Gift')
            ->assertOk()
            ->assertSee('Birthday money')
            ->assertDontSee('Salary credited');

        $this->actingAs($user)
            ->get('/daily?type=income&frequency=monthly')
            ->assertOk()
            ->assertSee('Salary credited')
            ->assertDontSee('Birthday money');

        $this->actingAs($user)
            ->get('/daily?type=income&frequency=one_time')
            ->assertOk()
            ->assertSee('Birthday money')
            ->assertDontSee('Salary credited');

        $this->actingAs($user)
            ->get('/daily?type=expenses')
            ->assertOk()
            ->assertSee('Lunch')
            ->assertDontSee('Salary credited')
            ->assertDontSee('Birthday money');
    }

    public function test_recurring_income_schedule_is_stored(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/incomes', [
                'amount' => '2500.00',
                'source' => 'Salary',
                'description' => 'Monthly salary',
                'income_date' => now()->toDateString(),
                'frequency' => 'monthly',
                'pay_day' => '0',
            ])
            ->assertRedirect('/dashboard');

        $monthly = Income::where('user_id', $user->id)->latest('id')->first();

        $this->assertSame('monthly', $monthly->frequency);
        $this->assertSame(0, $monthly->pay_day);
        $this->assertNull($monthly->pay_day_2);

        $this->actingAs($user)
            ->post('/incomes', [
                'amount' => '1500.00',
                'source' => 'Allowance',
                'description' => 'Twice a month',
                'income_date' => now()->subDay()->toDateString(),
                'frequency' => 'bi_monthly',
                'pay_day' => '15',
                'pay_day_2' => '0',
            ])
            ->assertRedirect();

        $biMonthly = Income::where('user_id', $user->id)->latest('id')->first();

        $this->assertSame('bi_monthly', $biMonthly->frequency);
        $this->assertSame(15, $biMonthly->pay_day);
        $this->assertSame(0, $biMonthly->pay_day_2);
        $this->assertSame('15th & Last day', $biMonthly->paydayLabel());
    }

    public function test_recurring_income_requires_paydays(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
        ])->assertSessionHasErrors('pay_day');

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'bi_monthly',
            'pay_day' => '15',
        ])->assertSessionHasErrors('pay_day_2');

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'daily',
        ])->assertSessionHasErrors('frequency');

        $this->assertSame(0, Income::where('user_id', $user->id)->count());
    }

    public function test_weekly_income_is_supported_without_paydays(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/incomes', [
                'amount' => '500.00',
                'source' => 'Allowance',
                'description' => 'Weekly allowance',
                'income_date' => now()->toDateString(),
                'frequency' => 'weekly',
                'pay_day' => '15',
                'pay_day_2' => '20',
            ])
            ->assertRedirect('/dashboard');

        $income = Income::where('user_id', $user->id)->first();

        $this->assertNotNull($income);
        $this->assertSame('weekly', $income->frequency);
        $this->assertNull($income->pay_day);
        $this->assertNull($income->pay_day_2);
    }

    public function test_bi_monthly_income_requires_different_paydays(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'amount' => '100.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'bi_monthly',
            'pay_day' => '15',
            'pay_day_2' => '15',
        ];

        $this->post('/incomes', $payload)->assertSessionHasErrors('pay_day_2');
        $this->assertSame(0, Income::where('user_id', $user->id)->count());

        $payload['pay_day_2'] = '0';

        $this->post('/incomes', $payload)->assertRedirect();
        $this->assertSame(1, Income::where('user_id', $user->id)->count());
    }

    public function test_one_time_and_monthly_income_normalize_paydays(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/incomes', [
            'amount' => '100.00',
            'source' => 'Gift',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
            'pay_day' => '15',
            'pay_day_2' => '20',
        ])->assertRedirect();

        $oneTime = Income::where('user_id', $user->id)->latest('id')->first();

        $this->assertNull($oneTime->pay_day);
        $this->assertNull($oneTime->pay_day_2);

        $this->post('/incomes', [
            'amount' => '200.00',
            'source' => 'Salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
            'pay_day' => '31',
            'pay_day_2' => '15',
        ])->assertRedirect();

        $monthly = Income::where('user_id', $user->id)->latest('id')->first();

        $this->assertSame(31, $monthly->pay_day);
        $this->assertNull($monthly->pay_day_2);
    }

    public function test_income_export_downloads_own_filtered_csv(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Income::create([
            'user_id' => $user->id,
            'amount' => 2500.00,
            'source' => 'Salary',
            'description' => 'Monthly salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
            'pay_day' => 15,
        ]);

        Income::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'source' => 'Gift',
            'description' => 'Birthday money',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ]);

        Income::create([
            'user_id' => $other->id,
            'amount' => 999.00,
            'source' => 'Salary',
            'description' => 'Secret income',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
            'pay_day' => 15,
        ]);

        $response = $this->actingAs($user)
            ->get('/incomes/export')
            ->assertOk()
            ->assertDownload('income-'.now()->toDateString().'.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Date,Description,Source,Frequency,Paydays,Amount', $content);
        $this->assertStringContainsString('Monthly salary', $content);
        $this->assertStringContainsString('Salary', $content);
        $this->assertStringContainsString('Monthly', $content);
        $this->assertStringContainsString('15th', $content);
        $this->assertStringContainsString('2500.00', $content);
        $this->assertStringNotContainsString('Secret income', $content);

        $filtered = $this->actingAs($user)
            ->get('/incomes/export?source=Gift')
            ->assertOk();

        $filteredContent = $filtered->streamedContent();

        $this->assertStringContainsString('Birthday money', $filteredContent);
        $this->assertStringContainsString('One-time', $filteredContent);
        $this->assertStringNotContainsString('Monthly salary', $filteredContent);

        $monthly = $this->actingAs($user)
            ->get('/incomes/export?frequency=monthly')
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Monthly salary', $monthly);
        $this->assertStringNotContainsString('Birthday money', $monthly);
    }
}
