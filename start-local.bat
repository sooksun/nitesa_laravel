@echo off
chcp 65001 >nul
echo ========================================
echo   NITESA Local Development Setup
echo ========================================
echo.

cd /d "%~dp0"

echo [1/6] Checking PHP...
php -v
if %errorlevel% neq 0 (
    echo ❌ PHP not found! Please install PHP 8.1+
    pause
    exit /b 1
)
echo ✅ PHP OK
echo.

echo [2/6] Checking Composer dependencies...
if not exist "vendor" (
    echo Installing Composer dependencies...
    composer install --no-interaction
    if %errorlevel% neq 0 (
        echo ❌ Composer install failed!
        pause
        exit /b 1
    )
) else (
    echo ✅ Composer dependencies already installed
)
echo.

echo [3/6] Checking Node dependencies...
if not exist "node_modules" (
    echo Installing Node dependencies...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ npm install failed!
        pause
        exit /b 1
    )
) else (
    echo ✅ Node dependencies already installed
)
echo.

echo [4/6] Checking .env file...
if not exist ".env" (
    echo Creating .env from .env.example...
    copy .env.example .env
    echo Generating application key...
    php artisan key:generate
) else (
    echo ✅ .env file exists
)
echo.

echo [5/6] Creating storage link...
if not exist "public\storage" (
    php artisan storage:link
    echo ✅ Storage link created
) else (
    echo ✅ Storage link already exists
)
echo.

echo [6/6] Checking database migrations...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Database connection issue. Please check .env file
    echo.
    echo Please ensure:
    echo   - MySQL is running in Laragon
    echo   - Database 'nitesa' exists
    echo   - DB credentials in .env are correct
    echo.
) else (
    echo ✅ Database connection OK
    echo.
    echo Running migrations...
    php artisan migrate --force
)
echo.

echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Starting development server...
echo.
echo 🌐 Open: http://localhost:8000
echo 📧 Mailpit: http://localhost:8025
echo.
echo Press Ctrl+C to stop the server
echo.

php artisan serve
