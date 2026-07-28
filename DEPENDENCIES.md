# Forex Journal - Dependency Inventory & Specifications

**Repository Audit Date:** July 28, 2026  
**Target Repository:** `forex_journal`  
**Primary Config Files Inspected:** `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `Dockerfile`  

---

## 1. Required System & Software Prerequisites

Before installing project dependencies, the host operating system or container environment must have the following runtime engines and global tools installed:

| Software / Tool | Required Version | Purpose | Source File Reference |
| :--- | :--- | :--- | :--- |
| **PHP Engine** | `^8.2` (Dev) / `8.3` (Docker) | Primary application runtime language. | `composer.json` (L9), `Dockerfile` (L2) |
| **Composer CLI** | `^2.5.0` | PHP dependency package manager. | `Dockerfile` (L28), `composer.json` |
| **Node.js** | `>=18.0.0` (Recommended: `20.x` or `22.x`) | JavaScript runtime for Vite bundling and frontend assets. | `Dockerfile` (L17), `package.json` |
| **npm CLI** | `>=9.0.0` | Node Package Manager for installing frontend packages. | `package.json`, `Dockerfile` (L18) |
| **SQLite3 CLI / Engine** | `^3.30.0` | Default lightweight relational database engine for dev/testing. | `Dockerfile` (L15), `fly.toml` (L11) |
| **Git CLI** | `>=2.30.0` | Version control system. | `Dockerfile` (L6) |

---

## 2. Required PHP Extensions

The following PHP extensions must be enabled in `php.ini` (or installed via OS package manager):

| PHP Extension | Purpose | Mandatory / Optional | Installation Command (Ubuntu/Debian) | Source File |
| :--- | :--- | :--- | :--- | :--- |
| `ext-pdo` | Core database abstraction layer. | **Mandatory** | Built-in PHP core | `Dockerfile` (L24) |
| `ext-pdo_sqlite` | SQLite database driver support. | **Mandatory** (for default dev DB) | `apt-get install php-sqlite3` | `Dockerfile` (L16) |
| `ext-pdo_mysql` | MySQL database driver support. | **Optional** (Production DB) | `docker-php-ext-install pdo_mysql` | `Dockerfile` (L24) |
| `ext-pdo_pgsql` / `pgsql` | PostgreSQL database driver support. | **Optional** (Production DB) | `docker-php-ext-install pdo_pgsql pgsql` | `Dockerfile` (L24) |
| `ext-mbstring` | Multibyte string processing for UTF-8. | **Mandatory** | `docker-php-ext-install mbstring` | `Dockerfile` (L24) |
| `ext-gd` | Image manipulation for profile/cover photos. | **Mandatory** | `docker-php-ext-install gd` | `Dockerfile` (L24), `composer.json` (L12) |
| `ext-zip` | Zip file handling for Spatie Backup & Excel. | **Mandatory** | `docker-php-ext-install zip` | `Dockerfile` (L25) |
| `ext-curl` | HTTP Client for Chapa, Gemini, Groq APIs. | **Mandatory** | `apt-get install php-curl` | `Dockerfile` (L7) |
| `ext-xml` | XML processing for PHPUnit & Excel import/export. | **Mandatory** | `apt-get install php-xml` | `Dockerfile` (L10) |
| `ext-bcmath` | Precision math operations for financial calculations. | **Mandatory** | `docker-php-ext-install bcmath` | `Dockerfile` (L24) |
| `ext-exif` | Image metadata parsing for uploads. | **Mandatory** | `docker-php-ext-install exif` | `Dockerfile` (L24) |

---

## 3. Production PHP Dependencies (`composer.json`)

Installed via command: `composer install --no-dev`

| Package Name | Constraint | Required / Optional | Purpose | Source File & Line |
| :--- | :--- | :--- | :--- | :--- |
| `php` | `^8.2` | **Mandatory** | Minimum supported PHP engine version. | `composer.json` (L9) |
| `laravel/framework` | `^12.0` | **Mandatory** | Core Laravel application framework. | `composer.json` (L13) |
| `akaunting/laravel-money` | `^6.0` | **Mandatory** | Currency formatting and monetary calculations. | `composer.json` (L10) |
| `consoletvs/charts` | `^6.8` | **Mandatory** | Server-side chart generation bindings. | `composer.json` (L11) |
| `intervention/image` | `^3.11` | **Mandatory** | Image processing, cropping, and resizing for user avatars. | `composer.json` (L12) |
| `laravel/sanctum` | `^4.2` | **Mandatory** | API token and session authentication. | `composer.json` (L14) |
| `laravel/tinker` | `^2.10.1` | **Mandatory** | Interactive REPL shell for debugging database models. | `composer.json` (L15) |
| `maatwebsite/excel` | `^1.1` | **Optional** | Excel/CSV import and export for trade history data. | `composer.json` (L16) |
| `spatie/laravel-activitylog` | `^4.10` | **Mandatory** | Admin audit log tracking for system events. | `composer.json` (L17) |
| `spatie/laravel-backup` | `^9.3` | **Mandatory** | Automated database and file backup archive generation. | `composer.json` (L18) |
| `spatie/laravel-medialibrary` | `^11.17` | **Mandatory** | File and image asset attachment to Eloquent models. | `composer.json` (L19) |
| `spatie/laravel-permission` | `^6.23` | **Mandatory** | Role-based permission control (`admin`, `analyst`, `trader`). | `composer.json` (L20) |
| `spatie/laravel-tags` | `^4.10` | **Optional** | Tagging system for trades, strategies, and reviews. | `composer.json` (L21) |
| `stripe/stripe-php` | `^19.1` | **Optional** | Stripe PHP SDK for credit card payments. | `composer.json` (L22) |
| `yajra/laravel-datatables-oracle`| `^12.6` | **Mandatory** | Server-side DataTables table processing for Admin UI. | `composer.json` (L23) |

---

## 4. Development PHP Dependencies (`composer.json`)

Installed via command: `composer install` (includes `require-dev`)

| Package Name | Constraint | Purpose | Source File & Line |
| :--- | :--- | :--- | :--- |
| `fakerphp/faker` | `^1.23` | Generating dummy user, trade, and review seed data. | `composer.json` (L26) |
| `laravel/pail` | `^1.2.2` | Real-time CLI log tailing tool for Laravel. | `composer.json` (L27) |
| `laravel/pint` | `^1.24` | Code style opinionated fixer for PHP (PSR-12 / Laravel standards). | `composer.json` (L28) |
| `laravel/sail` | `^1.41` | Docker desktop development environment launcher. | `composer.json` (L29) |
| `laravel/telescope` | `^5.15` | Local application debugging dashboard (requests, queries, logs). | `composer.json` (L30) |
| `mockery/mockery` | `^1.6` | Object mocking framework for unit tests. | `composer.json` (L31) |
| `nunomaduro/collision` | `^8.6` | Beautiful CLI error reporting handler. | `composer.json` (L32) |
| `phpunit/phpunit` | `^11.5.3` | Testing framework for Unit and Feature test suites. | `composer.json` (L33) |
| `spatie/laravel-ray` | `^1.43` | Ray desktop app debugging helper tool. | `composer.json` (L34) |

---

## 5. NPM Production Dependencies (`package.json`)

Installed via command: `npm install`

| Package Name | Version Constraint | Required / Optional | Purpose | Source File & Line |
| :--- | :--- | :--- | :--- | :--- |
| `alpinejs` | `^3.15.8` | **Mandatory** | Lightweight reactive JS framework for dropdowns, modals, and tabs. | `package.json` (L18) |
| `chart.js` | `^4.5.1` | **Mandatory** | Client-side trading charts, equity curves, and performance graphs. | `package.json` (L19) |

---

## 6. NPM Development Dependencies (`package.json`)

| Package Name | Version Constraint | Purpose | Source File & Line |
| :--- | :--- | :--- | :--- |
| `@tailwindcss/vite` | `^4.0.0` | Official Tailwind CSS v4 plugin for Vite bundler integration. | `package.json` (L10) |
| `axios` | `^1.11.0` | Promise-based HTTP client for AJAX calls in Blade/Alpine. | `package.json` (L11) |
| `concurrently` | `^9.0.1` | Run multiple CLI commands concurrently (`php artisan serve` + `vite`). | `package.json` (L12) |
| `laravel-vite-plugin` | `^2.0.0` | Integrates Vite with Laravel Blade asset helpers. | `package.json` (L13) |
| `tailwindcss` | `^4.0.0` | Utility-first CSS framework for component styling. | `package.json` (L14) |
| `vite` | `^7.0.7` | Next-generation frontend build tool and hot module reloader. | `package.json` (L15) |

---

## 7. Global CLI Utilities Summary

| Command | Recommended Version | Verification Command | Installation Link / Command |
| :--- | :--- | :--- | :--- |
| `php` | 8.2 or 8.3 | `php -v` | [php.net](https://www.php.net/downloads) |
| `composer` | 2.5+ | `composer --version` | [getcomposer.org](https://getcomposer.org/) |
| `node` | 18+ / 20+ | `node -v` | [nodejs.org](https://nodejs.org/) |
| `npm` | 9+ | `npm -v` | Bundled with Node.js |
| `sqlite3` | 3.30+ | `sqlite3 --version` | `apt install sqlite3` / Choco / Homebrew |
