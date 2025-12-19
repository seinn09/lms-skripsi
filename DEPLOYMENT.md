# Production Deployment Guide for Jagoan Hosting

## Prerequisites

Before deploying, ensure you have:

1. ✅ Access to Jagoan Hosting control panel
2. ✅ Production database created (PostgreSQL)
3. ✅ Domain configured and pointing to your hosting
4. ✅ SSH access to your hosting server
5. ✅ SSL certificate configured (Let's Encrypt recommended)

## Step-by-Step Deployment

### 1. Configure Production Environment

Edit `.env.production` and update the following:

```bash
# Update these values with your actual production credentials:
APP_URL=https://yourdomain.com
DB_HOST=your-database-host
DB_DATABASE=your_production_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
MAIL_HOST=smtp.your-provider.com
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-mail-password
```

### 2. Generate Production APP_KEY

```bash
# This will be done automatically by deploy.sh
# Or manually:
php artisan key:generate --env=production
```

### 3. Build Production Assets Locally (Optional)

```bash
npm run build
```

This creates optimized assets in `public/build/`.

### 4. Upload Files to Jagoan Hosting

Upload these files/folders via FTP/SFTP or Git:

**Required files:**

-   All application files EXCEPT:
    -   `node_modules/` (will install on server)
    -   `vendor/` (will install on server)
    -   `.env` (use `.env.production` instead)
    -   `storage/` contents (except directory structure)

**Important:** Ensure `.env.production` is uploaded but NOT committed to git.

### 5. SSH into Your Server

```bash
ssh your-username@your-server.jagoanhostingcom
cd /path/to/your/application
```

### 6. Run Deployment Script

```bash
./deploy.sh
```

This script will:

-   ✅ Enable maintenance mode
-   ✅ Install dependencies
-   ✅ Build assets
-   ✅ Run migrations
-   ✅ Optimize caches
-   ✅ Set permissions
-   ✅ Disable maintenance mode

### 7. Configure Web Server

#### For Apache (.htaccess)

Ensure your document root points to `public/` directory.

Create/verify `.htaccess` in `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### For Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 8. Set Directory Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Verify Deployment

Visit your domain and check:

-   ✅ Homepage loads correctly
-   ✅ Login functionality works
-   ✅ Database connections are working
-   ✅ Assets (CSS/JS) load properly
-   ✅ File uploads work (if applicable)

## Post-Deployment Checklist

-   [ ] Test all major features
-   [ ] Check error logs: `storage/logs/laravel.log`
-   [ ] Verify email sending works
-   [ ] Test user registration/login
-   [ ] Verify multi-tenancy works correctly
-   [ ] Check course creation and enrollment
-   [ ] Test file uploads
-   [ ] Monitor performance

## Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear and rebuild caches
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

### Issue: Assets not loading (404)

**Solution:**

```bash
# Rebuild assets
npm run build

# Check asset URL in .env
ASSET_URL=https://yourdomain.com
```

### Issue: Database connection failed

**Solution:**

-   Verify database credentials in `.env.production`
-   Ensure database exists and user has proper permissions
-   Check if PostgreSQL is running

### Issue: Permission denied errors

**Solution:**

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Maintenance & Updates

### Deploying Updates

```bash
# SSH into server
cd /path/to/your/application

# Pull latest changes (if using git)
git pull origin main

# Run deployment script
./deploy.sh
```

### Database Backup

```bash
# Create backup before major updates
pg_dump -U username database_name > backup_$(date +%Y%m%d).sql
```

### Monitoring

-   Set up log monitoring: `tail -f storage/logs/laravel.log`
-   Monitor disk space: `df -h`
-   Check database size regularly
-   Set up uptime monitoring (UptimeRobot, Pingdom, etc.)

## Security Best Practices

1. ✅ Keep `APP_DEBUG=false` in production
2. ✅ Use strong, unique `APP_KEY`
3. ✅ Enable HTTPS/SSL
4. ✅ Keep dependencies updated
5. ✅ Regular database backups
6. ✅ Monitor error logs
7. ✅ Use environment variables for sensitive data
8. ✅ Implement rate limiting
9. ✅ Keep `.env.production` secure and never commit to git

## Support

For Jagoan Hosting specific issues:

-   Check their documentation
-   Contact their support team
-   Review server logs in cPanel/hosting panel

For Laravel issues:

-   Check `storage/logs/laravel.log`
-   Review Laravel documentation
-   Check database migrations
