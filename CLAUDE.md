# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About This Application

Hay CIS (Contract Information System) is a Laravel 12 application for managing cellular contracts with WCOC (Wireless Code of Conduct) compliance. The system generates PDF contracts for customers purchasing mobile devices and plans, integrating with Bell Mobility pricing and NISC billing system customer data via API.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade templates, Alpine.js, Tailwind CSS 3
- **Database**: MySQL (primary)
- **External Integration**: NISC billing system API for customer data
- **PDF Generation**: DomPDF with FPDI for merging
- **Queue System**: Database-backed queues
- **Permissions**: Spatie Laravel Permission package
- **Activity Logging**: Spatie Laravel ActivityLog
- **Build Tools**: Vite 6, NPM

## Development Commands

### Starting Development Environment
```bash
# Start all services (server, queue, logs, vite)
composer dev
# This runs: php artisan serve + queue:listen + pail + npm run dev

# Individual services
php artisan serve                    # Start local server
php artisan queue:listen --tries=1   # Start queue worker
php artisan pail --timeout=0         # Watch logs
npm run dev                          # Start Vite dev server
```

### Building Assets
```bash
npm run build        # Build for production (includes TinyMCE assets)
npm run dev          # Development build
npm run copy-tinymce # Copy TinyMCE assets to public/
```

### Database Operations
```bash
php artisan migrate                          # Run migrations
php artisan db:seed                          # Seed database
php artisan db:seed --class=PermissionSeeder # Seed specific seeder
```

### Testing
```bash
vendor/bin/phpunit                    # Run all tests
vendor/bin/phpunit tests/Feature      # Run feature tests
vendor/bin/phpunit tests/Unit         # Run unit tests
```

### Code Quality
```bash
php artisan pint           # Format code (Laravel Pint)
php artisan test           # Run tests
```

### Custom Console Commands
```bash
php artisan import:bell-pricing              # Import Bell device pricing from Excel
php artisan import:cellular-price-plans      # Import rate plans and add-ons
php artisan activitylog:prune                # Prune old activity logs
php artisan clear:test-data                  # Clear test/demo contracts
```

### Cache and Optimization
```bash
php artisan config:cache    # Cache configuration
php artisan route:cache     # Cache routes
php artisan view:cache      # Cache views
php artisan optimize:clear  # Clear all caches
```

## Application Architecture

### Core Domain Models

The application centers around cellular contracts with these key models:

- **Contract**: Central entity containing all contract data (device, plan, pricing, signatures)
- **Customer/Subscriber/MobilityAccount/IvueAccount**: Customer hierarchy retrieved from NISC billing system API
- **RatePlan/MobileInternetPlan**: Cellular service plans
- **BellDevice/BellPricing**: Bell Mobility device catalog and pricing
- **PlanAddOn**: Service add-ons (voicemail, data boosts, etc.)
- **CommitmentPeriod**: Contract terms (24-month, financing options)
- **TermsOfService**: Legal terms attached to contracts
- **ActivityType**: Contract types (New Activation, Upgrade, etc.)
- **User**: System users with roles and permissions
- **Setting**: Application-wide configuration

### Service Layer

Located in `app/Services/`:

- **ContractPdfService**: Generates and merges contract PDFs (WCOC compliance forms, financing forms, DRO forms)
- **VaultFtpService**: Uploads signed contracts to Vault FTP server
- **ContractFileCleanupService**: Cleans up temporary contract files
- **ThemeService**: Manages user theme preferences (button/link colors)

### Controllers

Main controllers in `app/Http/Controllers/`:

- **ContractController**: CRUD for contracts, PDF generation, signing workflows
- **CellularPricingController**: Manage rate plans and add-ons
- **BellPricingController**: Manage Bell device pricing
- **CustomerController**: View/search customers from NISC billing system
- **SearchController**: Global search across contracts and customers
- **AdminController**: Admin settings and system configuration
- **UserController/RoleController/PermissionController**: User management with Spatie permissions

