# Forex Journal - 5-Minute Quick Start Guide

⚡ **Get the application running from a fresh clone in less than 5 minutes.**

---

## Prerequisites Check
Ensure you have installed:
- **PHP** `>= 8.2` (`php -v`)
- **Composer** `>= 2.5` (`composer --version`)
- **Node.js** `>= 18` (`node -v`)

---

## ⚡ Fast-Path Terminal Commands

Run the following commands in order inside your terminal:

```bash
# 1. Clone project and navigate into folder
git clone <repository_url> forex_journal
cd forex_journal

# 2. Install PHP and Node dependencies
composer install
npm install

# 3. Setup environment configuration
cp .env.example .env
php artisan key:generate

# 4. Create SQLite database file
touch database/database.sqlite

# 5. Run database migrations & seed default users (Admin, Trader, Analyst)
php artisan migrate:fresh --seed

# 6. Create public storage symlink
php artisan storage:link

# 7. Start unified development server (API, Queue, Vite asset bundling)
composer run dev
```

---

## 🔑 Login Credentials

Open your web browser at **`http://127.0.0.1:8000`** and log in with any seeded account:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@forexjournal.com` | `password` |
| **Trader** | `trader@forexjournal.com` | `password` |
| **Analyst** | `analyst@forexjournal.com` | `password` |

---

## 🧪 Run Tests (Optional Verification)

To verify application health, run the automated test suite:

```bash
php artisan test
```

For complete documentation, troubleshooting, and architecture details, refer to:
- 📖 [SETUP_GUIDE.md](file:///c:/Users/Tigistu/Personal%20Projects/forex_journal/SETUP_GUIDE.md)
- 🏗️ [PROJECT_ANALYSIS.md](file:///c:/Users/Tigistu/Personal%20Projects/forex_journal/PROJECT_ANALYSIS.md)
- 📦 [DEPENDENCIES.md](file:///c:/Users/Tigistu/Personal%20Projects/forex_journal/DEPENDENCIES.md)
