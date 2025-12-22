#!/bin/bash

###############################################################################
# Fix cPanel Deployment Issues
###############################################################################

echo "🔧 Fixing deployment issues..."

# Fix 1: Remove duplicate lowercase scopes folder
echo "1. Removing duplicate 'scopes' folder (keeping 'Scopes')..."
if [ -d "app/scopes" ]; then
    rm -rf app/scopes
    echo "   ✅ Removed app/scopes/"
else
    echo "   ℹ️  app/scopes/ not found (already fixed)"
fi

# Fix 2: Ensure bootstrap/cache directory exists and is writable
echo "2. Creating and setting permissions for bootstrap/cache..."
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache
echo "   ✅ bootstrap/cache/ ready"

# Fix 3: Ensure storage directories exist
echo "3. Creating storage directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage
echo "   ✅ Storage directories ready"

echo ""
echo "✅ All fixes applied!"
echo ""
echo "Now you can run:"
echo "  composer install --no-dev --optimize-autoloader"
