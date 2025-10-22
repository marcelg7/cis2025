# Security Maintenance Guide
**Application:** Hay Contract Information System (CIS)
**Last Updated:** October 22, 2025

---

## Security Audit Schedule

### Quarterly Security Audits

Security audits should be conducted **every 3 months** to ensure ongoing protection against vulnerabilities.

**Scheduled Audit Dates (2025-2026):**
- ✅ October 22, 2025 (Completed)
- ⏰ January 22, 2026
- ⏰ April 22, 2026
- ⏰ July 22, 2026
- ⏰ October 22, 2026

### Audit Checklist

Each audit should cover:

1. **Authentication & Authorization**
   - [ ] Verify all routes require proper authentication
   - [ ] Test policy enforcement (ContractPolicy, etc.)
   - [ ] Check password requirements are still enforced
   - [ ] Verify admin-only routes are protected

2. **Input Validation & XSS**
   - [ ] Review all form inputs for proper validation
   - [ ] Check XSS protection on all user-generated content
   - [ ] Verify markdown/HTML sanitization is working
   - [ ] Test file upload validation

3. **Dependencies**
   - [ ] Run `composer audit` for PHP vulnerabilities
   - [ ] Run `npm audit` for JavaScript vulnerabilities
   - [ ] Update all dependencies to latest secure versions

4. **Configuration**
   - [ ] Verify production environment (`APP_ENV=production`)
   - [ ] Verify debug mode is disabled (`APP_DEBUG=false`)
   - [ ] Check `.env` file permissions (should be `600`)
   - [ ] Verify security headers are active

5. **Logs & Monitoring**
   - [ ] Review security logs for suspicious patterns
   - [ ] Check failed login attempts
   - [ ] Review authorization failures
   - [ ] Verify monitoring is running

6. **Rate Limiting**
   - [ ] Test rate limits are working
   - [ ] Review and adjust limits if needed
   - [ ] Check for rate limit bypass attempts

---

## Security Monitoring

### Daily Monitoring (Automated)

Run the security monitoring command **every hour** via cron:

```bash
# Add to crontab
0 * * * * cd /var/www/mg_apps/cis4 && php artisan security:monitor --email >> /dev/null 2>&1
```

This will:
- Analyze security logs for suspicious patterns
- Detect failed login attempts
- Identify authorization failures
- Alert on rate limit violations
- Send email alerts if issues are found

### Manual Security Log Review

Review security logs weekly:

```bash
# View recent security events
tail -100 storage/logs/security.log

# Search for specific events
grep "Failed login" storage/logs/security.log
grep "Authorization failure" storage/logs/security.log
grep "Rate limit exceeded" storage/logs/security.log
```

### Alert Thresholds

The monitoring system will alert on:
- **Failed logins:** ≥10 in one hour
- **Repeated failed logins from same IP:** ≥5 attempts
- **Authorization failures:** ≥20 in one hour
- **User authorization failures:** ≥10 for single user (privilege escalation)
- **Rate limit violations:** ≥10 in one hour
- **Any privilege escalation attempts** (critical)

---

## Security Event Logging

### Logged Events

The application automatically logs:

1. **Authentication Events**
   - Failed login attempts (email, IP, user agent)
   - Successful logins (user ID, email, IP)
   - Password changes
   - Account lockouts

2. **Authorization Events**
   - 403 Forbidden responses
   - Policy authorization failures
   - Privilege escalation attempts

3. **Rate Limiting**
   - 429 Too Many Requests responses
   - Route and user information

4. **File Operations**
   - File upload attempts
   - MIME type mismatches
   - Upload success/failure

### Security Log Location

**Path:** `storage/logs/security.log`

**Retention:** 90 days (automatically rotated)

**Format:** Daily rotating log files

---

## Incident Response

### If Security Issues Are Detected

1. **Immediate Actions**
   - Review the security log entry details
   - Identify the affected user/IP
   - Check if it's a false positive or genuine threat

2. **For Failed Login Attempts**
   - Check if from known user (forgot password) or attack
   - Consider IP blocking if ≥20 failed attempts
   - Reset user password if account may be compromised

3. **For Authorization Failures**
   - Review what the user was trying to access
   - Check if user role/permissions are correct
   - Investigate if it's a privilege escalation attempt

4. **For Rate Limit Violations**
   - Determine if legitimate user or attack
   - Temporarily block IP if DoS attempt suspected
   - Adjust rate limits if legitimate usage pattern

5. **For Privilege Escalation**
   - **CRITICAL:** Lock the account immediately
   - Review all recent activity from that user
   - Investigate how they attempted escalation
   - Report to security team/management

### Contact Information

**Cellular Supervisor Email:** Configured in Admin Settings (`cellular_supervisor_email`)

**Security Alerts Sent To:** Cellular Supervisor (automated)

---

## Maintenance Tasks

### Weekly Tasks
- [ ] Review security log summary
- [ ] Check for unusual patterns
- [ ] Verify monitoring is running

### Monthly Tasks
- [ ] Review all security events
- [ ] Update dependency packages
- [ ] Review and update user permissions
- [ ] Test backup restoration

### Quarterly Tasks (See Audit Schedule Above)
- [ ] Full security audit
- [ ] Penetration testing (optional)
- [ ] Review and update security policies
- [ ] Update security documentation

---

## Security Best Practices

### For Administrators

1. **Never disable security features** in production
2. **Always use HTTPS** - never run production over HTTP
3. **Keep dependencies updated** - run updates monthly
4. **Review logs regularly** - don't wait for alerts
5. **Use strong passwords** - minimum 12 characters, complexity required
6. **Enable 2FA** if available (recommended for admin accounts)
7. **Limit admin access** - only grant to trusted personnel

### For Developers

1. **Never commit `.env` files** to version control
2. **Always validate user input** before processing
3. **Use `authorize()` checks** on all sensitive operations
4. **Escape output** - use `{{ }}` in Blade, not `{!! !!}` unless sanitized
5. **Never store secrets** in code - use environment variables
6. **Test security** before deploying changes
7. **Follow Laravel security best practices**

---

## Commands Reference

### Security Monitoring
```bash
# Check security logs (no email)
php artisan security:monitor

# Check and send email alert if issues found
php artisan security:monitor --email
```

### Manual Dependency Audit
```bash
# Check PHP dependencies
composer audit

# Check JavaScript dependencies
npm audit
```

### Log Management
```bash
# Clear old activity logs (keep last 90 days)
php artisan activitylog:prune

# View security log
tail -f storage/logs/security.log

# Search security log
grep "pattern" storage/logs/security.log
```

### Cache Management
```bash
# Clear all caches
php artisan optimize:clear

# Recache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Version History

| Date | Version | Changes |
|------|---------|---------|
| 2025-10-22 | 1.0 | Initial security maintenance guide created |
| 2026-01-22 | - | Next scheduled audit |

---

## Additional Resources

- **Laravel Security Documentation:** https://laravel.com/docs/security
- **OWASP Top 10:** https://owasp.org/www-project-top-ten/
- **Security Audit Report:** See `SECURITY_AUDIT_2025-10-22.md`

---

**For questions or security concerns, contact the Hay Communications IT team.**
