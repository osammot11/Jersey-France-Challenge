# Jersey France Challenge — registration concept

A Laravel demonstration prepared for the Jersey France Challenge organisers. The live event site describes a 14-mile Jersey-to-Carteret swim relay for teams of 2–6; its FAQ currently asks captains to email teammate addresses to the organisers for invitations. This concept makes each invitation and individual registration visible in one roster.

Source: https://www.jerseyfrancechallenge.com/

## What the demo does

- A captain creates a team of 2–6 and receives a private roster URL.
- The captain creates a unique registration link for each swimmer and shares it manually.
- Each swimmer completes their own form and accepts clearly labelled **draft demo conditions**. The demo requires a fictional passport value beginning with `DEMO`.
- Passport numbers are encrypted in the database and never displayed in the captain view, staff roster or CSV export.
- Authenticated staff see team readiness, export a roster without passport numbers, and replace a swimmer. Replacement invalidates the old link, clears the old submission and records who performed the change.
- `/proposal` contains an indicative GBP budget range and the assumptions still to be agreed.

No payment is taken. Invitations are links to copy; email sending is not enabled. The 2027 date, final rules, entry price, privacy notice and payment terms have not been supplied by the organiser. **Do not use this as a live registration service or collect real passport details.**

## Local setup

Requires PHP 8.2+, Composer and SQLite (or another configured Laravel database). In this copied folder, the ignored `.env` still contains settings from the previous project. Do not run migrations or registrations against that connection. The next commands are for a fresh checkout with no existing `.env`.

```bash
composer install
cp -n .env.example .env
php artisan key:generate
mkdir -p database && touch database/database.sqlite
php artisan migrate
php artisan serve
```

Use the commands above only after confirming `.env` points to the intended database. Open `http://127.0.0.1:8000`, then start a fictional team from the home page. The private captain URL shows each personal swimmer link.

For a preview that leaves this folder's `.env` and old database untouched, use an isolated SQLite file. Laravel's `serve` command can pass values from `.env` to its child process, so use PHP's server directly for this case:

```bash
touch /private/tmp/jfc-preview.sqlite
DB_CONNECTION=sqlite DB_DATABASE=/private/tmp/jfc-preview.sqlite php artisan migrate --force
DB_CONNECTION=sqlite DB_DATABASE=/private/tmp/jfc-preview.sqlite APP_NAME='Jersey France Challenge Concept' SESSION_DRIVER=file SESSION_COOKIE=jfc_preview_session CACHE_STORE=file APP_DEBUG=false php -S 127.0.0.1:8765 -t public
```

To create a staff account for local review, use `php artisan tinker` and enter:

```php
App\Models\User::create([
    'name' => 'Race Office',
    'email' => 'staff@example.test',
    'password' => Illuminate\Support\Facades\Hash::make('choose-a-unique-local-password'),
]);
```

Then sign in at `/login`. Replace the sample email and password before running the command. `DatabaseSeeder` deliberately creates no staff account. When using the isolated preview, run the account creation command with the same `DB_CONNECTION` and `DB_DATABASE` values. The browser preview in this workspace uses an isolated temporary SQLite database.

## Verification

```bash
php artisan test
php artisan view:cache
```

The feature tests cover team capacity, an individual's form and encrypted passport field, rejection of non-demo passport values, staff access and swimmer replacement.

## Before a real launch

Confirm the event's approved conditions and consent version, the minimum passport fields required, the payment and booking workflow, invitation email delivery, staff access roles, retention/deletion rules and hosting/security arrangements.
