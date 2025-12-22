# Deployment Error Fixes

## Issues Encountered

### 1. PSR-4 Autoloading Error

```
Class App\Scopes\TenantScope located in ./app/scopes/TenantScope.php
does not comply with psr-4 autoloading standard
```

**Cause:** Duplicate folders - both `app/Scopes/` (correct) and `app/scopes/` (incorrect, lowercase)

**Fix:** Removed the lowercase `app/scopes/` folder

---

### 2. Cache Path Error

```
Please provide a valid cache path.
```

**Cause:** Missing or unwritable `bootstrap/cache` directory

**Fix:** Created directory structure with proper permissions:

```bash
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache
```

---

## ✅ Fixes Applied

The new deployment package (`lms-cpanel-deploy.zip`) includes:

-   ✅ Removed duplicate `app/scopes/` folder
-   ✅ Created `bootstrap/cache/` directory
-   ✅ Created all required storage directories
-   ✅ Set proper permissions

---

## 🚀 For cPanel Deployment

If you've already uploaded the old zip, run these commands in **cPanel Terminal**:

```bash
# Navigate to project
cd ~/skripsi-lms.verifikator.web.id

# Fix the issues
rm -rf app/scopes
mkdir -p bootstrap/cache
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
chmod -R 775 bootstrap/cache storage

# Now run composer install
composer install --no-dev --optimize-autoloader
```

---

## 📦 Or Re-upload Fixed Package

A new fixed deployment package has been created:

-   **Location:** `/home/al/Documents/lms-skripsi/lms-cpanel-deploy.zip`
-   **Status:** ✅ All issues fixed
-   **Ready:** Upload this to cPanel

---

## ✅ Verification

After running `composer install`, you should see:

```
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
> @php artisan package:discover --ansi
Discovered Package: ...
```

**No errors!** ✅
