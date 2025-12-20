#!/bin/bash

# Payment Sync Challenge - Setup Script (Mac/Linux)
# This script sets up a working Laravel project with the challenge files

set -e

echo ""
echo "🚀 Payment Sync Challenge - Setup"
echo "=================================="

# Check prerequisites
command -v composer >/dev/null 2>&1 || { echo "❌ Composer is required. Install from https://getcomposer.org"; exit 1; }
command -v php >/dev/null 2>&1 || { echo "❌ PHP is required."; exit 1; }

# Check we're in the right directory
if [ ! -f "app/Http/Controllers/PaymentWebhookController.php" ]; then
    echo "❌ Please run this script from the payment-sync-challenge directory"
    exit 1
fi

CHALLENGE_DIR=$(pwd)

echo "📁 Current directory: $CHALLENGE_DIR"

# Step 1: Create fresh Laravel project alongside current folder
echo ""
echo "📦 Step 1/5: Creating fresh Laravel project..."
cd ..
rm -rf _laravel_temp_
composer create-project laravel/laravel _laravel_temp_ --quiet --no-interaction

# Step 2: Copy Laravel framework files to challenge directory
echo "📦 Step 2/5: Copying Laravel framework..."

# Core directories (these always exist)
cp -r _laravel_temp_/bootstrap "$CHALLENGE_DIR/"
cp -r _laravel_temp_/config "$CHALLENGE_DIR/"
cp -r _laravel_temp_/public "$CHALLENGE_DIR/"
cp -r _laravel_temp_/storage "$CHALLENGE_DIR/"
cp -r _laravel_temp_/vendor "$CHALLENGE_DIR/"

# App subdirectories - only copy if they exist (Laravel 11 doesn't have Console/Exceptions by default)
[ -d "_laravel_temp_/app/Console" ] && cp -r _laravel_temp_/app/Console "$CHALLENGE_DIR/app/"
[ -d "_laravel_temp_/app/Providers" ] && cp -r _laravel_temp_/app/Providers "$CHALLENGE_DIR/app/"
[ -d "_laravel_temp_/app/Exceptions" ] && cp -r _laravel_temp_/app/Exceptions "$CHALLENGE_DIR/app/"

# We need web.php for Laravel to boot
cp _laravel_temp_/routes/web.php "$CHALLENGE_DIR/routes/"

# Root files
cp _laravel_temp_/artisan "$CHALLENGE_DIR/"
cp _laravel_temp_/composer.lock "$CHALLENGE_DIR/"

# Cleanup temp Laravel
rm -rf _laravel_temp_

# Step 3: Setup environment
echo "📦 Step 3/5: Configuring environment..."
cd "$CHALLENGE_DIR"

# Create .env with SQLite configured
cat > .env << 'EOF'
APP_NAME="Payment Sync Challenge"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

# Generate app key
php artisan key:generate --quiet

# Step 4: Setup database
echo "📦 Step 4/5: Setting up database..."
touch database/database.sqlite
php artisan migrate:fresh --seed

# Step 5: Verify setup
echo "📦 Step 5/5: Verifying setup..."
echo ""
echo "✅ Setup complete!"
echo ""
echo "▶ Start the server:"
echo "  php artisan serve"
echo ""
echo "▶ Test the webhook:"
echo "  curl -X POST http://localhost:8000/api/webhooks/payments \\"
echo "    -H 'Content-Type: application/json' \\"
echo "    -d '{\"event\":\"payment.success\",\"order_ref\":\"ORD-1001\",\"transaction_id\":\"txn_test\",\"amount\":25000}'"
echo ""
echo "▶ Check order status:"
echo "  curl http://localhost:8000/api/orders/ORD-1001"
echo ""
