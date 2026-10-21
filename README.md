# Expense Tracker

A daily-expense tracker built with Laravel 12. Set a budget for the day, log your expenses, review them day by day, and export everything to CSV.

**The database is SQLite — you do not need XAMPP, MySQL, or phpMyAdmin.** The database lives in a single file at `database/database.sqlite`, so the app is ready to run with nothing but PHP installed.

## Features

- Register / log in / log out (session-based auth)
- Set a daily budget and see how much of it is left
- Log expenses with an amount, description, and category
  - Categories: Food, Transportation, Bills, Shopping, Entertainment, Other
- Edit and delete your own expenses (other users' expenses are off-limits)
- Daily history page with per-day totals, budget vs. spent, and remaining amount
- Filter the history by category and date range
- Export the filtered expenses to CSV
- Every user only ever sees their own expenses and budgets

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.2 or newer, with the `pdo_sqlite` extension |
| Composer | 2.x |
| Node.js | 20 or newer (for Vite and asset building) |

Check your PHP extensions:

```bash
php -m | grep sqlite
```

You should see `pdo_sqlite`. (The optional `intl` extension is only needed for the
`php artisan db:show` / `db:table` size reports — the app itself works without it.)

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file and generate an app key
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# 3. Create the SQLite database file and build the schema
touch database/database.sqlite   # Windows: New-Item database\database.sqlite
php artisan migrate --seed

# 4. Install and build frontend assets
npm install
npm run build
```

`migrate --seed` creates the schema and one test account:

| Email | Password |
|---|---|
| `test@example.com` | `password` |

You can also skip the seed and just register your own account from the UI.

## Running the app

```bash
composer dev
```

This starts the PHP dev server, the queue worker, the log viewer, and Vite all at once,
and prints the app URL (http://127.0.0.1:8000 by default).

To run just the web server:

```bash
php artisan serve
```

## Database

The app uses SQLite by default.

```dotenv
DB_CONNECTION=sqlite
```

When `DB_DATABASE` is not set, Laravel uses `database/database.sqlite` automatically. To
point at a different file, set an absolute path:

```dotenv
DB_DATABASE=/absolute/path/to/your/database.sqlite
```

The SQLite connection is tuned for concurrent use (the dev server and the queue worker
both write to the same file):

| Setting | Default | Purpose |
|---|---|---|
| `DB_JOURNAL_MODE` | `WAL` | Write-ahead logging so readers don't block the writer |
| `DB_BUSY_TIMEOUT` | `5000` | Milliseconds to wait on a locked database before failing |

Both are set in `config/database.php` and can be overridden from `.env`.

### Resetting the database

```bash
php artisan migrate:fresh --seed
```

This drops every table, re-runs all migrations, and re-seeds the test user. It is the
fastest way to get back to a clean state.

The database file itself is ignored by git (`database/.gitignore` ignores `*.sqlite*`),
so your local data is never committed.

### Migrating data from MySQL

The `mysql` connection is still defined in `config/database.php`, so a one-off import
from an old MySQL database is possible:

1. Fill in the MySQL credentials in `.env` and comment out `DB_CONNECTION=sqlite`.
2. Run your export/import queries.
3. Switch `DB_CONNECTION` back to `sqlite`.

## Tests

```bash
php artisan test
```

The test suite runs against an **in-memory** SQLite database (`phpunit.xml` sets
`DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`), so it never touches your
development database and needs no external database server.

## Project structure

```
app/
  Http/Controllers/
    Auth/LoginController.php        Login and logout
    Auth/RegisterController.php     Registration
    ExpenseController.php           Budgets, expenses, daily view, CSV export
  Models/
    Expense.php                     An expense belonging to a user
    DailyBudget.php                 A per-day budget belonging to a user
    User.php                        User and their expenses / budgets
database/
  migrations/                       Schema (SQLite compatible)
  seeders/                          Test user
resources/views/
  expenses/                         Dashboard and daily history pages
  auth/                             Login and register pages
routes/web.php                      All routes
```

## Useful commands

```bash
php artisan migrate          # Run pending migrations
php artisan migrate:fresh    # Rebuild the schema from scratch
php artisan db:seed          # Seed the test user
php artisan route:list       # List all routes
php artisan tinker           # Interactive PHP console with the app booted
```
