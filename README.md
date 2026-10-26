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

## Tests

The project includes feature tests covering feed discovery, baseline synchronization, notifications, and CLI commands:

```sh
php artisan test
```

---

## License

Distributed under the [MIT](LICENSE) License.
