# Forex Journal - Prioritized Implementation Roadmap

**Document Version:** 1.0  
**Created:** July 28, 2026  
**Source Audit:** `PROJECT_HEALTH_REPORT.md`  
**Status:** Plan & Design Phase (Read-Only Baseline)  

---

## Executive Overview

This roadmap defines an actionable, incremental implementation plan to resolve technical debt, security vulnerabilities, repository clutter, test coverage gaps, and deployment readiness issues identified during the **Forex Journal** repository audit.

Tasks are grouped into **3 Sequential Milestones** categorized by priority level (**High**, **Medium**, **Low**). Each work item is broken down into measurable sub-tasks, estimated effort, prerequisites, expected outcomes, and risk mitigation strategies.

---

## Roadmap Summary & Milestone Overview

```
+-----------------------------------------------------------------------------------+
| MILESTONE 1: High Priority - Security Hardening & Repository Cleanup              |
| Target: Immediate (Sprint 1) | Total Effort: ~9 Hours                             |
| Focus: Debug routes removal, 35+ loose script refactoring, .env.example alignment  |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
| MILESTONE 2: Medium Priority - Quality, Test Suite & CI/CD Pipeline               |
| Target: Short-Term (Sprint 2) | Total Effort: ~14 Hours                           |
| Focus: GitHub Actions CI workflow, unit/feature tests, README & project metadata  |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
| MILESTONE 3: Low Priority - Performance Optimization & Production Hardening       |
| Target: Medium-Term (Sprint 3) | Total Effort: ~8 Hours                           |
| Focus: Database composite indexes, N+1 query fixes, Docker & Fly.io deployment   |
+-----------------------------------------------------------------------------------+
```

---

## Milestone 1: Security Hardening, Repository Cleanup & Base Health (High Priority)

**Primary Goal:** Eliminate security vulnerabilities, remove loose debug scripts from version control, and align environment configuration.

### Task 1.1: Security Hardening & Route Sanitization
- **Priority:** **High**
- **Estimated Effort:** 2 Hours
- **Dependencies:** None
- **Work Items:**
  1. Remove `/debug-db-fix` closure route (Lines 93–170) from `routes/web.php`.
  2. Remove inline debug route `/debug-photo/{userId}` (Lines 509–527) from `routes/web.php`.
  3. Inspect and restrict access to `routes/debug_photo.php` behind `role:admin` middleware or environment checks.
- **Expected Outcome:** Public HTTP endpoints can no longer execute arbitrary database `ALTER TABLE` DDL queries or dump unauthenticated profile data.
- **Potential Risk:** Developers relying on `/debug-db-fix` for database schema repairs will lose browser shortcut access.
- **Risk Mitigation:** Convert repair operations into a secure Artisan CLI command (see Task 1.2).

---

### Task 1.2: Repository Cleanup & Script Refactoring
- **Priority:** **High**
- **Estimated Effort:** 6 Hours
- **Dependencies:** Task 1.1
- **Work Items:**
  1. **SQL Cleanup:** Delete loose SQL patch files from repository root: `add-profile-columns.sql`, `add_analyst_fields.sql`, `add_columns.sql`, `add_user_cols.sql`, `fix_db.sql`, `fix_migration.sql`.
  2. **Artisan Command Conversion:** Create custom Artisan command `app/Console/Commands/DbHealthCheck.php` (`php artisan db:health-check`) encapsulating diagnostic logic from `check-and-fix-db.php`, `verify_db.php`, `dump_db.php`, and `force_add_columns.php`.
  3. **Obsolete Script Removal:** Remove non-essential diagnostic files: `check_db.php`, `check_login.php`, `check_schema.php`, `create_tables.php`, `create_test_notification.php`, `db-fix-log.txt`, `debug_badge.php`, `debug_db.php`, `debug_filters.php`, `debug_sub.php`, `debug_user_22.php`, `fix-database.php`, `fix-migrations.php`, `fix-profile-migration.php`, `fix_db.php`, `fix_db_pdo.php`, `fix_migrations_table.php`, `list_models.php`, `read_log.php`, `reset_password.php`, `verify_tables.php`.
  4. **Integration Test Migration:** Move stand-alone test scripts (`test_chapa_connection.php`, `test_gemini.php`, `test_groq_connection.php`, `test_unread_count.php`) into structured PHPUnit test classes in `tests/Feature/Integrations/`.
