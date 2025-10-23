# Production Deployment Guide
**Application:** Hay Contract Information System (CIS)
**Target Server:** AlmaLinux 10.0 (Purple Lion)
**PHP Version:** 8.3.19
**Production URL:** https://cis-prod.hay.net

---

## Prerequisites Verification

Your server already has these installed:
- ✅ Apache 2.4.63
- ✅ MariaDB 10.11.11
- ✅ PHP 8.3.19
- ✅ PHP Extensions: cli, common, fpm, gd, intl, mbstring, mysqlnd, opcache, pdo, pecl-zip, process, xml

### Additional PHP Extensions Needed

Install these required Laravel extensions:

```bash
# Install required PHP extensions
sudo dnf install -y \
    php-bcmath \
    php-curl \
    php-fileinfo \
    php-json \
    php-tokenizer

# Verify PHP version and extensions
php -v
php -m | grep -E "bcmath|curl|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml"
```

### Install Composer

```bash
# Download and install Composer
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify
composer --version
```

### Install Node.js and NPM

```bash
# Install Node.js 20 LTS
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo dnf install -y nodejs

# Verify
node --version
npm --version
```

---

## Step 1: Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/mg_apps
cd /var/www/mg_apps

# Clone repository
sudo git clone https://github.com/marcelg7/cis2025.git cis4

# Set ownership
sudo chown -R apache:apache /var/www/mg_apps/cis4
```

---

## Step 2: Install Dependencies

```bash
cd /var/www/mg_apps/cis4

# Install PHP dependencies (production, optimized)
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install

# Build frontend assets
npm run build
npm run copy-tinymce
```

---

## Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Edit `.env` File

```bash
nano .env
```

**Critical Settings:**

```env
# Application
APP_NAME="Hay Contract Information System WCOC"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cis-prod.hay.net

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cis4_production
DB_USERNAME=cis4_user
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE

# Session & Security
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

# NISC Billing System API
CUSTOMER_API_URL=your_api_url
CUSTOMER_API_TOKEN=your_api_token

# GitHub Integration (for changelog)
GITHUB_OWNER=marcelg7
GITHUB_REPO=cis2025
GITHUB_TOKEN=your_github_token

# Vault FTP (optional - for contract storage)
VAULT_FTP_HOST=your_ftp_host
VAULT_FTP_USERNAME=your_ftp_user
VAULT_FTP_PASSWORD=your_ftp_password
VAULT_FTP_PORT=21
VAULT_FTP_ROOT=/
VAULT_FTP_TEST_MODE=false

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info
```

### Secure `.env` File Permissions

```bash
# Set correct permissions (apache group can read, owner can write)
sudo chown marcelg:apache .env
sudo chmod 640 .env

# Verify
ls -la .env
# Should show: -rw-r----- 1 marcelg apache
```

---

## Step 4: Database Setup

```bash
# Create database and user
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE cis4_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with strong password
CREATE USER 'cis4_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON cis4_production.* TO 'cis4_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Run Migrations

```bash
cd /var/www/mg_apps/cis4

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force
php artisan db:seed --class=PermissionSeeder --force
```

---

## Step 5: File Permissions

```bash
cd /var/www/mg_apps/cis4

# Set ownership
sudo chown -R marcelg:apache .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Storage and cache need write access for Apache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R apache:apache storage bootstrap/cache

# Verify .env permissions (critical!)
sudo chmod 640 .env
sudo chown marcelg:apache .env
```

---

## Step 6: Apache Configuration

### Create Virtual Host Configuration

```bash
sudo nano /etc/httpd/conf.d/cis-prod.conf
```

**Virtual Host Configuration:**

```apache
<VirtualHost *:443>
    ServerName cis-prod.hay.net
    DocumentRoot /var/www/mg_apps/cis4/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLCertificateChainFile /path/to/your/chain.crt

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # Environment Variables (CRITICAL for production)
    SetEnv APP_ENV production
    SetEnv APP_DEBUG false

    <Directory /var/www/mg_apps/cis4/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Enable .htaccess
        <IfModule mod_rewrite.c>
            RewriteEngine On
        </IfModule>
    </Directory>

    # Log files
    ErrorLog /var/log/httpd/cis-prod-error.log
    CustomLog /var/log/httpd/cis-prod-access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName cis-prod.hay.net
    Redirect permanent / https://cis-prod.hay.net/
</VirtualHost>
```

