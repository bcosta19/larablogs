<p align="center">
  <img src="public/favicon.svg" width="96" height="96" alt="Larablogs icon">
</p>
<h1 align="center">Larablogs</h1>
<p align="center"><strong>Personal web & RSS feed radar with instant email notifications.</strong></p>
<p align="center">
  <a href="#what-you-can-do">Features</a> ·
  <a href="#screenshots">Screenshots</a> ·
  <a href="#installation">Installation</a> ·
  <a href="#background-scheduler">Background Scheduler</a> ·
  <a href="#stack-and-requirements">Stack</a> ·
  <a href="#tests">Tests</a> ·
  <a href="README.pt-BR.md">Português</a>
</p>

A personal web publication and feed monitor built with **PHP 8.5, Laravel 13, Vue.js 3, Inertia.js and Tailwind CSS v4**.
Paste any blog or RSS/Atom feed URL to track new posts and receive instant alerts straight to your inbox, featuring intelligent anti-spam baseline sync and a distraction-free editorial interface with multi-language and dark mode support.

---

## Screenshots

<p align="center">
  <img src="docs/screenshots/larablogs-dark.png" alt="Larablogs in Default Dark Theme (Midnight Editorial) with monitored publications, stamps and stats" width="100%">
</p>

*Default Dark Theme (Midnight Editorial) — classical typography, high-contrast stamps for dark favicons, and manual verification triggers.*

<p align="center">
  <img src="docs/screenshots/larablogs-posts.png" alt="Larablogs - Recent Articles Tab" width="100%">
</p>

*Recent Articles tab — instant visual identification with origin blog favicons and delivery notification badges.*

<p align="center">
  <img src="docs/screenshots/larablogs-light.png" alt="Larablogs in Light Theme (Warm Ivory Paper)" width="100%">
</p>

*Optional Light Mode rendered in traditional warm ivory newsprint.*

---

## What you can do

| Feature | In Larablogs |
|---|---|
| **Smart Feed Discovery** | Enter just the blog URL (e.g., `https://laravel-news.com`); the engine automatically parses HTML for RSS/Atom `<link>` tags and common endpoints (`/feed`, `/rss`, `/atom.xml`). |
| **Anti-Spam Baseline** | Upon subscription, existing published posts are captured as a starting baseline without sending bulk historic emails. Only *new* articles published thereafter trigger alerts. |
| **Editorial Aesthetics** | Classical newspaper & literary journal typography with native Dark Theme (*Midnight Editorial*) as default, and instant 1-click toggle to Light Mode with `localStorage` persistence. |
| **High-Contrast Stamps** | Protective light background frames ensure dark, black, or transparent PNG favicons remain crisp and readable in dark mode. |
| **Multi-Language (i18n)** | Seamless real-time switching between 5 languages: 🇺🇸 English, 🇧🇷 Portuguese, 🇪🇸 Spanish, 🇫🇷 French, and 🇩🇪 German, with localized dates and relative timestamps. |
| **Synchronous Notifications** | Inline delivery on execution — no Redis, queue workers, or external daemons required to receive alerts. |
| **Individual Control** | On-demand manual checking (*Check now* button), pause/resume toggling, and publication deletion with confirmation. |

---

## Stack and requirements

- **PHP**: 8.5.x
- **Node.js**: 24.x
- **Laravel**: 13.x
- **Frontend**: Vue.js 3, Inertia.js 3, Tailwind CSS v4, Vite
- **Database**: SQLite (zero-config, stored in `database/database.sqlite`)
- **Version Management**: `mise` (`mise.toml`)

---

## Installation

### 1. Clone the repository
```sh
git clone git@github.com:bcosta19/larablogs.git
cd larablogs
```

### 2. Install dependencies
```sh
composer install
npm install
```

### 3. Setup environment and database
```sh
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

### 4. Run in development
```sh
composer run dev
```
*(or run separately in two terminal tabs: `php artisan serve` and `npm run dev`)*

Open your browser at: **`http://localhost:8000`**

---

## Private account

All application pages and settings require login. There is no public registration. Create the owner account with `php artisan account:bootstrap`; it prompts for the email and a hidden temporary password. The first login requires replacing that password. For unattended deployment, use `--email-file` and `--password-file` with private files. Existing demo accounts cannot sign in. An existing owner can only be reset with the explicit `--reset` option.

Run browser tests using `composer run dusk`. They use a temporary database, test accounts, a separate local server and a dedicated ChromeDriver port, leaving your application data untouched.

## Email setup

Open the navigation menu, choose Email and enter a Resend API key and sender address. These settings require signing in to the private account. Store your Laravel `APP_KEY` securely: it encrypts the Resend key in the database, and changing it makes that key unreadable. Use a Resend key limited to sending.

Use a sender on a verified domain for general delivery. `onboarding@resend.dev` is intended for testing and can only send to the email address associated with your Resend account. After saving, use the test email form with your recipient address. This queues the message; keep `php artisan queue:work database` running to process it. The job retries up to three times; inspect failures with `php artisan queue:failed`. Queuing does not prove delivery. Run the scheduler below to check feeds automatically. Failed article notifications are logged and are not retried automatically.

## Background Scheduler

The Artisan command responsible for crawling feeds and dispatching notifications is:
```sh
php artisan blogs:check
```

To run the Laravel scheduler during local development:
```sh
php artisan schedule:work
```

For production or continuous background checks on Linux, add a single entry to your crontab (`crontab -e`):
```cron
* * * * * cd /path/to/larablogs && php artisan schedule:run >> /dev/null 2>&1
```

> **Local Email Testing**: By default, Laravel uses `MAIL_MAILER=log`, writing sent emails to `storage/logs/laravel.log`. To inspect formatted emails with a web inbox during development, point to a local [Mailpit](https://github.com/axllent/mailpit) server on port `1025` in your `.env`.

---

## Testing & Code Quality

Larablogs features an automated quality and testing pipeline covering security audits, backend unit/feature tests with Pest, frontend TypeScript & Vue validations, static analysis at PHPStan level 7, and browser end-to-end tests with Laravel Dusk.

### Run the Full Pipeline

Run every check locally before committing:

```sh
./bin/ci
# or
composer ci
```

### Individual Commands

| Tool | Purpose | Command |
|---|---|---|
| **Pest** | Unit & Feature tests (SQLite `:memory:`) | `./vendor/bin/pest` *(or `composer test`)* |
| **Laravel Dusk** | End-to-end headless browser testing | `php artisan dusk` *(or `composer dusk`)* |
| **PHPStan** | Level 7 static analysis | `./vendor/bin/phpstan analyse --memory-limit=512M` |
| **Laravel Pint** | PHP code style & PSR standards | `composer lint:check` *(fix with `composer lint`)* |
| **ESLint + Prettier** | Vue 3 & TypeScript code quality | `npm run check` *(fix with `npm run format && npm run lint`)* |
| **Security Audits** | PHP & Node package vulnerabilities | `composer audit && npm audit` |
| **Vite** | Frontend production build | `npm run build` |

---

## License

Distributed under the [MIT](LICENSE) License.
