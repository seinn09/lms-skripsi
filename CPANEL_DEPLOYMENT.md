# cPanel Deployment Guide

## ✅ Pre-Upload Verification Complete

Your project structure is correct:

-   ✅ `/public` folder exists with `index.php`
-   ✅ No `index.php` in root folder
-   ✅ Deployment package created: `lms-cpanel-deploy.zip` (9.3MB)

---

## 📦 Package Details

**File:** `/home/al/Documents/lms-skripsi/lms-cpanel-deploy.zip`
**Size:** 9.3 MB

**Excluded from package:**

-   ❌ `node_modules/` (will install on server)
-   ❌ `vendor/` (will install on server)
-   ❌ `.git/` (not needed in production)
-   ❌ `.env` (will use `.env.production`)
-   ❌ Development logs and cache files

**Included in package:**

-   ✅ All application code
-   ✅ `/public` folder with assets
-   ✅ Database migrations
-   ✅ Configuration files
-   ✅ `.env.production` template
-   ✅ `deploy.sh` script
-   ✅ Production assets (built)

---

## 🚀 Step-by-Step cPanel Deployment

### Step 1: Upload to cPanel

1. **Login to cPanel** at your Jagoan Hosting control panel
2. **Open File Manager**
3. **Navigate to:** `/home/verifikasi4/skripsi-lms.verifikator.web.id/`
4. **Click "Upload"** button
5. **Select file:** `lms-cpanel-deploy.zip` from `/home/al/Documents/lms-skripsi/`
6. **Wait for upload** to complete (9.3MB)

![cPanel Upload](file:///home/al/.gemini/antigravity/brain/a0228a7d-1eae-4649-b3f6-e7b10a596d5e/uploaded_image_1766150143226.png)

---

### Step 2: Extract Files

1. **In File Manager**, right-click on `lms-cpanel-deploy.zip`
2. **Click "Extract"**
3. **Confirm extraction path**
4. **Wait for extraction** to complete
5. **Delete** `lms-cpanel-deploy.zip` after extraction

---

### Step 3: Configure Document Root

Your Laravel app needs the document root to point to the `/public` folder.

**Option A: Using .htaccess in root (if public_html is root)**

If your domain points to the root folder, create `.htaccess` in root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Option B: Change Document Root in cPanel**

1. Go to **cPanel → Domains**
2. Find your domain `skripsi-lms.verifikator.web.id`
3. Click **"Manage"**
4. Change **Document Root** to: `/home/verifikasi4/skripsi-lms.verifikator.web.id/public`
5. **Save changes**

> **Recommended:** Use Option B for better security

---

### Step 4: Install Dependencies via SSH

**Login via SSH:**

```bash
ssh verifikasi4@your-server.jagoanhostingcom
cd ~/skripsi-lms.verifikator.web.id
```

**Install Composer dependencies:**

```bash
composer install --no-dev --optimize-autoloader
```

> **Note:** If `composer` is not available, download it:
>
> ```bash
> curl -sS https://getcomposer.org/installer | php
> php composer.phar install --no-dev --optimize-autoloader
> ```

---

### Step 5: Configure Environment

**Copy production environment:**

```bash
cp .env.production .env
```

**Edit .env with your MySQL credentials:**

```bash
nano .env
```

Update these values:

```env
APP_URL=https://skripsi-lms.verifikator.web.id

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_mysql_database_name
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

MAIL_HOST=smtp.your-provider.com
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-mail-password
```

**Save:** Press `Ctrl+X`, then `Y`, then `Enter`

---

### Step 6: Generate Application Key

```bash
php artisan key:generate --force
```

This will generate a unique encryption key for your application.

---

### Step 7: Run Database Migrations

```bash
php artisan migrate --force
```

This will create all database tables.

**Optional - Seed initial data:**

```bash
php artisan db:seed --force
```

---

### Step 8: Create Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

---

### Step 9: Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R verifikasi4:verifikasi4 storage bootstrap/cache
```

Or if you need more permissive settings:

```bash
chmod -R 775 storage bootstrap/cache
```

---

### Step 10: Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Step 11: Verify Installation

Visit your website: `https://skripsi-lms.verifikator.web.id`

**Check:**

-   ✅ Homepage loads without errors
-   ✅ Login page accessible
-   ✅ Assets (CSS/JS) load correctly
-   ✅ No 500 errors

**If you see errors:**

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 🔧 Alternative: Using cPanel Terminal (No SSH)

If SSH is not available, use **cPanel Terminal**:

1. **Open cPanel → Terminal**
2. **Navigate to project:**
    ```bash
    cd ~/skripsi-lms.verifikator.web.id
    ```
3. **Follow Steps 4-10** above

---

## 📝 Post-Deployment Checklist

-   [ ] Application loads without errors
-   [ ] Login functionality works
-   [ ] Database connection successful
-   [ ] File uploads work
-   [ ] Email sending works (test forgot password)
-   [ ] Search functionality works
-   [ ] Multi-tenancy works correctly
-   [ ] SSL certificate is active (HTTPS)
-   [ ] All assets load correctly

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**

```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Issue: Database Connection Failed

**Solution:**

-   Verify MySQL credentials in `.env`
-   Ensure database exists in cPanel → MySQL Databases
-   Check if user has permissions on the database

### Issue: Assets Not Loading (404)

**Solution:**

```bash
# Rebuild assets if needed
npm install
npm run build

# Or ensure public/build folder exists in the zip
```

### Issue: Storage Link Not Working

**Solution:**

```bash
# Remove old link
rm public/storage

# Create new link
php artisan storage:link
```

### Issue: Composer Not Found

**Solution:**

```bash
# Download Composer
curl -sS https://getcomposer.org/installer | php

# Use it
php composer.phar install --no-dev --optimize-autoloader
```

---

## 🔄 Updating the Application

When you need to deploy updates:

1. **Create new deployment package** on your local machine
2. **Upload to cPanel**
3. **Extract** (overwrite existing files)
4. **Run via SSH/Terminal:**
    ```bash
    cd ~/skripsi-lms.verifikator.web.id
    php artisan down
    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan up
    ```

---

## 📞 Support

-   **cPanel Issues:** Contact Jagoan Hosting support
-   **Application Errors:** Check `storage/logs/laravel.log`
-   **Database Issues:** Check cPanel → MySQL Databases

---

## 🎉 Success!

Once everything is working, your LMS application is live at:
**https://skripsi-lms.verifikator.web.id**

Remember to:

-   Monitor logs regularly
-   Keep backups of database
-   Update dependencies periodically
-   Monitor disk space usage
