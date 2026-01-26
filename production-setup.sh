#!/bin/bash

# NITESA Production Server Setup Script
# สำหรับ Server: http://203.172.184.47:9000/
# Usage: ./production-setup.sh

set -e

echo "🚀 Setting up NITESA Production Server..."
echo "Server: http://203.172.184.47:9000/"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠ .env file not found. Creating from template...${NC}"
    if [ -f .env.production.server ]; then
        cp .env.production.server .env
        echo -e "${GREEN}✓ Created .env from template${NC}"
    else
        echo -e "${RED}❌ .env.production.server not found!${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ .env file exists${NC}"
    read -p "Backup existing .env? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo -e "${GREEN}✓ Backed up .env${NC}"
    fi
fi

# Step 1: Update .env for production
echo -e "\n${YELLOW}📝 Updating .env for production...${NC}"
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=.*|APP_URL=http://203.172.184.47:9000|' .env
sed -i 's/LOG_LEVEL=debug/LOG_LEVEL=error/' .env
sed -i 's/FILESYSTEM_DISK=local/FILESYSTEM_DISK=public/' .env
echo -e "${GREEN}✓ .env updated${NC}"

# Step 2: Generate app key if needed
echo -e "\n${YELLOW}🔑 Checking APP_KEY...${NC}"
if ! grep -q "APP_KEY=base64:" .env || grep -q "APP_KEY=$" .env; then
    php artisan key:generate
    echo -e "${GREEN}✓ APP_KEY generated${NC}"
else
    echo -e "${GREEN}✓ APP_KEY already exists${NC}"
fi

# Step 3: Install dependencies
echo -e "\n${YELLOW}📦 Installing dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Step 4: Build frontend assets
echo -e "\n${YELLOW}🎨 Building frontend assets...${NC}"
if [ -f package.json ]; then
    npm install --silent
    npm run build
    echo -e "${GREEN}✓ Frontend assets built${NC}"
else
    echo -e "${YELLOW}⚠ package.json not found, skipping frontend build${NC}"
fi

# Step 5: Run migrations
echo -e "\n${YELLOW}🗄️  Running database migrations...${NC}"
read -p "Run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Skipped migrations${NC}"
fi

# Step 6: Create storage link
echo -e "\n${YELLOW}📁 Creating storage link...${NC}"
if [ ! -L public/storage ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Storage link created${NC}"
else
    echo -e "${GREEN}✓ Storage link already exists${NC}"
fi

# Step 7: Clear and cache
echo -e "\n${YELLOW}⚡ Optimizing application...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || echo -e "${YELLOW}⚠ Event cache not available${NC}"

echo -e "${GREEN}✓ Application optimized${NC}"

# Step 8: Set permissions (Linux/Unix only)
if [ "$(uname)" != "MINGW"* ] && [ "$(uname)" != "MSYS"* ]; then
    echo -e "\n${YELLOW}🔐 Setting permissions...${NC}"
    chmod -R 775 storage bootstrap/cache 2>/dev/null || echo -e "${YELLOW}⚠ Could not set permissions (may need sudo)${NC}"
    echo -e "${GREEN}✓ Permissions set${NC}"
fi

# Step 9: Verify setup
echo -e "\n${YELLOW}✅ Verifying setup...${NC}"
php artisan about
echo ""

# Success message
echo -e "${GREEN}🎉 Production setup completed!${NC}"
echo ""
echo "Next steps:"
echo "  1. Review .env file and update database/mail settings"
echo "  2. Test the application: http://203.172.184.47:9000/"
echo "  3. Check logs: tail -f storage/logs/laravel.log"
echo "  4. Review: PRODUCTION_SERVER_SETUP.md"
echo ""
