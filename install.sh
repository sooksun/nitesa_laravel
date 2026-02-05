#!/bin/bash
# =============================================================================
# NITESA - Installation Script for Production Server
# Server: https://nitesa.cnppai.com/
# =============================================================================

set -e  # Exit on error

echo "=========================================="
echo "  NITESA Installation Script"
echo "=========================================="

# Configuration - แก้ไขค่าเหล่านี้ตามต้องการ
DB_HOST="192.168.1.4"
DB_NAME="nitesa"
DB_USER="nitesa_user"
DB_PASS="your_secure_password"
APP_URL="https://nitesa.cnppai.com"
ADMIN_EMAIL="admin@nitesa.local"
ADMIN_PASSWORD="password123"

# =============================================================================
# Step 1: Create .env file
# =============================================================================
echo ""
echo "[Step 1/10] Creating .env file..."

cat > .env << EOF
APP_NAME="ระบบนิเทศการศึกษา"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=${APP_URL}
ASSET_URL=${APP_URL}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=redis
SESSION_LIFETIME=120
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

FILESYSTEM_DISK=public
EOF

echo "✓ .env file created"

# =============================================================================
# Step 2: Build Docker containers
# =============================================================================
echo ""
echo "[Step 2/10] Building Docker containers..."
docker compose up -d --build
echo "✓ Docker containers built and started"

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# =============================================================================
# Step 3: Install PHP dependencies
# =============================================================================
echo ""
echo "[Step 3/10] Installing PHP dependencies..."
docker compose exec -T app composer install --no-dev --optimize-autoloader
echo "✓ PHP dependencies installed"

# =============================================================================
# Step 4: Install Node dependencies and build assets
# =============================================================================
echo ""
echo "[Step 4/10] Building frontend assets..."
docker compose exec -T app npm ci
docker compose exec -T app npm run build
echo "✓ Frontend assets built"

# =============================================================================
# Step 5: Set permissions
# =============================================================================
echo ""
echo "[Step 5/10] Setting permissions..."
docker compose exec -T app chown -R www-data:www-data /var/www/html/storage
docker compose exec -T app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec -T app chmod -R 775 /var/www/html/storage
docker compose exec -T app chmod -R 775 /var/www/html/bootstrap/cache
echo "✓ Permissions set"

# =============================================================================
# Step 6: Generate APP_KEY
# =============================================================================
echo ""
echo "[Step 6/10] Generating APP_KEY..."
docker compose exec -T app php artisan key:generate --force
echo "✓ APP_KEY generated"

# =============================================================================
# Step 7: Run database migrations
# =============================================================================
echo ""
echo "[Step 7/10] Running database migrations..."
docker compose exec -T app php artisan migrate --force
echo "✓ Database migrations completed"

# =============================================================================
# Step 8: Create Admin user
# =============================================================================
echo ""
echo "[Step 8/10] Creating Admin user..."
docker compose exec -T app php artisan tinker --execute="
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

if (!User::where('email', '${ADMIN_EMAIL}')->exists()) {
    User::create([
        'name' => 'Admin',
        'email' => '${ADMIN_EMAIL}',
        'password' => Hash::make('${ADMIN_PASSWORD}'),
        'role' => Role::ADMIN,
    ]);
    echo 'Admin user created successfully!';
} else {
    echo 'Admin user already exists.';
}
"
echo "✓ Admin user ready"

# =============================================================================
# Step 9: Publish assets and optimize
# =============================================================================
echo ""
echo "[Step 9/10] Publishing assets and optimizing..."
docker compose exec -T app php artisan livewire:publish --assets
docker compose exec -T app php artisan storage:link 2>/dev/null || true
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan view:cache
echo "✓ Assets published and optimized"

# =============================================================================
# Step 10: Restart containers
# =============================================================================
echo ""
echo "[Step 10/10] Restarting containers..."
docker compose restart
echo "✓ Containers restarted"

# =============================================================================
# Done!
# =============================================================================
echo ""
echo "=========================================="
echo "  Installation Complete!"
echo "=========================================="
echo ""
echo "Application URL: ${APP_URL}"
echo "Admin Email: ${ADMIN_EMAIL}"
echo "Admin Password: ${ADMIN_PASSWORD}"
echo ""
echo "Please change the admin password after first login!"
echo ""
