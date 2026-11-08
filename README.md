# Expense Tracker

Expense Tracker is a Laravel web application for manually recording daily income and spending against a daily budget. It does not connect to a bank or import transactions. Each account only sees its own data.

## How the system works

### 1. Visitor entry

- `/` is the public landing page. It explains the app and links to registration and login.
- `/register` creates an account. The user chooses a profile type, then is logged in automatically.
- `/login` starts a session for an existing account.
- Authenticated users are sent to `/dashboard` when they visit the landing page.

### 2. Account profiles

The profile chosen at registration changes the defaults and the insights shown on the dashboard.

| Profile | Intended use | Default behaviour |
|---|---|---|
| Student | Allowance-based spending | Weekly allowance income, student-focused categories, weekly allowance cards |
| Employee | Salary-based spending | Monthly salary income, work-focused categories, next payday card |
| Working student | Salary and allowance together | Monthly income, all categories, next payday and next allowance cards |
| Something else | Freelancers, gigs, and irregular income | One-time income, all categories, monthly income vs. spending summary |

The profile can be changed later on the Profile page. It controls the default income source, default income frequency, visible expense categories, visible income sources, and the budget label.

### 3. Dashboard

`/dashboard` is the daily home page. It shows:

- The daily budget, how much has been spent, and how much remains.
- Profile-specific insights:
  - Allowance left this week and the next allowance for students.
  - The next payday for employees.
  - Both schedules for working students.
  - Monthly income and spending for the “Something else” profile.
- Today’s spending broken down by category.
- A Chart.js doughnut chart showing the category mix.
- A combined activity feed of today’s expenses and income.
- Quick-add forms for setting the budget, adding an expense, and adding income.
- Edit and delete actions for each transaction.

The dashboard only uses records dated today.

### 4. Income

Income records contain an amount, source, optional description, date, and frequency.

- Sources include salary, allowance, freelance, business, scholarship, stipend, part-time work, gifts, refunds, and other.
- Frequencies are one-time, weekly, monthly, and bi-monthly.
- Monthly and bi-monthly income can store payday numbers.
- Recurring income is used to calculate the next payday or next allowance.
- Backdated income redirects to that date in the History page.
- Income can be edited or deleted by its owner.

### 5. Expenses and budget

Expenses contain an amount, description, category, payment method, and date.

- Categories include food, transportation, bills, shopping, entertainment, school supplies, load and data, printing, org and projects, savings, and other.
- Payment methods include cash, GCash, Maya, bank transfer, debit card, credit card, and other.
- The daily budget is stored per date. Saving it again updates the existing budget for that day.
- Expenses can be edited or deleted by their owner.

### 6. History

`/daily` is the review page. It groups expenses, income, and budgets by date and shows:

- Days tracked, total income, total spent, net amount, and average spending per day.
- For each day: budget, amount spent, remaining budget, income, net, and all transactions.
- Filters for transaction type, category, payment method, income source, frequency, and date range.
- Presets for today, this week, this month, and all time.
- CSV downloads for the filtered expenses and income.

### 7. Profile

`/profile` lets the user update their name, email address, and profile type.

## Access and ownership

- Laravel session authentication protects every page except the landing, registration, and login pages.
- A user has many expenses, incomes, and daily budgets.
- Every expense, income, and budget belongs to exactly one user.
- Expense and income edit/delete actions check ownership and return `403` for another user’s record.
- History and CSV exports query only the authenticated user’s records.

## Technology

- Laravel 12 and PHP 8.2
- Blade templates with Tailwind CSS 4
- Vite for frontend assets
- Vanilla JavaScript for UI interactions
- Chart.js for the dashboard category doughnut
- SQLite as the default database

## URL map

| URL | Purpose |
|---|---|
| `GET /` | Landing page |
| `GET /register` | Registration form |
| `POST /register` | Create an account |
| `GET /login` | Login form |
| `POST /login` | Start a session |
| `POST /logout` | End a session |
| `GET /dashboard` | Today’s dashboard |
| `GET /daily` | Filtered income and expense history |
| `POST /budget` | Save or update today’s budget |
| `POST /expenses` | Add an expense |
| `GET /expenses/export` | Download filtered expenses as CSV |
| `GET /expenses/{expense}/edit` | Edit form for an expense |
| `PUT /expenses/{expense}` | Update an expense |
| `DELETE /expenses/{expense}` | Delete an expense |
| `POST /incomes` | Add income |
| `GET /incomes/export` | Download filtered income as CSV |
| `GET /incomes/{income}/edit` | Edit form for income |
| `PUT /incomes/{income}` | Update income |
| `DELETE /incomes/{income}` | Delete income |
| `GET /profile` | Profile page |
| `PUT /profile` | Update the profile |
