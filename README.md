# Hay Contract Information System (CIS)

A Laravel-based contract management system for cellular service agreements with WCOC (Wireless Code of Conduct) compliance. Generates PDF contracts for customers purchasing mobile devices and plans, integrating with Bell Mobility pricing and NISC billing system customer data.

## Features

### Contract Management
- **Create & Edit Contracts** - Full CRUD for cellular service contracts
- **Multi-Form Signing Workflow** - WCOC form, financing form, and DRO form with digital signatures
- **PDF Generation** - Automatic PDF generation with DomPDF and FPDI for merging multiple forms
- **Contract Finalization** - Complete workflow from draft to finalized with all required signatures
- **Version Control** - Track contract revisions and changes with activity logging
- **Contract Revision** - Create revisions of finalized contracts (resets signatures and vault status)

#### Vault Integration
- **FTP Upload to NISC Vault** - Automatic upload of finalized contracts to NISC iVue Vault
- **Standardized Filename Format** - `CustomerNumber-AccountNumber-CustomerName-SubscriberName-Phone-Contract-ContractID.pdf`
- **File Cleanup** - Automatic removal of signature files after vault upload for security
- **Upload Tracking** - Timeline tracking of vault upload status with error logging
- **Test Mode** - Configurable test mode for development environments
- **CSR Access** - CSRs can view uploaded contracts through NISC iVue Service software

### Customer Integration
- **NISC Billing System Integration** - Fetch customer data from external billing system API
- **Customer Hierarchy** - Customer → IvueAccount → MobilityAccount → Subscriber relationships
- **Search Functionality** - Global search across customers, contracts, devices, and plans

### Pricing Management
- **Bell Device Pricing** - Import and manage Bell Mobility device pricing from Excel
- **Rate Plans** - Manage cellular rate plans with promotional pricing and Hay Credit
- **Mobile Internet Plans** - Standalone internet plan management
- **Add-Ons** - Service add-ons (voicemail, data boosts, etc.)
- **Price Comparison** - Compare device pricing across tiers

### Financial Calculations
- **Device Financing** - Calculate device payments, credits, and buyout costs
- **Early Cancellation Fees** - Automatic ECF calculation based on contract terms
- **DRO (Device Return Option)** - Optional device return pricing and calculations
- **Hay Credit System** - Promotional credit tracking and application

### Administrative Features
- **User Management** - Role-based access control with Spatie Permission package
- **Activity Logging** - Comprehensive audit trail with Spatie ActivityLog
- **System Settings** - Configurable connection fees and system preferences
- **Terms of Service** - Version-controlled legal terms attached to contracts
- **Changelog** - GitHub integration for displaying application changes

### Security Features
- **Authentication** - Secure user authentication with password reset
- **Authorization** - Policy-based access control (ContractPolicy)
- **CSRF Protection** - Token validation on all forms
- **Rate Limiting** - Configurable throttling on sensitive operations
- **Security Headers** - Comprehensive headers (CSP, HSTS, X-Frame-Options, etc.)
- **Secure Sessions** - HTTPS-only cookies with encryption
- **Strong Passwords** - 12+ character minimum with complexity requirements
- **File Upload Security** - MIME type validation and cryptographically secure filenames
- **SQL Injection Prevention** - Parameter binding and Eloquent ORM throughout
- **XSS Protection** - HTMLPurifier sanitization for user-generated content

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade templates, Alpine.js, Tailwind CSS 3
- **Database:** MySQL (primary)
- **PDF Generation:** DomPDF with FPDI for merging
- **Queue System:** Database-backed queues
- **Permissions:** Spatie Laravel Permission
- **Activity Logging:** Spatie Laravel ActivityLog
- **Build Tools:** Vite 6, NPM
- **External APIs:** NISC billing system, GitHub API

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with mod_rewrite
- SSL/TLS certificate (production)

## Installation

### 1. Clone Repository
```bash
git clone https://github.com/marcelg7/cis2025.git
cd cis2025
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:
```env
APP_NAME="Hay Contract Information System WCOC"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cis4
DB_USERNAME=your_username
DB_PASSWORD=your_password

# NISC Billing System API
CUSTOMER_API_URL=your_api_url
CUSTOMER_API_TOKEN=your_api_token

# GitHub Integration (for changelog)
GITHUB_OWNER=your_github_username
GITHUB_REPO=your_repo_name
GITHUB_TOKEN=your_github_token

