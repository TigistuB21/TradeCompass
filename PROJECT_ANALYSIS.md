# Forex Journal - Project Analysis & Technical Audit

**Repository Audit Date:** July 28, 2026  
**Target Repository:** `forex_journal`  
**Audit Type:** Read-Only Technical Architecture & Onboarding Analysis  

---

## 1. Executive Summary & Project Purpose

**Forex Journal** is a full-featured web application built on the **Laravel framework** designed to act as a **Trading Performance Journal and Analyst Marketplace**. It bridges individual Forex traders with certified market analysts to improve trading discipline, track statistics, perform behavioral analytics, and provide AI-assisted performance coaching.

### Key Functional Capabilities
1. **Trader Journaling & Analytics**: Track currency pair trades, execution strategies, win rates, risk/reward ratios, drawdown, and psychological journaling.
2. **Analyst Marketplace & Subscriptions**: Certified financial analysts offer tier-based coaching plans (Basic, Premium, Elite) with payment integration via **Chapa** (Ethiopian Birr / ETB) and **Stripe**.
3. **AI-Assisted Coaching**: Automated feedback draft generation utilizing Google **Gemini AI** (`gemini-2.5-flash` / `gemini-1.5-flash`) and **Groq LLM** (`llama3-70b-8192`) integration.
4. **Gamification**: XP points, leveling system, and achievement badges for consistent trading habits.
5. **Direct Messaging & Notifications**: Real-time communication between analysts and subscribed traders.
6. **Admin Oversight**: Comprehensive user management, verification workflows, analyst application processing, backups, activity logs, and dispute resolution.

---

## 2. Verified Technology Stack

| Layer | Technology | Verified Version | Source File Reference |
| :--- | :--- | :--- | :--- |
| **Language** | PHP | `^8.2` (Runtime) / `8.3` (Docker container) | `composer.json` (L9), `Dockerfile` (L2) |
| **Backend Framework** | Laravel Framework | `^12.0` | `composer.json` (L13) |
| **Frontend Assets** | Vite | `^7.0.7` | `package.json` (L15) |
| **CSS Framework** | Tailwind CSS | `^4.0.0` (with `@tailwindcss/vite`) | `package.json` (L10, L14) |
| **JS Reactive Framework**| Alpine.js | `^3.15.8` | `package.json` (L18) |
| **Data Visualization** | Chart.js | `^4.5.1` & `consoletvs/charts:^6.8` | `package.json` (L19), `composer.json` (L11) |
| **Database Engine** | SQLite (Default Dev/Fly.io) / MySQL / PostgreSQL | Driver-agnostic | `.env.example` (L23), `fly.toml` (L11), `Dockerfile` (L24) |
| **Authentication** | Laravel Sanctum / Built-in Session Guard | `^4.2` | `composer.json` (L14), `config/auth.php` |
| **Authorization** | Spatie Laravel Permission | `^6.23` | `composer.json` (L20), `database/seeders/RoleSeeder.php` |
| **Payment Gateway** | Chapa (Primary ETB) & Stripe | Chapa v1 API / Stripe PHP `^19.1` | `app/Services/ChapaPaymentService.php`, `composer.json` (L22) |
| **AI Integration** | Google Gemini API & Groq API | REST HTTP Client (`generativelanguage.googleapis.com`) | `app/Services/AiCoachingService.php`, `test_gemini.php`, `test_groq_connection.php` |
| **Deployment / Container**| Docker (Apache + PHP 8.3) & Fly.io | Multi-stage Dockerfile | `Dockerfile`, `fly.toml` |

---

## 3. Architecture & Design Patterns

The project follows a modular **Laravel MVC (Model-View-Controller)** pattern extended with specialized Service Objects, Repositories, DTOs, and Domain Enums.

