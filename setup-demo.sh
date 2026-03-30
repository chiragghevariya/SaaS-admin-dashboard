#!/usr/bin/env bash
# setup-demo.sh — one-command local demo setup
# Usage: bash setup-demo.sh
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
info()    { echo -e "${CYAN}[setup]${NC} $*"; }
success() { echo -e "${GREEN}[done]${NC}  $*"; }
warn()    { echo -e "${YELLOW}[warn]${NC}  $*"; }
die()     { echo -e "${RED}[error]${NC} $*"; exit 1; }

# ── Prerequisites ────────────────────────────────────────────────────────────
command -v php  >/dev/null 2>&1 || die "PHP not found. Install PHP 8.2+."
command -v composer >/dev/null 2>&1 || die "Composer not found. See https://getcomposer.org"
command -v node >/dev/null 2>&1 || die "Node.js not found. Install Node 18+."
command -v npm  >/dev/null 2>&1 || die "npm not found."

# ── .env ─────────────────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    info "Creating .env from .env.example …"
    cp .env.example .env
fi

# ── Dependencies ──────────────────────────────────────────────────────────────
info "Installing PHP dependencies …"
composer install --quiet

info "Installing JS dependencies …"
npm install --silent

# ── App key ──────────────────────────────────────────────────────────────────
if grep -q "^APP_KEY=$" .env; then
    info "Generating application key …"
    php artisan key:generate --quiet
fi

# ── SQLite fast-path (skip MySQL for local demo) ─────────────────────────────
if grep -q "^DB_CONNECTION=mysql" .env; then
    warn "Switching DB to SQLite for local demo (no MySQL required)."
    sed -i.bak \
        -e 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' \
        -e 's/^DB_HOST=.*/#DB_HOST=127.0.0.1/' \
        -e 's/^DB_PORT=.*/#DB_PORT=3306/' \
        -e 's/^DB_DATABASE=.*/#DB_DATABASE=saas_demo/' \
        -e 's/^DB_USERNAME=.*/#DB_USERNAME=root/' \
        -e 's/^DB_PASSWORD=.*/#DB_PASSWORD=/' \
        .env
    touch database/database.sqlite
fi

# ── Migrate & seed ────────────────────────────────────────────────────────────
info "Running migrations and seeders …"
php artisan migrate --seed --force --quiet

# ── Storage link ──────────────────────────────────────────────────────────────
php artisan storage:link --quiet 2>/dev/null || true

# ── Build assets ─────────────────────────────────────────────────────────────
info "Building front-end assets …"
npm run build --silent

# ── Done ─────────────────────────────────────────────────────────────────────
success "Setup complete!"
echo ""
echo -e "  ${CYAN}Start the server:${NC}  php artisan serve"
echo -e "  ${CYAN}Open:${NC}              http://localhost:8000"
echo ""
echo -e "  Demo credentials:"
echo -e "    Admin  →  admin@demo.com  /  password"
echo -e "    Member →  member@demo.com /  password"
echo ""
echo -e "  ${YELLOW}Stripe:${NC} Set STRIPE_KEY, STRIPE_SECRET, and price IDs in .env"
echo -e "         then re-run: php artisan migrate:fresh --seed"
echo ""
