# SaaS Admin Dashboard Demo

[![Live Demo](https://img.shields.io/badge/demo-live-brightgreen)](https://demo.yoursaas.com)
[![Laravel](https://img.shields.io/badge/Laravel-11-red)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3-brightgreen)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-blue)](https://tailwindcss.com)

A production-ready SaaS admin dashboard showcasing multi-tenancy, RBAC, Stripe billing, and analytics — built for Upwork portfolio.

## Demo Credentials

| Role  | Email             | Password   |
|-------|-------------------|------------|
| Admin | admin@demo.com    | password   |
| Member| member@demo.com   | password   |

## Features

- **Multi-tenancy** — each account is completely isolated
- **RBAC** — role-based access control (Admin / Member) via spatie/laravel-permission
- **Stripe Billing** — Checkout, Customer Portal, webhook handling via Laravel Cashier
- **Analytics Dashboard** — MRR chart, active users, churn rate metrics
- **User Management** — invite, deactivate, assign roles
- **Inertia.js SPA** — seamless navigation without full page reloads

## Tech Stack

| Layer     | Technology                     |
|-----------|-------------------------------|
| Backend   | Laravel 11                     |
| Auth      | Laravel Breeze + Sanctum       |
| Frontend  | Vue 3 + Inertia.js             |
| Styling   | Tailwind CSS v3                |
| Database  | MySQL 8                        |
| Billing   | Laravel Cashier (Stripe)       |
| Charts    | ApexCharts (vue3-apexcharts)   |

## Local Setup

**One-command setup** (uses SQLite — no MySQL needed locally):

```bash
git clone https://github.com/yourhandle/saas-demo.git
cd saas-demo
bash setup-demo.sh
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) and log in with `admin@demo.com / password`.

<details>
<summary>Manual setup</summary>

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Edit .env — set DB_* and STRIPE_* credentials
php artisan migrate --seed
php artisan serve &
npm run dev
```
</details>

## Stripe Setup

1. Create three products in the [Stripe dashboard](https://dashboard.stripe.com/products) (Starter $29, Pro $79, Enterprise $199)
2. Copy the Price IDs into `.env` as `STRIPE_PRICE_STARTER`, `STRIPE_PRICE_PRO`, `STRIPE_PRICE_ENTERPRISE`
3. Add a webhook endpoint pointing to `/stripe/webhook` in Stripe dashboard
4. Set `STRIPE_WEBHOOK_SECRET` to the webhook signing secret

## Deployment (Railway)

1. Connect your GitHub repo to [Railway](https://railway.app)
2. Add a MySQL plugin
3. Set all environment variables from `.env.example`
4. Railway will auto-detect the `railway.toml` and deploy

### Required Environment Variables on Railway

```
APP_KEY=           (generate with: php artisan key:generate --show)
APP_URL=           https://your-app.up.railway.app
DB_HOST=           (from Railway MySQL plugin)
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_PRICE_STARTER=
STRIPE_PRICE_PRO=
STRIPE_PRICE_ENTERPRISE=
```

## Demo Flow (Loom script)

1. Login as `admin@demo.com` → Dashboard with MRR chart and 4 metric cards
2. Users → paginated table, invite a user, change role via dropdown
3. Logout → login as `member@demo.com` → no Billing/Users in sidebar (RBAC)
4. Login as admin → Billing → Plans page → click Upgrade to Pro
5. Stripe Checkout with test card `4242 4242 4242 4242`
6. Return to Billing Portal → "Pro Plan — Active"
