#!/bin/bash
# =============================================
# NITESA Docker Setup Script
# Run this on production server
# =============================================

set -e

echo "=========================================="
echo "  NITESA Docker Setup"
echo "=========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker is not installed. Installing...${NC}"
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo -e "${GREEN}Docker installed successfully!${NC}"
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo -e "${RED}Docker Compose is not installed. Installing...${NC}"
    sudo apt-get update
    sudo apt-get install -y docker-compose-plugin
    echo -e "${GREEN}Docker Compose installed successfully!${NC}"
fi

# Navigate to project directory
cd /DATA/AppData/www/nitesa

# Create .env if not exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.docker...${NC}"
    cp .env.docker .env
    
    # Generate APP_KEY
    echo -e "${YELLOW}Generating APP_KEY...${NC}"
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s/APP_KEY=/APP_KEY=base64:$APP_KEY/" .env
    
    echo -e "${GREEN}.env created! Please update the values.${NC}"
fi

# Create necessary directories
echo -e "${YELLOW}Creating directories...${NC}"
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
echo -e "${YELLOW}Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache

# Build and start containers
echo -e "${YELLOW}Building Docker containers...${NC}"
docker compose build --no-cache

echo -e "${YELLOW}Starting containers...${NC}"
docker compose up -d

# Wait for MySQL to be ready
echo -e "${YELLOW}Waiting for MySQL to be ready...${NC}"
sleep 15

# Run migrations
echo -e "${YELLOW}Running migrations...${NC}"
docker compose exec app php artisan migrate --force

# Create storage link
echo -e "${YELLOW}Creating storage link...${NC}"
docker compose exec app php artisan storage:link

# Cache configuration
echo -e "${YELLOW}Caching configuration...${NC}"
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Show status
echo -e "${GREEN}=========================================="
echo "  NITESA Docker Setup Complete!"
echo "==========================================${NC}"
echo ""
echo "Application URL: http://203.172.184.47:9000"
echo ""
echo "Useful commands:"
echo "  docker compose ps          - Show running containers"
echo "  docker compose logs -f     - Show logs"
echo "  docker compose down        - Stop all containers"
echo "  docker compose restart     - Restart all containers"
echo ""
echo "To access the app container:"
echo "  docker compose exec app sh"
echo ""
