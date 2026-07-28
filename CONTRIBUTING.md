# Contributing to Forex Journal

Thank you for contributing to **Forex Journal**! This document provides guidelines for environment setup, code formatting, branch conventions, testing, and submitting Pull Requests (PRs).

---

## 1. Getting Started & Development Setup

1. Fork and clone the repository:
   ```bash
   git clone https://github.com/your-username/forex_journal.git
   cd forex_journal
   ```
2. Set up environment prerequisites as detailed in [SETUP_GUIDE.md](file:///c:/Users/Tigistu/Personal%20Projects/forex_journal/SETUP_GUIDE.md):
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

---

## 2. Git Branch Naming Conventions

All new work should be created on a descriptive topic branch branching off `main`:

| Category | Prefix Pattern | Example |
| :--- | :--- | :--- |
| **New Feature** | `feature/<short-description>` | `feature/chapa-recurring-billing` |
| **Bug Fix** | `fix/<short-description>` | `fix/sqlite-migration-column-missing` |
| **Refactoring** | `refactor/<short-description>` | `refactor/ai-coaching-service` |
| **Documentation** | `docs/<short-description>` | `docs/update-setup-guide` |
| **Testing** | `test/<short-description>` | `test/subscription-renewal-feature` |

---

## 3. Commit Message Standards

We enforce **Conventional Commits** for clear release logs:

### Format
```
<type>(<scope>): <short summary in present imperative mood>

[optional body explaining WHY the change was made]
```

### Commit Types
- `feat`: A new feature for the user or system.
- `fix`: A bug fix.
- `docs`: Changes to documentation files only.
- `style`: Formatting, missing semi-colons, white-space fixes (no code logic change).
- `refactor`: Code change that neither fixes a bug nor adds a feature.
- `test`: Adding missing tests or correcting existing tests.
- `chore`: Maintenance tasks, dependency updates, build configuration changes.

### Example Commit
```
feat(analyst): add AI-generated feedback draft button in analyst view

Integrates Gemini 2.5 Flash API inside AnalystFeedbackController to automatically generate initial review drafts based on trader performance statistics.
```

---

## 4. PHP Coding Standards & Formatting

The project enforces Laravel/PSR-12 coding standards using **Laravel Pint**.

### Run Linter / Formatter Before Committing
Before pushing your changes, format PHP code using Pint:

```bash
# Check code formatting without mutating files
vendor/bin/pint --test

# Fix formatting automatically
vendor/bin/pint
```

### Key Coding Guidelines
- **Strict Typing**: Use type hints for all method parameters and return types.
- **Service Objects**: Keep Controllers thin by moving business calculations into `app/Services/`.
- **Enums**: Avoid magic strings; use PHP Enums located in `app/Enums/` (e.g., `TradeOutcome`, `RiskLevel`).
- **Blade Conventions**: Maintain component modularity in `resources/views/components/`.

---

## 5. Running Automated Tests

All pull requests must pass the automated test suite.

### Run Full Test Suite
```bash
composer run test
# OR
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Feature/SubscriptionRenewalTest.php
```

---

## 6. Pull Request (PR) Submission Checklist

Before opening a Pull Request:
- [ ] Code passes all tests (`php artisan test`).
- [ ] Code passes formatting checks (`vendor/bin/pint --test`).
- [ ] No temporary debug scripts or commented-out code left behind.
- [ ] Branch is rebased onto latest `main`.
- [ ] PR title follows Conventional Commit naming.
- [ ] PR description clearly explains the changes, why they were made, and testing steps.