### Enable Required Apache Modules

```bash
# Enable mod_rewrite (should already be enabled)
sudo systemctl restart httpd
```

### Test Apache Configuration

```bash
# Test configuration syntax
sudo httpd -t

# If "Syntax OK", restart Apache
sudo systemctl restart httpd
sudo systemctl enable httpd
```

---

## Step 7: Optimize for Production

```bash
cd /var/www/mg_apps/cis4

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## Step 8: Queue Worker Setup (Important!)

The application uses queues for background jobs. Set up a systemd service:

```bash
sudo nano /etc/systemd/system/cis-queue-worker.service
```

**Service Configuration:**

```ini
[Unit]
Description=CIS Queue Worker
After=network.target mariadb.service

[Service]
Type=simple
User=apache
WorkingDirectory=/var/www/mg_apps/cis4
ExecStart=/usr/bin/php /var/www/mg_apps/cis4/artisan queue:listen --tries=1
Restart=always
RestartSec=3

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=cis-queue

[Install]
WantedBy=multi-user.target
```

**Enable and Start:**

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service
sudo systemctl enable cis-queue-worker

# Start service
sudo systemctl start cis-queue-worker

# Check status
sudo systemctl status cis-queue-worker
```

---

## Step 9: Security Monitoring Setup

Set up hourly security monitoring:

```bash
# Edit crontab for apache user
sudo crontab -u apache -e
```

**Add this line:**

```cron
# Security monitoring - runs every hour
0 * * * * cd /var/www/mg_apps/cis4 && /usr/bin/php artisan security:monitor --email >> /dev/null 2>&1
```

---

## Step 10: SELinux Configuration (AlmaLinux)

AlmaLinux has SELinux enabled by default. Configure it:

```bash
# Set SELinux context for web files
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mg_apps/cis4/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/mg_apps/cis4/bootstrap/cache(/.*)?"

# Apply context
sudo restorecon -Rv /var/www/mg_apps/cis4

# Allow Apache to connect to network (for API calls)
sudo setsebool -P httpd_can_network_connect 1

# Allow Apache to send email
sudo setsebool -P httpd_can_sendmail 1
```

---

## Step 11: Firewall Configuration

```bash
# Allow HTTPS
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=http

# Reload firewall
sudo firewall-cmd --reload

# Verify
sudo firewall-cmd --list-all
```

---

## Step 12: Create First Admin User

```bash
cd /var/www/mg_apps/cis4

# Use tinker to create admin user
php artisan tinker
```

```php
// In tinker:
$user = new App\Models\User();
$user->name = 'Your Name';
$user->email = 'your.email@hay.net';
$user->password = Hash::make('YourSecurePassword123!@#');
$user->save();

// Assign admin role
$user->assignRole('admin');

exit
```

---

## Step 13: Verification

### Test Configuration

```bash
# Verify environment
php artisan about

# Should show:
# Environment: production
# Debug Mode: DISABLED
```

### Test Web Access

1. Visit: https://cis-prod.hay.net
2. Login with admin credentials
3. Test creating a contract
4. Check that all features work

### Check Logs

```bash
# Application logs
tail -f /var/www/mg_apps/cis4/storage/logs/laravel.log

# Security logs
tail -f /var/www/mg_apps/cis4/storage/logs/security.log

# Apache logs
sudo tail -f /var/log/httpd/cis-prod-error.log
```

---

## PHP 8.3 Compatibility Notes

Your server runs PHP 8.3, the app was developed on 8.2. These should be compatible, but watch for:

### Potential Issues

1. **Deprecated Features:** PHP 8.3 may show deprecation warnings
   - Set `APP_DEBUG=false` to hide warnings in production
   - Monitor logs for deprecation notices

2. **Minor Behavior Changes:**
   - Most Laravel 12 features are PHP 8.3 compatible
   - If you see errors, check Laravel compatibility

### If Issues Arise

```bash
# Option 1: Downgrade to PHP 8.2 (if needed)
sudo dnf module reset php
sudo dnf module enable php:8.2
sudo dnf install php php-cli php-fpm php-common ...

# Option 2: Update composer.json PHP requirement
# Edit composer.json and change "php": "^8.2" to "php": "^8.2|^8.3"
```

