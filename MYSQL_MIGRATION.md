# MySQL Migration Guide

## Changes Made for MySQL Compatibility

### 1. Query Syntax Updates

#### Fixed: Question Model

**File:** `app/Models/Question.php`

**Before (PostgreSQL):**

```php
public function scopeSearch($query, $term)
{
    $term = "%$term%";
    $query->where('question_text', 'ilike', $term);
}
```

**After (MySQL):**

```php
public function scopeSearch($query, $term)
{
    $term = strtolower($term);
    $term = "%$term%";
    $query->whereRaw('LOWER(question_text) like ?', [$term]);
}
```

**Reason:** PostgreSQL's `ILIKE` operator doesn't exist in MySQL. We use `LOWER()` with `LIKE` for case-insensitive searching.

---

### 2. Already Compatible Queries

The following models already use MySQL-compatible syntax:

#### Course Model

```php
// Uses LOWER() with LIKE - works in both PostgreSQL and MySQL
$query->whereRaw('LOWER(course_code) like ?', [$term])
    ->orWhereRaw('LOWER(name) like ?', [$term]);
```

#### CourseClass Model

```php
// Uses LOWER() with LIKE - works in both PostgreSQL and MySQL
$query->whereRaw('LOWER(class_code) like ?', [$term]);
```

---

### 3. Environment Configuration

#### Updated: .env.production

**Changed:**

-   `DB_CONNECTION=pgsql` → `DB_CONNECTION=mysql`
-   `DB_PORT=5432` → `DB_PORT=3306`
-   `DB_HOST` → Usually `localhost` on Jagoan Hosting

**Backup:** Original PostgreSQL config saved as `.env.production.pgsql`

---

### 4. Migration Compatibility

All migrations are MySQL-compatible:

-   ✅ `boolean` → Becomes `TINYINT(1)` in MySQL
-   ✅ `string('uuid')` → Becomes `VARCHAR(255)` in MySQL
-   ✅ `text` → Compatible with MySQL
-   ✅ No `jsonb` or PostgreSQL-specific types found

---

## Testing MySQL Compatibility

### Local Testing (Optional)

If you want to test MySQL locally before deploying:

1. **Install MySQL:**

```bash
sudo apt install mysql-server
```

2. **Create Test Database:**

```bash
mysql -u root -p
CREATE DATABASE lms_test;
CREATE USER 'lms_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON lms_test.* TO 'lms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

3. **Update .env:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_test
DB_USERNAME=lms_user
DB_PASSWORD=password
```

4. **Run Migrations:**

```bash
php artisan migrate:fresh --seed
```

5. **Test Search Functionality:**

```bash
php artisan tinker
>>> Course::search('test')->get()
>>> Question::search('test')->get()
>>> CourseClass::search('test')->get()
```

---

## Deployment to Jagoan Hosting

### 1. Get MySQL Credentials

From your Jagoan Hosting control panel, get:

-   Database name
-   Database username
-   Database password
-   Database host (usually `localhost`)

### 2. Update .env.production

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_username
DB_PASSWORD=your_actual_password
```

### 3. Deploy

Follow the normal deployment process:

```bash
./deploy.sh
```

The script will automatically run migrations on MySQL.

---

## Summary of Changes

| File                      | Change                        | Reason                      |
| ------------------------- | ----------------------------- | --------------------------- |
| `app/Models/Question.php` | `ilike` → `LOWER() with LIKE` | MySQL doesn't support ILIKE |
| `.env.production`         | `pgsql` → `mysql`             | Jagoan Hosting uses MySQL   |
| `.env.production`         | Port `5432` → `3306`          | MySQL default port          |

**Total Code Changes:** 1 file (Question.php)
**Migration Changes:** None needed (already compatible)
**Environment Changes:** Database connection settings

---

## Rollback to PostgreSQL

If you need to switch back to PostgreSQL:

```bash
# Restore PostgreSQL config
cp .env.production.pgsql .env.production

# Revert Question.php
git checkout app/Models/Question.php
```
