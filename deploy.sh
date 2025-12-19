#!/bin/bash

###############################################################################
# Laravel LMS Production Deployment Script for Jagoan Hosting
###############################################################################

set -e  # Exit on error

echo "🚀 Starting deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo -e "${RED}❌ Error: .env.production file not found!${NC}"
    exit 1
fi

echo -e "${GREEN}✓${NC} Environment file found"

# Enable maintenance mode
echo "🔧 Enabling maintenance mode..."
php artisan down || true

# Pull latest changes (if using git deployment)
# echo "📥 Pulling latest changes..."
# git pull origin main

# Install/Update Composer dependencies (production only, optimized)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install/Update NPM dependencies
echo "📦 Installing NPM dependencies..."
npm ci --production=false

# Build production assets
echo "🏗️  Building production assets..."
npm run build

# Copy production environment file
echo "⚙️  Setting up production environment..."
cp .env.production .env

# Generate application key if not set
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Clear all caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Optimize for production
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link if not exists
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear and warm up cache
echo "🔥 Warming up cache..."
php artisan cache:clear
php artisan config:cache

# Disable maintenance mode
echo "✅ Disabling maintenance mode..."
php artisan up

echo -e "${GREEN}✨ Deployment completed successfully!${NC}"
echo ""
echo "📋 Post-deployment checklist:"
echo "  1. Verify the application is accessible"
echo "  2. Check error logs: storage/logs/laravel.log"
echo "  3. Test critical functionality"
echo "  4. Monitor performance"
