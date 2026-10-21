<?php

namespace Tests\Feature;

use App\Models\DailyBudget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_set_daily_budget_and_add_expense(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/budget', ['budget' => '500.00'])
            ->assertRedirect('/');

        $this->post('/budget', ['budget' => '650.00'])
            ->assertRedirect('/');

        $this->post('/expenses', [
            'amount' => '120.50',
            'description' => 'Lunch',
            'category' => 'Food',
        ])->assertRedirect('/');

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
        $this->assertSame(now()->toDateString(), $expense->expense_date->toDateString());

        $this->get('/')
            ->assertOk()
            ->assertSee('₱650.00')
            ->assertSee('₱120.50')
            ->assertSee('Lunch');
    }

    public function test_user_cannot_add_expense_with_zero_or_negative_amount(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/expenses', [
            'amount' => '0.00',
            'description' => 'Invalid zero',
            'category' => 'Food',
        ])->assertSessionHasErrors('amount');

        $this->post('/expenses', [
            'amount' => '-5.00',
            'description' => 'Invalid negative',
            'category' => 'Food',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, Expense::where('user_id', $user->id)->count());
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
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => '150.75',
            'description' => 'Weekly groceries',
            'category' => 'Food',
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
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAs($user)
            ->put('/expenses/'.$expense->id, [
                'amount' => '-10.00',
                'description' => 'Groceries',
                'category' => 'Shopping',
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
            'expense_date' => now()->subDay()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $user->id,
            'amount' => 40.00,
            'description' => 'Movie night',
            'category' => 'Entertainment',
            'expense_date' => now()->toDateString(),
        ]);

        $this->get('/daily?category=Transportation')
            ->assertOk()
            ->assertSee('Bus fare')
            ->assertDontSee('Movie night');

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
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'user_id' => $other->id,
            'amount' => 99.99,
            'description' => 'Secret purchase',
            'category' => 'Food',
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get('/expenses/export')
            ->assertOk()
            ->assertDownload('expenses-'.now()->toDateString().'.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Date,Description,Category,Amount', $content);
        $this->assertStringContainsString('Lunch', $content);
        $this->assertStringContainsString('120.50', $content);
        $this->assertStringNotContainsString('Secret purchase', $content);

        $filtered = $this->actingAs($user)
            ->get('/expenses/export?category=Entertainment')
            ->assertOk();

        $this->assertStringNotContainsString('Lunch', $filtered->streamedContent());
    }
}