# Vault FTP (NISC iVue Vault integration - REQUIRED for production)
VAULT_FTP_HOST=your_ftp_host
VAULT_FTP_USERNAME=your_ftp_user
VAULT_FTP_PASSWORD=your_ftp_password
VAULT_FTP_PORT=21
VAULT_FTP_PATH=/Scan/           # IMPORTANT: Must be /Scan/ for NISC Vault integration
VAULT_FTP_PASSIVE=true
VAULT_FTP_SSL=false
VAULT_FTP_TEST_MODE=true        # Set to false in production
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
php artisan db:seed --class=PermissionSeeder
```

### 5. Build Assets
```bash
npm run build
npm run copy-tinymce
```

### 6. Storage Permissions
```bash
chmod -R 755 storage bootstrap/cache
```

## Development

### Start Development Environment
```bash
composer dev
# This runs: php artisan serve + queue:listen + pail + npm run dev
```

Or start services individually:
```bash
php artisan serve                    # Local development server
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0         # Log viewer
npm run dev                          # Vite dev server
```

### Common Commands
```bash
# Database
php artisan migrate
php artisan db:seed
php artisan activitylog:prune

# Data Import
php artisan bell:import-pricing
php artisan import:cellular-price-plans

# Testing
vendor/bin/phpunit
vendor/bin/phpunit --filter=ContractTest

# Code Quality
php artisan pint                # Format code
php artisan test                # Run tests

# Cache Management
php artisan optimize:clear      # Clear all caches
php artisan config:cache        # Cache configuration
php artisan route:cache         # Cache routes
php artisan view:cache          # Cache views
```

## Production Deployment

### 1. Server Requirements
- PHP 8.2+ with required extensions
- MySQL/MariaDB database
- Apache with mod_rewrite (or Nginx)
- SSL/TLS certificate installed
- Composer installed
- Node.js and NPM installed

### 2. Clone and Install
```bash
cd /var/www/your-domain
git clone https://github.com/marcelg7/cis2025.git .
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

**Critical `.env` settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-production-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Session & Security
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
```

### 4. File Permissions
```bash
chmod 644 .env
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Database Migration
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=PermissionSeeder --force
```

### 6. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Apache Configuration
The `.htaccess` file includes production environment enforcement:
```apache
SetEnv APP_ENV production
SetEnv APP_DEBUG false
```

### 8. Queue Worker Setup
Set up a supervisor or systemd service:
```ini
[program:cis-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-domain/artisan queue:listen --tries=1
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/your-domain/storage/logs/queue-worker.log
```

### 9. Log Rotation
Create `/etc/logrotate.d/laravel-cis`:
```
/var/www/your-domain/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    missingok
}
```

### 10. Verify Production Mode
```bash
php artisan about
# Should show:
# Environment: production
# Debug Mode: OFF
```

## Security Best Practices

### Application Security
- ✅ Debug mode disabled in production
- ✅ HTTPS enforced via HSTS headers
- ✅ CSRF protection on all forms
- ✅ Rate limiting on sensitive operations
- ✅ SQL injection prevention via Eloquent ORM
- ✅ XSS protection with HTMLPurifier
- ✅ Secure file uploads with validation
- ✅ Password hashing with bcrypt (12 rounds)
- ✅ Session encryption enabled

### Server Security
- Keep PHP and Laravel updated
- Use strong database passwords
- Restrict database user permissions
- Enable firewall (UFW/iptables)
- Regular security audits
- Monitor logs for suspicious activity
- Keep dependencies updated: `composer update`

### Rate Limits (Per User)
- Global authenticated routes: 60/minute
- Contract finalization: 30/minute
- Email operations: 15/minute
- FTP operations: 20/minute
- Password resets: 10/minute

## Architecture

### Key Models
- **Contract** - Central entity for cellular contracts
- **Customer/Subscriber** - Customer hierarchy from NISC
- **BellDevice/BellPricing** - Device catalog and pricing
- **RatePlan/MobileInternetPlan** - Service plans
- **PlanAddOn** - Service add-ons
- **CommitmentPeriod** - Contract terms
- **User** - System users with roles/permissions

### Services
- **ContractPdfService** - PDF generation and merging
- **VaultFtpService** - FTP uploads to Vault
- **ContractFileCleanupService** - Temporary file cleanup
- **ThemeService** - User theme preferences

### Policies
- **ContractPolicy** - Authorization for contract operations

### Middleware
- **Admin** - Admin-only access
- **CheckRole** - Role-based access
- **SecurityHeaders** - Security headers (CSP, HSTS, etc.)
- **TrackActiveUsers** - User activity tracking
- **CustomSessionLifetime** - Session management

## Contributing

This is a private application for Hay Communications. Contact the development team for contribution guidelines.

## License

Proprietary - All rights reserved by Hay Communications.

## Support

For support, contact the Hay Communications IT team.

## Changelog

View the [Changelog](/changelog) for recent updates and changes (accessible when logged into the application).
