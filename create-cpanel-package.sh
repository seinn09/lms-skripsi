#!/bin/bash

###############################################################################
# cPanel Deployment Package Creator
###############################################################################

echo "🎁 Creating cPanel deployment package..."

# Set variables
PROJECT_DIR="/home/al/Documents/lms-skripsi/project-lms-skripsi"
OUTPUT_DIR="/home/al/Documents/lms-skripsi"
ZIP_NAME="lms-cpanel-deploy.zip"

# Navigate to project directory
cd "$PROJECT_DIR"

# Create zip excluding unnecessary files
echo "📦 Compressing files (excluding node_modules, .git, vendor)..."

zip -r "$OUTPUT_DIR/$ZIP_NAME" . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x "vendor/*" \
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
echo "📋 Next steps:"
echo "  1. Upload $ZIP_NAME to cPanel File Manager"
echo "  2. Extract in /home/verifikasi4/skripsi-lms.verifikator.web.id/"
echo "  3. Run: composer install --no-dev --optimize-autoloader"
echo "  4. Copy .env.production to .env and configure"
echo "  5. Run: php artisan key:generate"
echo "  6. Run: php artisan migrate --force"
echo "  7. Run: php artisan storage:link"
echo "  8. Set permissions: chmod -R 755 storage bootstrap/cache"
