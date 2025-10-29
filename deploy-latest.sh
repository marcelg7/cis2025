#!/bin/bash

# Deploy Latest Changes to Production
# Usage: ./deploy-latest.sh

echo "================================================"
echo "  Deploying Latest Changes to Production"
echo "================================================"
echo ""

# Navigate to project directory
cd "$(dirname "$0")"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: Not in Laravel project directory"
    exit 1
fi

# Pull latest changes
echo "1. Pulling latest code from main..."
git pull origin main
if [ $? -ne 0 ]; then
    echo "Error: Failed to pull from main"
    exit 1
fi
echo "✓ Code updated"
echo ""

# Fetch tags
echo "2. Fetching version tags..."
git fetch --tags
if [ $? -ne 0 ]; then
    echo "Error: Failed to fetch tags"
    exit 1
fi
echo "✓ Tags fetched"
echo ""

# Run migrations
echo "3. Running database migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "Warning: Migrations failed or had issues"
fi
echo "✓ Migrations complete"
echo ""

# Cache version
echo "4. Caching version..."
php artisan version:cache
if [ $? -ne 0 ]; then
    echo "Error: Failed to cache version"
    exit 1
fi
CURRENT_VERSION=$(cat storage/framework/version.txt 2>/dev/null || echo "unknown")
echo "✓ Version cached: $CURRENT_VERSION"
echo ""

# Clear all caches
echo "5. Clearing application caches..."
php artisan optimize:clear
if [ $? -ne 0 ]; then
    echo "Error: Failed to clear caches"
    exit 1
fi
echo "✓ Caches cleared"
echo ""

# Rebuild config cache (critical for APP_KEY)
echo "6. Rebuilding config cache..."
php artisan config:cache
if [ $? -ne 0 ]; then
    echo "Error: Failed to cache config"
    exit 1
fi
echo "✓ Config cached"
echo ""

# Optional: Install/update Composer dependencies
# Uncomment if you update composer.json
# echo "7. Updating Composer dependencies..."
# composer install --no-dev --optimize-autoloader
# echo "✓ Composer updated"
# echo ""

# Optional: Build frontend assets
# Uncomment if you update frontend assets
# echo "8. Building frontend assets..."
# npm run build
# echo "✓ Assets built"
# echo ""

echo "================================================"
echo "  Deployment Complete!"
echo "================================================"
echo ""
echo "Current Version: $CURRENT_VERSION"
echo ""
echo "Next steps:"
echo "  - Verify the version in the footer of the website"
echo "  - Test critical functionality"
echo ""
