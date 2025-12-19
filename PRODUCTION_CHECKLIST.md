# Production Readiness Checklist

## Before Deployment

### Environment Configuration

-   [ ] `.env.production` configured with production values
-   [ ] `APP_ENV=production` set
-   [ ] `APP_DEBUG=false` set
-   [ ] `APP_URL` set to production domain
-   [ ] Database credentials configured
-   [ ] Mail settings configured
-   [ ] `APP_KEY` generated (will be done by deploy.sh)

### Security

-   [ ] `.env.production` added to `.gitignore`
-   [ ] No sensitive data in version control
-   [ ] Strong database password set
-   [ ] HTTPS/SSL certificate configured on hosting
-   [ ] CORS settings reviewed
-   [ ] Rate limiting configured (if needed)

### Code Quality

-   [ ] All migrations tested
-   [ ] No debug code or console.logs left
-   [ ] Error handling implemented
-   [ ] Validation rules in place

### Dependencies

-   [ ] All composer dependencies listed in `composer.json`
-   [ ] All npm dependencies listed in `package.json`
-   [ ] No dev-only dependencies in production

### Database

-   [ ] All migrations ready
-   [ ] Seeders reviewed (only essential data for production)
-   [ ] Database backup strategy planned
-   [ ] Database indexes optimized

### Assets

-   [ ] Production build tested locally (`npm run build`)
-   [ ] Images optimized
-   [ ] Unused assets removed

### Performance

-   [ ] Query optimization reviewed
-   [ ] Eager loading implemented where needed
-   [ ] Cache strategy defined
-   [ ] Session driver configured (database recommended)
-   [ ] Queue driver configured (database recommended)

## During Deployment

-   [ ] Upload all files except `node_modules`, `vendor`, `.env`
-   [ ] Upload `.env.production` separately
-   [ ] Run `./deploy.sh` on server
-   [ ] Verify deployment script completes successfully
-   [ ] Check for any errors in output

## After Deployment

### Immediate Checks

-   [ ] Application loads without errors
-   [ ] Homepage accessible
-   [ ] Login/authentication works
-   [ ] Database connection working
-   [ ] Assets (CSS/JS) loading correctly

### Functional Testing

-   [ ] User registration works
-   [ ] User login works
-   [ ] Course creation works
-   [ ] Course enrollment works
-   [ ] Multi-tenancy working correctly
-   [ ] File uploads work (if applicable)
-   [ ] Email sending works
-   [ ] All major features tested

### Monitoring

-   [ ] Check `storage/logs/laravel.log` for errors
-   [ ] Monitor server resources (CPU, memory, disk)
-   [ ] Set up uptime monitoring
-   [ ] Set up error tracking (optional: Sentry, Bugsnag)

### Performance

-   [ ] Page load times acceptable
-   [ ] Database queries optimized
-   [ ] Caches working correctly

### Security

-   [ ] HTTPS working correctly
-   [ ] No debug information exposed
-   [ ] Error pages don't reveal sensitive info
-   [ ] File permissions correct (755 for directories, 644 for files)
-   [ ] `storage/` and `bootstrap/cache/` writable

## Ongoing Maintenance

### Daily

-   [ ] Monitor error logs
-   [ ] Check application uptime

### Weekly

-   [ ] Review error logs
-   [ ] Check disk space
-   [ ] Monitor database size

### Monthly

-   [ ] Update dependencies (security patches)
-   [ ] Review and optimize database
-   [ ] Check backup integrity
-   [ ] Review performance metrics

### As Needed

-   [ ] Deploy updates using `./deploy.sh`
-   [ ] Run new migrations
-   [ ] Update documentation

## Rollback Plan

If deployment fails:

1. Enable maintenance mode:

    ```bash
    php artisan down
    ```

2. Restore previous version:

    ```bash
    git checkout previous-working-commit
    ./deploy.sh
    ```

3. Restore database backup (if needed):

    ```bash
    psql -U username database_name < backup_file.sql
    ```

4. Disable maintenance mode:
    ```bash
    php artisan up
    ```

## Emergency Contacts

-   Jagoan Hosting Support: [Add contact info]
-   Database Admin: [Add contact info]
-   Development Team: [Add contact info]

## Notes

-   Always test deployment process on staging first
-   Keep backups before major updates
-   Document any custom configurations
-   Maintain changelog of deployments