```
                  +-----------------------------------+
                  |        Web Browser Client         |
                  +-----------------------------------+
                                    |
                            HTTP Requests (Vite / Blade)
                                    v
                  +-----------------------------------+
                  |         Routes & Middleware       |
                  |  (web.php, Role / Verification)   |
                  +-----------------------------------+
                                    |
                                    v
                  +-----------------------------------+
                  |          Controllers              |
                  | (Admin, Analyst, Trader, Public)  |
                  +-----------------------------------+
                                    |
          +-------------------------+-------------------------+
          |                         |                         |
          v                         v                         v
+-------------------+     +-------------------+     +-------------------+
|  Service Layer    |     |  Eloquent Models  |     | External APIs     |
| - TradeAnalytics  |     | - Trade           |     | - Chapa Payment   |
| - AiCoaching      |     | - User            |     | - Stripe Payment  |
| - Performance     |     | - Subscription    |     | - Gemini AI       |
| - Behavioral      |     | - Feedback        |     | - Groq LLM        |
+-------------------+     +-------------------+     +-------------------+
          |                         |
          v                         v
+-----------------------------------------------------------------+
|                        Database Layer                           |
|       (SQLite / MySQL / PostgreSQL via Eloquent ORM)            |
+-----------------------------------------------------------------+
```

### Architectural Highlights
- **Service Layer Pattern (`app/Services/`)**: Decouples business logic from controllers.
  - `TradeAnalyticsService.php`: Computes win rate, profit factor, max drawdown, and expectancy.
  - `AiCoachingService.php`: Interfaces with Google Gemini API to construct prompt payloads and generate structured JSON coaching recommendations.
  - `ChapaPaymentService.php`: Handles ETB payment initialization, transaction verification, local simulation fallback, and webhooks.
  - `BehavioralAnalysisService.php`: Scans trade histories for revenge trading, overtrading, or position sizing violations.
  - `PerformanceAnalysisService.php` & `QuantitativeAnalysisService.php`: In-depth statistical evaluation.
- **Role-Based Access Control (RBAC)**: Managed via `spatie/laravel-permission`. Roles include `admin`, `analyst`, and `trader` defined in `database/seeders/RoleSeeder.php`.
- **Middleware Guarding (`app/Http/Middleware/EnsureVerified.php`)**: Ensures users complete mandatory verification steps before gaining full role access.
- **Event-Driven Observer Pattern (`app/Observers/`)**: Automatically triggers achievements, XP awards, and notifications upon trade insertion/update.

---

## 4. Key Subsystems & Folder Structure

```
forex_journal/
├── app/                        # Core Application Code
│   ├── Console/                # Artisan Commands
│   ├── DTOs/                   # Data Transfer Objects
│   ├── Enums/                  # PHP Enums (TradeOutcome, RiskLevel, ApplicationStatus)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin Dashboard, User Management, Backups, Analytics
│   │   │   ├── Analyst/        # Analyst Dashboard, Feedback, Payouts, Templates
│   │   │   ├── Auth/           # Login, Register, Password Reset, Verification
│   │   │   └── Trader/         # Trader Dashboard, Trade Management, Accounts, Strategies
│   │   └── Middleware/         # Custom Middleware (EnsureVerified, Role checks)
│   ├── Models/                 # 22 Eloquent Models (User, Trade, Subscription, Feedback, etc.)
│   ├── Observers/              # Model Lifecycle Observers
│   ├── Policies/               # Authorization Policies
│   ├── Providers/              # Service Providers
│   └── Services/               # 12 Domain Service Classes
├── bootstrap/                  # App initialization (app.php, providers.php)
├── config/                     # Configuration files (auth, database, services, backup, etc.)
├── database/
│   ├── factories/              # Model Factories
│   ├── migrations/             # 53 Migration files
│   └── seeders/                # 11 Database Seeders
├── public/                     # Public Web Root (index.php, compiled Vite assets)
├── resources/
│   ├── css/                    # Tailwind CSS assets
│   ├── js/                     # Client JavaScript modules
│   └── views/                  # Blade templates (Admin, Analyst, Trader layouts)
├── routes/
│   ├── console.php             # CLI Console commands
│   ├── debug_photo.php         # Diagnostic photo debugging
│   └── web.php                 # Application Web Routes (530 lines)
├── storage/                    # Application logs, file uploads, backups
├── tests/                      # Feature and Unit test suites
├── Dockerfile                  # Apache + PHP 8.3 production deployment container
├── fly.toml                    # Fly.io hosting configuration
├── composer.json               # PHP Dependencies
├── package.json                # NPM Frontend Dependencies
└── vite.config.js              # Vite bundler configuration
```

