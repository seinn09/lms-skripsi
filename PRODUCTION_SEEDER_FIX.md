# Production Seeder Fix - Quick Guide

## Problem

`Class "Faker\Factory" not found` - Faker is a dev dependency, not available in production when using `composer install --no-dev`.

## Solution

Created `ProductionUserSeeder.php` that doesn't use Faker or factories.

## What to Do on Server

You have **already extracted** the old zip. Here's what to do:

### Option 1: Re-upload Fixed Package (Recommended)

1. **Delete all files** in `/home/verifik4/skripsi-lms.verifikator.web.id/`
2. **Upload new** `lms-cpanel-with-vendor.zip` (19MB)
3. **Extract** it
4. **Continue with commands** below

### Option 2: Fix on Server (If You Don't Want to Re-upload)

Run this in cPanel Terminal:

```bash
cd ~/skripsi-lms.verifikator.web.id

# Download the fixed seeder file
# (You'll need to upload ProductionUserSeeder.php manually via File Manager)
# Place it in: database/seeders/ProductionUserSeeder.php

# Then edit DatabaseSeeder.php to use ProductionUserSeeder instead of UserSeeder
```

---

## Commands to Run After Upload/Extract

```bash
cd ~/skripsi-lms.verifikator.web.id

# Create directories
mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs
chmod -R 775 bootstrap/cache storage

# Setup environment
cp .env.production .env
nano .env  # Edit MySQL credentials

# Deploy
php artisan key:generate --force
php artisan migrate --seed --force  # Now works without Faker!
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## What Was Fixed

-   ✅ Created `ProductionUserSeeder` (no Faker dependency)
-   ✅ Updated `DatabaseSeeder` to use production seeder
-   ✅ Skipped `CourseSeeder` and `ExamSeeder` (they use Faker)
-   ✅ New package ready at: `/home/al/Documents/lms-skripsi/lms-cpanel-with-vendor.zip`

---

## Accounts Created by Seeder

After running `migrate --seed`, you'll have:

| Email                      | Password | Role             |
| -------------------------- | -------- | ---------------- |
| superadministrator@app.com | password | Superadmin       |
| admin1@app.com             | password | Admin            |
| admin2@app.com             | password | Admin            |
| staff_ti@app.com           | password | Staff Prodi (TI) |
| staff_te@app.com           | password | Staff Prodi (TE) |
| pengajar1@app.com          | password | Pengajar         |
| pengajar2@app.com          | password | Pengajar         |
| mahasiswa1@app.com         | password | Mahasiswa        |
| mahasiswa2@app.com         | password | Mahasiswa        |

All passwords are: **`password`** (change after first login!)