- **Expected Outcome:** Clean root directory with zero loose PHP/SQL fix scripts; diagnostic utilities preserved in proper Artisan commands.
- **Potential Risk:** Deleting a script that contained unmigrated database changes.
- **Risk Mitigation:** Diff each script against `database/migrations/` before deleting to verify all schema columns are already present in formal migrations.

---

### Task 1.3: Environment Configuration Matrix Alignment
- **Priority:** **High**
- **Estimated Effort:** 1 Hour
- **Dependencies:** None
- **Work Items:**
  1. Update `.env.example` to include missing AI and payment configuration keys:
     ```ini
     # AI Integration Credentials
     GEMINI_API_KEY=
     GROQ_API_KEY=

     # Stripe Payment Gateway Credentials
     STRIPE_KEY=
     STRIPE_SECRET=
     STRIPE_WEBHOOK_SECRET=

     # Email Service Credentials
     RESEND_API_KEY=
     POSTMARK_API_KEY=
     ```
  2. Add inline comment documentation in `.env.example` describing default fallback modes.
- **Expected Outcome:** Complete `.env.example` preventing runtime errors for new contributors configuring AI or payment features.
- **Potential Risk:** None.

---

## Milestone 2: Automated Testing, Quality & CI/CD Pipeline (Medium Priority)

**Primary Goal:** Establish continuous integration, expand automated test coverage, and complete project documentation metadata.

### Task 2.1: GitHub Actions CI/CD Pipeline Setup
- **Priority:** **Medium**
- **Estimated Effort:** 3 Hours
- **Dependencies:** Task 1.3
- **Work Items:**
  1. Create `.github/workflows/ci.yml` triggering on `push` and `pull_request` to `main`.
  2. Configure job matrix for PHP 8.2 and 8.3 with SQLite extension.
  3. Execute automated pipeline steps:
     - Install Composer dependencies (`composer install`).
     - Install NPM dependencies & build assets (`npm install && npm run build`).
     - Verify code style (`vendor/bin/pint --test`).
     - Run database migrations (`php artisan migrate:fresh`).
     - Execute test suite (`php artisan test`).
- **Expected Outcome:** Automated feedback and build checks on all incoming pull requests.
- **Potential Risk:** CI build failures due to environment-dependent test assumptions.
- **Risk Mitigation:** Ensure `phpunit.xml` configures in-memory SQLite (`:memory:`) and mock array drivers for mail and queues.

---

### Task 2.2: Test Coverage Expansion
- **Priority:** **Medium**
- **Estimated Effort:** 8 Hours
- **Dependencies:** Task 2.1
- **Work Items:**
  1. **Domain Analytics Tests:** Create `tests/Unit/TradeAnalyticsServiceTest.php` testing win rate, drawdown, and risk/reward calculations.
  2. **AI Service Tests:** Create `tests/Unit/AiCoachingServiceTest.php` with `Http::fake()` to verify Gemini API prompt construction, response JSON parsing, and fallback logic.
  3. **Payment Gateway Tests:** Create `tests/Feature/ChapaPaymentTest.php` testing transaction initialization, simulation mode, and webhook signature verification.
  4. **Analyst Application Tests:** Create `tests/Feature/AnalystApplicationTest.php` verifying submission, admin approval, and role assignment.
- **Expected Outcome:** Core business logic covered by automated unit/feature tests (> 70% coverage).
- **Potential Risk:** Tests relying on live external APIs failing during execution.
- **Risk Mitigation:** Use `Http::fake()` for all third-party API interactions (Gemini, Chapa, Stripe).

---

### Task 2.3: Project Documentation & Open Source Tooling Complete Suite
- **Priority:** **Medium**
- **Estimated Effort:** 3 Hours
- **Dependencies:** None
- **Work Items:**
  1. **Replace Default `README.md`**: Overwrite default Laravel skeleton README with project overview, key features, architecture diagram, onboarding links (`SETUP_GUIDE.md`, `QUICK_START.md`), and build status badges.
  2. **Add Project Metadata Files**:
     - `LICENSE`: Open-source MIT or proprietary license file.
     - `CODE_OF_CONDUCT.md`: Standard community guidelines.
     - `.editorconfig`: Cross-editor formatting definitions.
     - `.prettierrc`: Prettier configuration for Blade/Tailwind styling.