---

## Post-Deployment Checklist

- [ ] `.env` configured with production values
- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] SSL certificate installed and working
- [ ] Database created and migrated
- [ ] File permissions set correctly (775 for storage/cache)
- [ ] `.env` permissions: 640, group apache
- [ ] Apache virtual host configured
- [ ] Queue worker service running
- [ ] Security monitoring cron job active
- [ ] SELinux contexts applied
- [ ] Firewall rules configured
- [ ] Admin user created
- [ ] Site accessible via HTTPS
- [ ] All caches cleared and regenerated
- [ ] Logs being written correctly

---

## Maintenance Commands

```bash
# Clear all caches (after config changes)
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update application (after git pull)
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart cis-queue-worker

# View queue worker status
sudo systemctl status cis-queue-worker

# Restart queue worker (after code changes)
sudo systemctl restart cis-queue-worker
```

---

## Troubleshooting

### 500 Internal Server Error

```bash
# Check Apache error log
sudo tail -50 /var/log/httpd/cis-prod-error.log

# Check Laravel log
tail -50 /var/www/mg_apps/cis4/storage/logs/laravel.log

# Common causes:
# 1. Wrong permissions on storage/bootstrap/cache
# 2. .env file not readable by Apache (should be 640, group apache)
# 3. APP_KEY not set
# 4. Database connection failed
# 5. SELinux blocking access
```

### Permission Denied Errors

```bash
# Fix storage permissions
cd /var/www/mg_apps/cis4
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R apache:apache storage bootstrap/cache

# Fix SELinux if needed
sudo restorecon -Rv /var/www/mg_apps/cis4
```

### Queue Jobs Not Processing

```bash
# Check queue worker status
sudo systemctl status cis-queue-worker

# View queue worker logs
sudo journalctl -u cis-queue-worker -f

# Restart worker
sudo systemctl restart cis-queue-worker
```

---

## Security Reminders

1. **Never commit `.env` to git** ✅ Already in `.gitignore`
2. **Use strong database password** - Generate with: `openssl rand -base64 32`
3. **Keep APP_DEBUG=false in production** - Set in both `.env` and Apache config
4. **Monitor security logs daily** - Check `/var/www/mg_apps/cis4/storage/logs/security.log`
5. **Keep dependencies updated** - Run `composer update` monthly
6. **Backup database regularly** - Set up automated mysqldump cron job

---

## Support

For issues, check:
1. `/var/www/mg_apps/cis4/storage/logs/laravel.log`
2. `/var/www/mg_apps/cis4/storage/logs/security.log`
3. `/var/log/httpd/cis-prod-error.log`
4. `sudo systemctl status cis-queue-worker`

Good luck with your production deployment!



-----------------
> Usually I make a second user named marcelg.   What should the permissions on /var/www and subfolders be? 

