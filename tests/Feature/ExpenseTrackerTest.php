<?php

namespace Tests\Feature;

use App\Models\DailyBudget;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'profile_type' => 'working',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'profile_type' => 'working',
        ]);
    }

    public function test_user_can_register_with_a_profile_type(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'profile_type' => 'student_allowance',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'maria@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('student_allowance', $user->profile_type);
        $this->assertSame('Allowance', $user->defaultIncomeSource());
    }

    public function test_registration_requires_a_profile_type(): void
    {
        $this->post('/register', [
            'name' => 'No Profile',
            'email' => 'noprofile@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('profile_type');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'noprofile@example.com']);
    }

    public function test_registration_rejects_an_invalid_profile_type(): void
    {
        $this->post('/register', [
            'name' => 'Invalid Profile',
            'email' => 'invalid@example.com',
            'profile_type' => 'invalid',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('profile_type');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.com']);
    }

    public function test_profile_type_controls_the_default_income_source(): void
    {
        $this->assertSame('Allowance', (new User(['profile_type' => 'student_allowance']))->defaultIncomeSource());
        $this->assertSame('Salary', (new User(['profile_type' => 'working']))->defaultIncomeSource());
        $this->assertNull((new User(['profile_type' => 'working_student']))->defaultIncomeSource());
        $this->assertNull((new User(['profile_type' => 'other']))->defaultIncomeSource());
        $this->assertNull((new User)->defaultIncomeSource());
    }

    public function test_profile_type_sets_expected_defaults(): void
    {
        $student = new User(['profile_type' => 'student_allowance']);
        $this->assertSame('weekly', $student->defaultIncomeFrequency());
        $this->assertContains('School Supplies', $student->expenseCategories());
        $this->assertContains('Scholarship', $student->incomeSources());
        $this->assertSame('Daily allowance', $student->budgetLabel());

        $working = new User(['profile_type' => 'working']);
        $this->assertSame('monthly', $working->defaultIncomeFrequency());
        $this->assertContains('Bills', $working->expenseCategories());
        $this->assertNotContains('School Supplies', $working->expenseCategories());
        $this->assertContains('Salary', $working->incomeSources());
        $this->assertSame('Daily budget', $working->budgetLabel());

        $workingStudent = new User(['profile_type' => 'working_student']);
        $this->assertContains('Salary', $workingStudent->incomeSources());
        $this->assertContains('Allowance', $workingStudent->incomeSources());

        $other = new User(['profile_type' => 'other']);
        $this->assertSame('one_time', $other->defaultIncomeFrequency());
        $this->assertSame(Expense::CATEGORIES, $other->expenseCategories());
    }

    public function test_registration_page_shows_profile_descriptions(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Employee')
            ->assertSee('Student')
            ->assertSee('Working student')
            ->assertSee('Something else')
            ->assertSee('Salary, paydays, and everyday bills.');
    }

    public function test_student_profile_preselects_allowance_in_quick_add(): void
    {
        $user = User::factory()->create(['profile_type' => 'student_allowance']);

        $response = $this->actingAs($user)->get('/dashboard')->assertOk();

        $this->assertMatchesRegularExpression(
            '/name="source"\s+value="Allowance"[^>]*checked/',
            $response->getContent()
        );
    }

    public function test_student_quick_add_shows_student_categories(): void
    {
        $user = User::factory()->create(['profile_type' => 'student_allowance']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('School Supplies')
            ->assertSee('Load & Data')
            ->assertSee('Scholarship')
            ->assertDontSee('Bills');
    }

    public function test_student_dashboard_shows_allowance_insights(): void
    {
        $user = User::factory()->create(['profile_type' => 'student_allowance']);

        Income::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'source' => 'Allowance',
            'description' => 'Weekly allowance',
            'income_date' => now()->toDateString(),
            'frequency' => 'weekly',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'description' => 'Lunch',
            'category' => 'Food',
            'payment_method' => 'Cash',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Allowance left this week')
            ->assertSee('₱400.00')
            ->assertSee('Next allowance');
    }

    public function test_dashboard_renders_category_spending_chart(): void
    {
        $user = User::factory()->create();

        Expense::create([
            'user_id' => $user->id,
            'amount' => 250.00,
            'description' => 'Lunch',
            'category' => 'Food',
            'payment_method' => 'Cash',
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'description' => 'Jeepney fare',
            'category' => 'Transportation',
            'payment_method' => 'Cash',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Category mix')
            ->assertSee('data-category-chart', false)
            ->assertSee('id="category-chart-data"', false)
            ->assertSee('"label":"Food"', false)
            ->assertSee('"label":"Transportation"', false)
            ->assertSee('"color":"food"', false)
            ->assertSee('"color":"transport"', false);
    }

    public function test_working_dashboard_shows_the_next_payday(): void
    {
        $user = User::factory()->create(['profile_type' => 'working']);

        Income::create([
            'user_id' => $user->id,
            'amount' => 2500.00,
            'source' => 'Salary',
            'description' => 'Monthly salary',
            'income_date' => now()->toDateString(),
            'frequency' => 'monthly',
            'pay_day' => 15,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Next payday');
    }

    public function test_working_student_dashboard_shows_both_income_schedules(): void
    {
        $user = User::factory()->create(['profile_type' => 'working_student']);

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
            'amount' => 500.00,
            'source' => 'Allowance',
            'description' => 'Weekly allowance',
            'income_date' => now()->toDateString(),
            'frequency' => 'weekly',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Next payday')
            ->assertSee('Next allowance');
    }

    public function test_other_profile_dashboard_shows_a_flexible_monthly_overview(): void
    {
        $user = User::factory()->create(['profile_type' => 'other']);

        Income::create([
            'user_id' => $user->id,
            'amount' => 1200.00,
            'source' => 'Freelance',
            'description' => 'Side project',
            'income_date' => now()->toDateString(),
            'frequency' => 'one_time',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'description' => 'Groceries',
            'category' => 'Food',
            'payment_method' => 'Cash',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Income this month')
            ->assertSee('No payday required')
            ->assertSee('Spent this month')
            ->assertDontSee('Next payday');
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_set_daily_budget_and_add_expense(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/budget', ['budget' => '500.00'])
            ->assertRedirect('/dashboard');

        $this->post('/budget', ['budget' => '650.00'])
            ->assertRedirect('/dashboard');

        $this->post('/expenses', [
            'amount' => '120.50',
            'description' => 'Lunch',
            'category' => 'Food',
            'payment_method' => 'GCash',
        ])->assertRedirect('/dashboard');

        $this->assertSame(
            1,
            DailyBudget::where('user_id', $user->id)->count(),
            'Setting the daily budget twice must update, not duplicate, the row.'
        );

        $budget = DailyBudget::where('user_id', $user->id)->first();
        $this->assertNotNull($budget);
        $this->assertSame('650.00', $budget->amount);
        $this->assertSame(now()->toDateString(), $budget->budget_date->toDateString());

        $expense = Expense::where('user_id', $user->id)->first();
        $this->assertNotNull($expense);
        $this->assertSame('120.50', $expense->amount);
        $this->assertSame('Lunch', $expense->description);
        $this->assertSame('Food', $expense->category);
        $this->assertSame('GCash', $expense->payment_method);
        $this->assertSame(now()->toDateString(), $expense->expense_date->toDateString());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('₱650.00')
            ->assertSee('₱120.50')
            ->assertSee('Lunch')
            ->assertSee('GCash');
    }

    public function test_user_cannot_add_expense_with_zero_or_negative_amount(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/expenses', [
            'amount' => '0.00',
            'description' => 'Invalid zero',
            'category' => 'Food',
            'payment_method' => 'Cash',
        ])->assertSessionHasErrors('amount');

        $this->post('/expenses', [
            'amount' => '-5.00',
            'description' => 'Invalid negative',
            'category' => 'Food',
            'payment_method' => 'Cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, Expense::where('user_id', $user->id)->count());
    }

    public function test_expense_requires_a_valid_payment_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/expenses', [
            'amount' => '10.00',
            'description' => 'Missing method',
            'category' => 'Food',
        ])->assertSessionHasErrors('payment_method');

        $this->post('/expenses', [
            'amount' => '10.00',
            'description' => 'Invalid method',
            'category' => 'Food',
            'payment_method' => 'Crypto',
        ])->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Expense::where('user_id', $user->id)->count());
    }

    public function test_payment_method_defaults_to_cash_for_existing_expenses(): void
    {
        $user = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $user->id,
            'amount' => 25.00,
            'description' => 'Legacy expense',
            'category' => 'Other',
            'expense_date' => now()->toDateString(),
        ]);

        $this->assertSame('Cash', $expense->fresh()->payment_method);
    }

    public function test_daily_page_shows_each_day_with_totals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $yesterday = now()->subDay()->toDateString();

        Expense::create([
            'user_id' => $user->id,
            'amount' => 75.00,
            'description' => 'Bus fare',
            'category' => 'Transportation',
            'expense_date' => $yesterday,
        ]);

        DailyBudget::create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'budget_date' => $yesterday,
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 40.00,
            'description' => 'Coffee',
            'category' => 'Food',
            'expense_date' => now()->toDateString(),
        ]);

        $this->get('/daily')
            ->assertOk()
            ->assertSee('Bus fare')
            ->assertSee('Coffee')
            ->assertSee(now()->format('l, F j, Y'))
            ->assertSee(now()->subDay()->format('l, F j, Y'));
    }

    public function test_users_cannot_delete_each_others_expenses(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $owner->id,
            'amount' => 100.00,
            'description' => 'Groceries',
            'category' => 'Shopping',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($other)
            ->delete('/expenses/'.$expense->id)
            ->assertForbidden();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    public function test_user_can_edit_their_own_expense(): void
    {
        $user = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'description' => 'Groceries',
            'category' => 'Shopping',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/expenses/'.$expense->id.'/edit')
            ->assertOk()
            ->assertSee('Groceries');

        $this->actingAs($user)
            ->put('/expenses/'.$expense->id, [
                'amount' => '150.75',
                'description' => 'Weekly groceries',
                'category' => 'Food',
                'payment_method' => 'Debit Card',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => '150.75',
            'description' => 'Weekly groceries',
            'category' => 'Food',
            'payment_method' => 'Debit Card',
        ]);
    }

    public function test_users_cannot_edit_each_others_expenses(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $owner->id,
            'amount' => 100.00,
            'description' => 'Groceries',
            'category' => 'Shopping',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($other)
            ->get('/expenses/'.$expense->id.'/edit')
            ->assertForbidden();

        $this->actingAs($other)
            ->put('/expenses/'.$expense->id, [
                'amount' => '1.00',
                'description' => 'Hacked',
                'category' => 'Other',
                'payment_method' => 'Cash',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Groceries',
        ]);
    }

    public function test_update_rejects_zero_or_negative_amount(): void
    {
        $user = User::factory()->create();

        $expense = Expense::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'description' => 'Groceries',
            'category' => 'Shopping',
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->put('/expenses/'.$expense->id, [
                'amount' => '0.00',
                'description' => 'Groceries',
                'category' => 'Shopping',
                'payment_method' => 'Cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAs($user)
            ->put('/expenses/'.$expense->id, [
                'amount' => '-10.00',
                'description' => 'Groceries',
                'category' => 'Shopping',
                'payment_method' => 'Cash',
            ])
            ->assertSessionHasErrors('amount');
    }

    public function test_daily_page_filters_by_category_and_date_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 75.00,
            'description' => 'Bus fare',
            'category' => 'Transportation',
            'payment_method' => 'Cash',
            'expense_date' => now()->subDay()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 40.00,
            'description' => 'Movie night',
            'category' => 'Entertainment',
            'payment_method' => 'GCash',
            'expense_date' => now()->toDateString(),
        ]);

        $this->get('/daily?category=Transportation')
            ->assertOk()
            ->assertSee('Bus fare')
            ->assertDontSee('Movie night');

        $this->get('/daily?payment_method=GCash')
            ->assertOk()
            ->assertSee('Movie night')
            ->assertDontSee('Bus fare');

        $this->get('/daily?from='.now()->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertSee('Movie night')
            ->assertDontSee('Bus fare');
    }

    public function test_daily_page_date_filter_excludes_budgets_outside_the_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $outside = now()->subDays(5)->toDateString();
        $inside = now()->subDay()->toDateString();

        DailyBudget::create([
            'user_id' => $user->id,
            'amount' => 111.00,
            'budget_date' => $outside,
        ]);

        DailyBudget::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'budget_date' => $inside,
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'description' => 'Inside the range',
            'category' => 'Food',
            'expense_date' => $inside,
        ]);

        $response = $this->get('/daily?from='.$inside.'&to='.$inside)->assertOk();

        $response->assertDontSee('111.00');
        $response->assertSee('500.00');
        $response->assertSee('Inside the range');

        $this->assertMatchesRegularExpression(
            '/Days Tracked<\/span>\s*<strong>1<\/strong>/',
            $response->getContent()
        );

        $this->assertMatchesRegularExpression(
            '/Average Per Day<\/span>\s*<strong>\s*₱200\.00\s*<\/strong>/',
            $response->getContent()
        );
    }

    public function test_export_downloads_filtered_csv(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Expense::create([
            'user_id' => $user->id,
            'amount' => 120.50,
            'description' => 'Lunch',
            'category' => 'Food',
            'payment_method' => 'GCash',
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $other->id,
            'amount' => 99.99,
            'description' => 'Secret purchase',
            'category' => 'Food',
            'payment_method' => 'Cash',
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get('/expenses/export')
            ->assertOk()
            ->assertDownload('expenses-'.now()->toDateString().'.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Date,Description,Category,"Payment Method",Amount', $content);
        $this->assertStringContainsString('Lunch', $content);
        $this->assertStringContainsString('GCash', $content);
        $this->assertStringContainsString('120.50', $content);
        $this->assertStringNotContainsString('Secret purchase', $content);

        $filtered = $this->actingAs($user)
            ->get('/expenses/export?category=Entertainment')
            ->assertOk();

        $this->assertStringNotContainsString('Lunch', $filtered->streamedContent());

        $filteredByPaymentMethod = $this->actingAs($user)
            ->get('/expenses/export?payment_method=GCash')
            ->assertOk();

        $methodContent = $filteredByPaymentMethod->streamedContent();
        $this->assertStringContainsString('Lunch', $methodContent);
        $this->assertStringNotContainsString('Secret purchase', $methodContent);
    }
}
