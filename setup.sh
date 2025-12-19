#!/bin/bash

# Setup Script for Payment Sync Challenge
# This script creates a fresh Laravel project and copies the challenge files

set -e

echo "🚀 Setting up Payment Sync Challenge..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install it first."
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "README.md" ]; then
    echo "❌ Please run this script from the payment-sync-challenge directory"
    exit 1
fi

# Create a temporary directory for Laravel installation
TEMP_DIR=$(mktemp -d)
echo "📦 Creating fresh Laravel project in temp directory..."

# Install Laravel in temp directory
composer create-project laravel/laravel "$TEMP_DIR/laravel" --quiet

# Copy Laravel core files
echo "📁 Copying Laravel core files..."
cp -r "$TEMP_DIR/laravel/bootstrap" ./
cp -r "$TEMP_DIR/laravel/config" ./
cp -r "$TEMP_DIR/laravel/public" ./
cp -r "$TEMP_DIR/laravel/storage" ./
cp "$TEMP_DIR/laravel/artisan" ./

# Copy app structure but keep our files
cp -r "$TEMP_DIR/laravel/app/Console" ./app/
cp -r "$TEMP_DIR/laravel/app/Exceptions" ./app/ 2>/dev/null || true
cp -r "$TEMP_DIR/laravel/app/Providers" ./app/

# Clean up temp directory
rm -rf "$TEMP_DIR"

# Create .env from example
cp .env.example .env

# Update .env with SQLite configuration
sed -i 's|DB_DATABASE=.*|DB_DATABASE='"$(pwd)"'/database/database.sqlite|g' .env

# Create SQLite database file
touch database/database.sqlite

# Install dependencies
echo "📦 Installing dependencies..."
composer install --quiet

# Generate application key
php artisan key:generate --quiet

# Run migrations and seed
echo "🗃️ Running migrations and seeding database..."
php artisan migrate:fresh --seed

echo ""
echo "✅ Setup complete!"
echo ""
echo "Start the server with:"
echo "  php artisan serve"
echo ""
echo "Test webhook with:"
echo "  curl -X POST http://localhost:8000/api/webhooks/payments \\"
echo "    -H 'Content-Type: application/json' \\"
echo "    -d '{\"event\":\"payment.success\",\"order_ref\":\"ORD-1001\",\"transaction_id\":\"txn_test123\",\"amount\":25000}'"
