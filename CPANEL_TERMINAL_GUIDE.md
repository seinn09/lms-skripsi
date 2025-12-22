# cPanel Terminal Quick Guide

![cPanel Terminal](file:///home/al/.gemini/antigravity/brain/a0228a7d-1eae-4649-b3f6-e7b10a596d5e/uploaded_image_1766152293974.png)

## ✅ Yes, This is the Right Page!

You're currently in the **cPanel Terminal** - this is perfect for running deployment commands without needing external SSH access.

---

## 📋 Step-by-Step Commands to Run

After you've uploaded and extracted `lms-cpanel-deploy.zip`, run these commands in order:

### 1. Navigate to Your Project Directory

```bash
cd ~/skripsi-lms.verifikator.web.id
```

Or if the path is different:

```bash
cd /home/verifikasi4/skripsi-lms.verifikator.web.id
```

---

### 2. Verify Files Are There

```bash
ls -la
```

You should see folders like: `app`, `config`, `database`, `public`, etc.

---

### 3. Install Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

**If composer is not found**, install it first:

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

This will take a few minutes. Wait for it to complete.

---

### 4. Copy Environment File

```bash
cp .env.production .env
```

---

### 5. Edit Environment File

```bash
nano .env
```

**Update these values** with your actual MySQL credentials:

-   `DB_DATABASE=` (your database name from cPanel)
-   `DB_USERNAME=` (your database username)
-   `DB_PASSWORD=` (your database password)
-   `APP_URL=https://skripsi-lms.verifikator.web.id`

**To save in nano:**

1. Press `Ctrl + X`
2. Press `Y` (yes to save)
3. Press `Enter`

---

### 6. Generate Application Key

```bash
php artisan key:generate --force
```

---

### 7. Run Database Migrations

```bash
php artisan migrate --force
```

**Optional - Seed initial data:**

```bash
php artisan db:seed --force
```

---

### 8. Create Storage Link

```bash
php artisan storage:link
```

---

### 9. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
```

---

### 10. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🎯 All Commands in One Block (Copy & Paste)

Once you're ready, you can copy this entire block:

```bash
# Navigate to project
cd ~/skripsi-lms.verifikator.web.id

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.production .env

# Generate key
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Done!
echo "✅ Deployment complete!"
```

**Note:** You'll still need to edit `.env` manually with `nano .env` to add your database credentials.

---

## 🔍 What You'll See

When you run commands, you'll see output in the terminal like:

-   `composer install` → Shows packages being installed
-   `php artisan migrate` → Shows tables being created
-   `php artisan config:cache` → Shows "Configuration cached successfully"

---

## ⚠️ Important Notes

1. **Before running migrations**, make sure you've:

    - Created MySQL database in cPanel
    - Edited `.env` with correct credentials

2. **If you see errors**, check:

    - Database credentials are correct
    - Database exists
    - User has permissions on database

3. **Current directory**: The terminal shows `verifikasi4@polite~$` which means you're in the home directory. You need to `cd` to your project first.

---

## 🆘 Common Issues

**"composer: command not found"**

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

**"Permission denied"**

```bash
chmod -R 775 storage bootstrap/cache
```

**"Database connection failed"**

-   Double-check `.env` credentials
-   Verify database exists in cPanel → MySQL Databases

---

## ✅ You're Ready!

This cPanel Terminal is perfect for deployment. You don't need external SSH - just use this terminal to run all the commands after uploading your zip file.