### NISC Billing System Integration

The app retrieves customer data from the NISC billing system via API:
- Customer data is fetched via API calls to the NISC billing system
- Customer hierarchy: `Customer` → `IvueAccount` → `MobilityAccount` → `Subscriber`
- Models: `Customer`, `Subscriber`, `MobilityAccount`, `IvueAccount`
- API integration handled through controllers (CustomerController, SearchController)

### PDF Generation Workflow

1. Contract data is rendered into Blade views (`contracts/pdf-view.blade.php`, `contracts/dro.blade.php`, etc.)
2. DomPDF converts HTML to PDF
3. FPDI merges multiple PDFs (WCOC form, financing form, DRO form, terms of service)
4. PDFs stored temporarily in `storage/app/contracts/`
5. After signing, uploaded to Vault FTP and cleaned up

### Permissions System

Uses Spatie Laravel Permission with roles and granular permissions:
- Permissions control access to sensitive areas (admin settings, user management, etc.)
- Check permissions in controllers: `$user->can('permission-name')`
- Middleware: `CheckRole`, `Admin`
- Seeded via `PermissionSeeder`

### Queue System

Database-backed queue for background jobs:
- Queue table: `jobs`
- Used for contract file cleanup, FTP uploads
- Worker: `php artisan queue:listen --tries=1`

### Activity Logging

Uses Spatie ActivityLog to track:
- Contract changes and status updates
- User actions
- Model: `App\Models\Contract` has `LogsActivity` trait
- Pruning: `php artisan activitylog:prune`

## Important Application Details

### Contract Financial Calculations

Contracts contain complex financial logic for device financing:
- **Device Amount**: Retail price minus agreement credit
- **Total Financed Amount**: Device amount minus upfront/down payments
- **Deferred Payment**: Optional first payment
- **Remaining Balance**: Total financed minus deferred payment
- **Monthly Device Payment**: Remaining balance / 24 months
- **Early Cancellation Fee**: Total financed + DRO amount
- **Buyout Cost**: (Retail price - deferred payment) / 24

These calculations are in `ContractPdfService::generateMergedPdfContent()` and contract views.

### Hay Credit System

Rate plans can include "Hay Credit" - a promotional discount:
- `hay_credit_amount`: Credit value
- `hay_credit_applicable_for`: Duration in months
- `hay_credit_when_applicable`: Conditional text
- Credits shown in contract UI with savings indicators
- Can be applied once per contract

### Device Pricing Systems

Two pricing systems:
1. **Bell Pricing**: Imported via `import:bell-pricing` command from Excel
2. **Generic Cellular Pricing**: Managed via CellularPricingController

### Contract Status Flow

Contracts progress through statuses:
- `draft` → `pending` → `signed` → `completed`
- Status tracked in `contracts.status` column

### Signature Workflows

Multiple signature flows:
1. **Customer signs WCOC** (`/contracts/{id}/sign`)
2. **Customer signs financing** (`/contracts/{id}/sign-financing`)
3. **Customer signs DRO** (`/contracts/{id}/sign-dro`)
4. **CSR signs** (`/contracts/{id}/sign-financing-csr`, `/contracts/{id}/sign-dro-csr`)

Signatures stored as base64 images in contract fields.

### Theme System

Users can customize UI theme colors:
- Theme preferences stored in `users.theme` JSON column
- Managed via `ThemeService`
- Configurable: button color, link color
- Applied globally via Blade components

### Admin Settings

System-wide settings in `settings` table (key-value):
- `connection_fee`: Default connection fee
- `show_development_info`: Toggle debug information
- `terms_of_service_id`: Active terms of service
- Managed via `Setting` model and `SettingsController`

## File Structure

