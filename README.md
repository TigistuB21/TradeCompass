# TradeCompass

> Navigate Every Trade with Confidence.

TradeCompass is an AI-powered trading journal and performance analytics platform designed to help traders record, analyze, and improve their trading performance through data-driven insights, AI coaching, and expert performance reviews.

Built with Laravel, TradeCompass transforms trading history into actionable insights by combining journaling, analytics, behavioral analysis, and personalized feedback.

---

## Features

### Trading Journal
- Record and organize every trade
- Upload trade screenshots
- Track emotions and trading psychology
- Categorize trades with tags
- Add personal notes

### Performance Analytics
- Interactive trading dashboard
- Win/Loss analysis
- Risk-to-Reward statistics
- Trading session analysis
- Currency pair performance
- Equity and performance charts
- Historical performance tracking

### AI Coaching
- AI-generated trade feedback
- Behavioral analysis
- Personalized trading insights
- Performance recommendations
- Trading habit evaluation

### Performance Analyst Marketplace
- Connect traders with professional analysts
- Request expert reviews
- Receive personalized feedback
- Analyst dashboards
- Subscription management

### Gamification
- Achievement system
- XP rewards
- Trading milestones
- Progress tracking

### Administration
- User management
- Analyst management
- Subscription management
- Payment management
- Platform monitoring

---

## Technology Stack

### Backend

- Laravel 12
- PHP 8.2+
- Eloquent ORM
- Laravel Queues
- Laravel Scheduler

### Frontend

- Blade
- Tailwind CSS 4
- Alpine.js
- Vite
- Chart.js

### Database

- SQLite
- MySQL
- PostgreSQL (supported)

### Integrations

- Gemini AI
- Groq AI
- Stripe
- Chapa Payment Gateway

---

## Project Structure

```
app/
bootstrap/
config/
database/
docs/
public/
resources/
routes/
storage/
tests/
```

---

## Installation

Clone the repository

```bash
git clone https://github.com/YourUsername/TradeCompass.git
cd TradeCompass
```

Install PHP dependencies

```bash
composer install
```

Install frontend dependencies

```bash
npm install
```

Copy the environment file

```bash
cp .env.example .env
```

Generate the application key

```bash
php artisan key:generate
```

Run database migrations

```bash
php artisan migrate
```

Seed the database (if available)

```bash
php artisan db:seed
```

Start the development server

```bash
composer run dev
```

---

## Documentation

Additional documentation is available in the repository.

- PROJECT_ANALYSIS.md
- DEPENDENCIES.md
- SETUP_GUIDE.md
- QUICK_START.md
- CONTRIBUTING.md
- PROJECT_HEALTH_REPORT.md
- IMPLEMENTATION_ROADMAP.md

---

## Architecture

TradeCompass follows a layered Laravel architecture based on the MVC pattern with dedicated service classes responsible for business logic.

Core modules include:

- Authentication
- Trade Journal
- Analytics
- AI Coaching
- Performance Analysts
- Messaging
- Payments
- Achievements
- Administration

---

## Screenshots

Screenshots will be added soon.

---

## Roadmap

- Enhanced AI coaching
- Mobile application
- Broker integrations
- REST API
- Portfolio analytics
- Trading calendar
- Strategy performance tracking
- Advanced reporting
- Docker deployment
- Cloud deployment

---

## Contributing

Contributions are welcome.

Please read:

```
CONTRIBUTING.md
```

before opening issues or submitting pull requests.

---

## License

This project is licensed under the MIT License.

---

## Author

Developed by **Tigistu Begashaw**

Computer Science Graduate

GitHub: https://github.com/YourUsername

---

## Vision

TradeCompass aims to become a comprehensive trading companion that empowers traders to make better decisions through data, analytics, expert guidance, and artificial intelligence.