- **Expected Outcome:** Professional repository landing page and clear legal/community metadata.
- **Potential Risk:** None.

---

## Milestone 3: Performance Optimization & Production Hardening (Low Priority)

**Primary Goal:** Optimize database query performance, eliminate N+1 queries, and harden production container deployment.

### Task 3.1: Database Performance Optimization & Indexing
- **Priority:** **Low**
- **Estimated Effort:** 4 Hours
- **Dependencies:** Task 2.2
- **Work Items:**
  1. Create new migration `database/migrations/2026_08_01_000000_add_performance_indexes.php` adding composite indexes:
     - `trades(user_id, created_at, outcome)`
     - `messages(conversation_id, created_at)`
     - `notifications(user_id, is_read)`
     - `subscriptions(analyst_id, trader_id, status)`
  2. Audit Eloquent queries in `TraderAnalyticsController`, `AdminAnalyticsController`, and `AnalystDashboardController` to apply eager loading (`with(['trader', 'strategy', 'account'])`) and eliminate N+1 query bottlenecks.
- **Expected Outcome:** Reduced query latency and optimized database throughput under high transaction volume.
- **Potential Risk:** Long index creation times on production databases with large dataset sizes.
- **Risk Mitigation:** Run index migrations during scheduled maintenance windows.

---

### Task 3.2: Production Docker & Cloud Deployment Hardening
- **Priority:** **Low**
- **Estimated Effort:** 4 Hours
- **Dependencies:** Task 2.1
- **Work Items:**
  1. **Dockerfile Hardening:** Optimize multi-stage build in `Dockerfile` for smaller image size and non-root execution (`www-data`).
  2. **Security Hardening:** Update Apache config to disable directory listing and suppress server banners (`ServerTokens Prod`).
  3. **Fly.io Deployment Tuning:** Configure health check paths (`/login`), persistent volume mounts for storage, and auto-stop machine rules in `fly.toml`.
- **Expected Outcome:** Secure, containerized deployment pipeline ready for Fly.io, Render, or AWS ECS.
- **Potential Risk:** File permission issues on persistent mounted storage volumes.
- **Risk Mitigation:** Explicitly set entrypoint ownership rules (`chown -R www-data:www-data /var/www/html/storage`).

---

## Summary Matrix of All Roadmap Tasks

| Task ID | Task Description | Priority | Effort Est. | Prerequisites | Expected Key Outcome |
| :--- | :--- | :---: | :---: | :--- | :--- |
| **1.1** | Security Hardening & Debug Endpoint Removal | **High** | 2 Hours | None | Public routes sanitized of raw SQL execution endpoints. |
| **1.2** | Root Directory Cleanup & Script Refactoring | **High** | 6 Hours | Task 1.1 | 35+ loose scripts deleted/converted to Artisan commands. |
| **1.3** | Environment Matrix Alignment | **High** | 1 Hour | None | Complete `.env.example` declaring Gemini, Groq & Stripe keys. |
| **2.1** | GitHub Actions CI/CD Setup | **Medium** | 3 Hours | Task 1.3 | Automated testing & linting pipeline on PRs. |
| **2.2** | Automated Test Coverage Expansion | **Medium** | 8 Hours | Task 2.1 | > 70% test coverage for analytics, AI, and payment flows. |
| **2.3** | Documentation Suite & Metadata Files | **Medium** | 3 Hours | None | Custom `README.md`, `LICENSE`, and code formatting configs. |
| **3.1** | Database Composite Indexing & N+1 Fixes | **Low** | 4 Hours | Task 2.2 | Optimized database performance for high trade volume. |
| **3.2** | Production Docker & Fly.io Deployment | **Low** | 4 Hours | Task 2.1 | Hardened multi-stage container & Fly.io hosting setup. |
| **Total** | **All 8 Implementation Tasks** | - | **31 Hours** | - | **Production-Ready, Fully Tested & Clean Repository** |
