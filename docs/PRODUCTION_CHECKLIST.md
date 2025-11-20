# Production Deployment Checklist

## Pre-Deployment

### Configuration
- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database credentials
- [ ] Set secure `APP_KEY` and `JWT_SECRET`
- [ ] Configure OAuth providers (if using SSO)
- [ ] Set up email configuration (SMTP)
- [ ] Configure payment gateways (Stripe, Square)

### Security
- [ ] Review and update `.gitignore`
- [ ] Ensure no sensitive data in repository
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Make storage/ and public/uploads/ writable (775)
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up fail2ban or similar
- [ ] Review security headers

### Database
- [ ] Run all migrations in order
- [ ] Verify all tables created successfully
- [ ] Set up database backups
- [ ] Configure database user with minimal permissions
- [ ] Test database connection

### Performance
- [ ] Enable OPcache for PHP
- [ ] Configure Redis/Memcached (if available)
- [ ] Set up CDN for static assets
- [ ] Enable gzip compression
- [ ] Optimize images

### Monitoring
- [ ] Set up error logging
- [ ] Configure application monitoring
- [ ] Set up uptime monitoring
- [ ] Configure backup monitoring
- [ ] Set up alerts for critical errors

## Post-Deployment

### Testing
- [ ] Test user login
- [ ] Test SSO login (if configured)
- [ ] Test POS functionality
- [ ] Test customer management
- [ ] Test product management
- [ ] Test reporting
- [ ] Test mobile responsiveness
- [ ] Test PWA installation

### Documentation
- [ ] Update README with production URL
- [ ] Document deployment process
- [ ] Create admin user guide
- [ ] Create backup/restore procedures

### Maintenance
- [ ] Schedule regular backups
- [ ] Plan for updates and patches
- [ ] Set up monitoring dashboards
- [ ] Create incident response plan

## Verification

- [ ] All migrations run successfully
- [ ] No PHP errors in logs
- [ ] All features working as expected
- [ ] Performance is acceptable
- [ ] Security scan passed
- [ ] Backup system tested
- [ ] SSL certificate valid
- [ ] DNS configured correctly

## Sign-off

- [ ] Development team approval
- [ ] QA team approval
- [ ] Security team approval
- [ ] Management approval

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Version:** 1.1.0
