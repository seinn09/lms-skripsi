#!/bin/bash

###############################################################################
# Create cPanel Package WITH Vendor Folder
# Use this when server has eval() disabled and can't run composer
###############################################################################

echo "📦 Creating cPanel deployment package WITH vendor folder..."

# Set variables
PROJECT_DIR="/home/al/Documents/lms-skripsi/project-lms-skripsi"
OUTPUT_DIR="/home/al/Documents/lms-skripsi"
ZIP_NAME="lms-cpanel-with-vendor.zip"

# Navigate to project directory
cd "$PROJECT_DIR"

# Create zip INCLUDING vendor (but excluding node_modules and .git)
echo "📦 Compressing files (including vendor, excluding node_modules and .git)..."

zip -r "$OUTPUT_DIR/$ZIP_NAME" . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x ".env" \
  -x ".env.production.pgsql" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*" \
  -x "*.log" \
  -x ".DS_Store" \
  -x "Thumbs.db"

echo ""
echo "✅ Package created: $OUTPUT_DIR/$ZIP_NAME"
echo ""
echo "📊 Package info:"
ls -lh "$OUTPUT_DIR/$ZIP_NAME"
echo ""
echo "⚠️  NOTE: This package includes vendor/ folder (larger file size)"
echo "   Use this when server cannot run composer due to eval() restrictions"
echo ""
echo "📋 Next steps:"
echo "  1. Upload $ZIP_NAME to cPanel File Manager"
echo "  2. Extract in /home/verifik4/skripsi-lms.verifikator.web.id/"
echo "  3. SKIP composer install (vendor already included!)"
echo "  4. Copy .env.production to .env and configure"
echo "  5. Run: php artisan key:generate --force"
echo "  6. Run: php artisan migrate --force"
echo "  7. Run: php artisan storage:link"
echo "  8. Set permissions: chmod -R 775 storage bootstrap/cache"
echo "  9. Run: php artisan config:cache"
echo "  10. Run: php artisan route:cache"
echo "  11. Run: php artisan view:cache"