```
app/
├── Console/Commands/     # Artisan commands for imports, cleanup
├── Helpers/              # Helper functions
├── Http/
│   ├── Controllers/      # Main application logic
│   ├── Middleware/       # Auth, role checking, session
│   └── Requests/         # Form request validation
├── Mail/                 # Email notifications
├── Models/               # Eloquent models
├── Notifications/        # User notifications
├── Providers/            # Service providers
├── Services/             # Business logic services
└── View/                 # View composers

config/
├── permission.php        # Spatie permission config
├── activitylog.php       # Activity logging config
└── dompdf.php            # PDF generation config

database/
├── migrations/           # Database schema
└── seeders/              # Database seeders

resources/
├── views/
│   ├── contracts/        # Contract views and PDFs
│   ├── components/       # Reusable Blade components
│   ├── layouts/          # Layout templates
│   └── ...               # Feature-specific views
├── css/
│   ├── app.css           # Main styles
│   └── app2.css          # Additional styles
└── js/
    └── app.js            # Alpine.js, Axios

routes/
├── web.php               # Web routes
├── auth.php              # Authentication routes
└── console.php           # Console routes

public/
└── tinymce/              # TinyMCE assets (copied via npm)

storage/
└── app/
    └── contracts/        # Temporary PDF storage
```

## Development Conventions

### Model Relationships

- Use eager loading to avoid N+1 queries: `$contract->load(['subscriber.mobilityAccount.ivueAccount.customer', 'ratePlan', ...])`
- Customer data from NISC is read-only (fetched via API)
- Contracts have many add-ons (`contractAddOns`) and one-time fees (`contractOneTimeFees`)

### Blade Components

Reusable components in `resources/views/components/`:
- `contract-card.blade.php`: Display contract summary
- `primary-button`, `secondary-button`, `danger-button`: Themed buttons
- `primary-link`, `secondary-link`: Themed links
- All buttons/links respect user theme preferences

### Form Validation

- Use Form Requests for complex validation (`app/Http/Requests/`)
- Simple validation in controllers

### Asset Building

- TinyMCE requires special handling: `npm run copy-tinymce` copies assets to `public/tinymce/`
- Always run `npm run build` before deployment
- Vite configured for Tailwind CSS processing

### Testing

- Feature tests for API/controller logic
- Unit tests for services and models
- Use in-memory SQLite for tests (configured in `phpunit.xml`)

## Database Connections

The application uses multiple database connections:

1. **mysql** (default): Primary application database
2. **sqlite**: Testing database (in-memory)

Configure database connection in `.env`:
```
DB_CONNECTION=mysql
DB_DATABASE=cis4
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=...
DB_PASSWORD=...
```

Customer data is retrieved via API calls to the NISC billing system (not stored locally).

## Important Files to Review

When working on contracts:
- `app/Http/Controllers/ContractController.php`: Main contract logic
- `app/Services/ContractPdfService.php`: PDF generation
- `resources/views/contracts/create.blade.php`: Contract creation form
- `resources/views/contracts/pdf-view.blade.php`: PDF template

When working on pricing:
- `app/Console/Commands/ImportBellPricing.php`: Bell pricing import
- `app/Console/Commands/ImportCellularPricePlans.php`: Plan import
- `app/Http/Controllers/CellularPricingController.php`: Plan management

When working on permissions:
- `config/permission.php`: Configuration
- `database/seeders/PermissionSeeder.php`: Seeded permissions
- `app/Http/Middleware/Admin.php`: Admin middleware

## Common Pitfalls

1. **TinyMCE assets**: Always run `npm run copy-tinymce` when updating TinyMCE or deploying
2. **Queue worker**: Background jobs won't run without `php artisan queue:listen`
3. **NISC API connection**: Ensure NISC billing system API credentials and endpoints are correctly configured
4. **PDF generation**: Requires write permissions on `storage/app/contracts/`
5. **FTP uploads**: Vault FTP credentials must be configured in `.env`
6. **Permissions cache**: Clear permission cache after seeding: `php artisan permission:cache-reset`
