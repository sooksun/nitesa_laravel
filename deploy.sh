#!/bin/bash

# NITESA Production Deployment Script
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting NITESA deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}❌ Please do not run as root${NC}"
   exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    exit 1
fi

# Check if in production mode
if grep -q "APP_ENV=production" .env; then
    echo -e "${GREEN}✓ Production environment detected${NC}"
else
    echo -e "${YELLOW}⚠ Warning: Not in production mode${NC}"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 1: Pull latest code
echo -e "\n${YELLOW}📥 Pulling latest code...${NC}"
git pull origin main || {
    echo -e "${RED}❌ Git pull failed${NC}"
    exit 1
}
echo -e "${GREEN}✓ Code updated${NC}"

# Step 2: Install PHP dependencies
echo -e "\n${YELLOW}📦 Installing PHP dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction || {
    echo -e "${RED}❌ Composer install failed${NC}"
    exit 1
}
echo -e "${GREEN}✓ PHP dependencies installed${NC}"

# Step 3: Install and build frontend assets
echo -e "\n${YELLOW}🎨 Building frontend assets...${NC}"
npm ci || {
    echo -e "${RED}❌ npm ci failed${NC}"
    exit 1
}
npm run build || {
    echo -e "${RED}❌ npm build failed${NC}"
    exit 1
}
echo -e "${GREEN}✓ Frontend assets built${NC}"

# Step 4: Run database migrations
echo -e "\n${YELLOW}🗄️  Running database migrations...${NC}"
php artisan migrate --force || {
    echo -e "${RED}❌ Migration failed${NC}"
    exit 1
}
echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 5: Clear and cache configuration
echo -e "\n${YELLOW}⚡ Optimizing application...${NC}"
php artisan config:cache || {
    echo -e "${RED}❌ Config cache failed${NC}"
    exit 1
}
php artisan route:cache || {
    echo -e "${RED}❌ Route cache failed${NC}"
    exit 1
}
php artisan view:cache || {
    echo -e "${RED}❌ View cache failed${NC}"
    exit 1
}
php artisan event:cache || {
    echo -e "${YELLOW}⚠ Event cache failed (may not be available)${NC}"
}
echo -e "${GREEN}✓ Application optimized${NC}"

# Step 6: Restart queue workers (if supervisor is available)
if command -v supervisorctl &> /dev/null; then
    echo -e "\n${YELLOW}🔄 Restarting queue workers...${NC}"
    sudo supervisorctl restart nitesa-worker:* || {
        echo -e "${YELLOW}⚠ Supervisor restart failed (may not be configured)${NC}"
    }
    echo -e "${GREEN}✓ Queue workers restarted${NC}"
fi

# Step 7: Restart PHP-FPM (if available)
if systemctl is-active --quiet php*-fpm; then
    echo -e "\n${YELLOW}🔄 Restarting PHP-FPM...${NC}"
    sudo systemctl reload php*-fpm || {
        echo -e "${YELLOW}⚠ PHP-FPM reload failed${NC}"
    }
    echo -e "${GREEN}✓ PHP-FPM reloaded${NC}"
fi

# Step 8: Verify deployment
echo -e "\n${YELLOW}✅ Verifying deployment...${NC}"
php artisan about || {
    echo -e "${RED}❌ Application verification failed${NC}"
    exit 1
}
echo -e "${GREEN}✓ Application verified${NC}"

# Success message
echo -e "\n${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "\nNext steps:"
echo -e "  1. Test the application: https://nitesa.go.th"
echo -e "  2. Check logs: tail -f storage/logs/laravel.log"
echo -e "  3. Monitor queue: php artisan queue:monitor"