● Great question! Here's the proper permission structure for a production web server with a marcelg user:

  📁 Directory Permission Structure

  Root Web Directory

  # /var/www
  # Owner: root, Group: root
  # Permissions: 755 (rwxr-xr-x)
  sudo chown root:root /var/www
  sudo chmod 755 /var/www

  Applications Directory

  # /var/www/mg_apps
  # Owner: marcelg, Group: apache
  # Permissions: 755 (rwxr-xr-x)
  sudo mkdir -p /var/www/mg_apps
  sudo chown marcelg:apache /var/www/mg_apps
  sudo chmod 755 /var/www/mg_apps

  Application Directory (CIS4)

  # /var/www/mg_apps/cis4
  # Owner: marcelg, Group: apache
  # Directories: 755, Files: 644
  sudo chown -R marcelg:apache /var/www/mg_apps/cis4

  # Set directory permissions
  sudo find /var/www/mg_apps/cis4 -type d -exec chmod 755 {} \;

  # Set file permissions
  sudo find /var/www/mg_apps/cis4 -type f -exec chmod 644 {} \;

  Special Directories (Storage & Cache)

  # storage/ and bootstrap/cache/ - NEED WRITE ACCESS
  # Owner: apache, Group: apache
  # Permissions: 775 (rwxrwxr-x)
  sudo chown -R apache:apache /var/www/mg_apps/cis4/storage
  sudo chown -R apache:apache /var/www/mg_apps/cis4/bootstrap/cache
  sudo chmod -R 775 /var/www/mg_apps/cis4/storage
  sudo chmod -R 775 /var/www/mg_apps/cis4/bootstrap/cache

  Special Files

  # .env file - CRITICAL!
  # Owner: marcelg, Group: apache
  # Permissions: 640 (rw-r-----)
  sudo chown marcelg:apache /var/www/mg_apps/cis4/.env
  sudo chmod 640 /var/www/mg_apps/cis4/.env

  # artisan command - should be executable
  sudo chmod 755 /var/www/mg_apps/cis4/artisan

  Default Apache Document Root (if used)

  # /var/www/html (usually not used with Laravel)
  sudo chown apache:apache /var/www/html
  sudo chmod 755 /var/www/html

  ---
  🔐 Security Model Explained

  | Directory/File                | Owner   | Group  | Permissions | Why                                           |
  |-------------------------------|---------|--------|-------------|-----------------------------------------------|
  | /var/www                      | root    | root   | 755         | Root web directory - system managed           |
  | /var/www/mg_apps              | marcelg | apache | 755         | Developer owns, Apache can read               |
  | /var/www/mg_apps/cis4 (dirs)  | marcelg | apache | 755         | Developer owns, Apache can read               |
  | /var/www/mg_apps/cis4 (files) | marcelg | apache | 644         | Developer owns, Apache can read               |
  | storage/                      | apache  | apache | 775         | Apache needs to write logs, cache, uploads    |
  | bootstrap/cache/              | apache  | apache | 775         | Apache needs to write compiled files          |
  | .env                          | marcelg | apache | 640         | Developer owns, Apache can read, others can't |

  ---
  ✅ Complete Setup Commands

  Here's the complete sequence to run after cloning:

  # 1. Set base ownership
  sudo chown -R marcelg:apache /var/www/mg_apps/cis4

  # 2. Set directory permissions (755)
  sudo find /var/www/mg_apps/cis4 -type d -exec chmod 755 {} \;

  # 3. Set file permissions (644)
  sudo find /var/www/mg_apps/cis4 -type f -exec chmod 644 {} \;

  # 4. Make artisan executable
  sudo chmod 755 /var/www/mg_apps/cis4/artisan

  # 5. Give Apache write access to storage and cache
  sudo chown -R apache:apache /var/www/mg_apps/cis4/storage
  sudo chown -R apache:apache /var/www/mg_apps/cis4/bootstrap/cache
  sudo chmod -R 775 /var/www/mg_apps/cis4/storage
  sudo chmod -R 775 /var/www/mg_apps/cis4/bootstrap/cache

  # 6. Secure .env file (AFTER creating it)
  sudo chown marcelg:apache /var/www/mg_apps/cis4/.env
  sudo chmod 640 /var/www/mg_apps/cis4/.env

  ---
  🔍 Verification Commands

  # Check /var/www structure
  ls -la /var/www/
  # Should show: drwxr-xr-x root root for /var/www

  # Check mg_apps
  ls -la /var/www/mg_apps/
  # Should show: drwxr-xr-x marcelg apache for cis4/

  # Check storage
  ls -la /var/www/mg_apps/cis4/
  # Should show: drwxrwxr-x apache apache for storage/

  # Check .env
  ls -la /var/www/mg_apps/cis4/.env
  # Should show: -rw-r----- marcelg apache

  ---
  💡 Why This Structure?

  1. marcelg owns application files
    - Can edit files, run git pull, composer update
    - Maintains files via SSH/terminal
  2. apache group has read access
    - Web server can read PHP files
    - Can execute application code
  3. apache owns storage/cache
    - Web server writes logs, sessions, cache
    - Prevents permission errors when writing
  4. .env is group-readable only
    - Apache can read database credentials
    - Other users can't read secrets
    - Not world-readable (security)

  ---
  ⚠️ Common Mistakes to Avoid

  ❌ Don't do this:
  # DON'T make everything 777!
  sudo chmod -R 777 /var/www/mg_apps/cis4  # INSECURE!

  # DON'T set .env to 600
  sudo chmod 600 .env  # Apache can't read = site crash

  # DON'T leave storage owned by marcelg
  # Apache won't be able to write = errors

  ✅ Do this instead: Use the commands above!

  ---
  Ready to proceed with the installation? Let me know when you've set up the directory structure!