---

## 5. Documented External Service Integrations

| External Service | Purpose | Required / Optional | Config Keys | Credential Acquisition | Fallback Behavior |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Chapa Payment Gateway** | Primary payment gateway for Ethiopian Birr (ETB) analyst subscriptions. | **Required** (for Analyst marketplace monetization) | `CHAPA_MODE`, `CHAPA_PUBLIC_KEY`, `CHAPA_SECRET_KEY`, `CHAPA_SECRET_HASH` | Register account at [chapa.co](https://chapa.co) and generate API keys. | Local simulation mode available when `CHAPA_MODE=simulation`. |
| **Google Gemini AI** | Automated AI feedback draft generation for trading reviews. | **Optional** (AI feature) | `GEMINI_API_KEY` | Obtain API key from [Google AI Studio](https://aistudio.google.com/). | Falls back to rule-based template summary if key is missing or fails. |
| **Groq AI** | LLM testing integration (`llama3-70b-8192`). | **Optional** (Experimental) | `GROQ_API_KEY` | Register at [console.groq.com](https://console.groq.com/). | Script diagnostic test fails gracefully. |
| **Stripe** | International card payments alternative. | **Optional** | `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` | Create developer account on [stripe.com](https://stripe.com). | Subscriptions fallback to Chapa or offline testing. |
| **Mail Providers** | Transactional emails (activation, alerts, password resets). | **Optional** in dev (Log driver default) | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `RESEND_API_KEY`, `POSTMARK_API_KEY`, `AWS_ACCESS_KEY_ID` | Signup with Resend, Postmark, AWS, or use Mailtrap/Log. | `MAIL_MAILER=log` writes emails to `storage/logs/laravel.log`. |

---

## 6. Verification Source Attribution & Confidence Ratings

| Information Item | Verified Source File | Line Number / Key | Confidence Rating |
| :--- | :--- | :--- | :--- |
| Framework Version | `composer.json` | Line 13 (`"laravel/framework": "^12.0"`) | **High (Verified)** |
| PHP Version Constraint | `composer.json` | Line 9 (`"php": "^8.2"`) | **High (Verified)** |
| Docker Image | `Dockerfile` | Line 2 (`php:8.3-apache`) | **High (Verified)** |
| Frontend Bundler | `package.json` | Line 15 (`"vite": "^7.0.7"`) | **High (Verified)** |
| Default Database | `.env.example` & `fly.toml` | `DB_CONNECTION=sqlite` | **High (Verified)** |
| Chapa Integration | `app/Services/ChapaPaymentService.php` | Lines 16–29 | **High (Verified)** |
| Gemini AI Integration | `app/Services/AiCoachingService.php` | Lines 14–26 | **High (Verified)** |
| Database Seeders | `database/seeders/DatabaseSeeder.php` | Lines 18–28 | **High (Verified)** |
| Loose Debug Scripts | Repository Root | 35+ `.php` and `.sql` root files | **High (Verified)** |

---

## 7. Assumptions & Inferred Details

1. **Production Database Engine**: While local development and Fly.io rely on SQLite (`database/database.sqlite`), the presence of `pdo_mysql` and `pdo_pgsql` extensions in `Dockerfile` indicates production support for MySQL or PostgreSQL. *(Confidence: High)*
2. **Environment Variable Omissions**: The `.env.example` file omits `GEMINI_API_KEY`, `GROQ_API_KEY`, and `STRIPE_*` keys, which are present in `app/Services/AiCoachingService.php` and `config/services.php`. *(Confidence: High)*
