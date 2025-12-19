@echo off
setlocal EnableDelayedExpansion

:: Payment Sync Challenge - Setup Script (Windows)
:: This script sets up a working Laravel project with the challenge files

echo.
echo ========================================
echo   Payment Sync Challenge - Setup
echo ========================================
echo.

:: Check prerequisites
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] Composer is required. Install from https://getcomposer.org
    pause
    exit /b 1
)

where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] PHP is required.
    pause
    exit /b 1
)

:: Check we're in the right directory
if not exist "app\Http\Controllers\PaymentWebhookController.php" (
    echo [X] Please run this script from the payment-sync-challenge directory
    pause
    exit /b 1
)

set "CHALLENGE_DIR=%CD%"
echo [i] Current directory: %CHALLENGE_DIR%

:: Step 1: Create fresh Laravel project
echo.
echo [1/5] Creating fresh Laravel project...
cd ..
if exist "_laravel_temp_" rmdir /s /q "_laravel_temp_"
composer create-project laravel/laravel _laravel_temp_ --quiet --no-interaction

if %ERRORLEVEL% NEQ 0 (
    echo [X] Failed to create Laravel project
    pause
    exit /b 1
)

:: Step 2: Copy Laravel framework files
echo [2/5] Copying Laravel framework...

:: Core directories
xcopy "_laravel_temp_\bootstrap" "%CHALLENGE_DIR%\bootstrap\" /E /I /Y /Q >nul
xcopy "_laravel_temp_\config" "%CHALLENGE_DIR%\config\" /E /I /Y /Q >nul
xcopy "_laravel_temp_\public" "%CHALLENGE_DIR%\public\" /E /I /Y /Q >nul
xcopy "_laravel_temp_\storage" "%CHALLENGE_DIR%\storage\" /E /I /Y /Q >nul
xcopy "_laravel_temp_\vendor" "%CHALLENGE_DIR%\vendor\" /E /I /Y /Q >nul

:: App subdirectories
xcopy "_laravel_temp_\app\Console" "%CHALLENGE_DIR%\app\Console\" /E /I /Y /Q >nul
xcopy "_laravel_temp_\app\Providers" "%CHALLENGE_DIR%\app\Providers\" /E /I /Y /Q >nul
if exist "_laravel_temp_\app\Exceptions" xcopy "_laravel_temp_\app\Exceptions" "%CHALLENGE_DIR%\app\Exceptions\" /E /I /Y /Q >nul

:: Routes - we need web.php
copy "_laravel_temp_\routes\web.php" "%CHALLENGE_DIR%\routes\web.php" /Y >nul

:: Root files
copy "_laravel_temp_\artisan" "%CHALLENGE_DIR%\artisan" /Y >nul
copy "_laravel_temp_\composer.lock" "%CHALLENGE_DIR%\composer.lock" /Y >nul

:: Cleanup temp Laravel
rmdir /s /q "_laravel_temp_"

:: Step 3: Setup environment
echo [3/5] Configuring environment...
cd "%CHALLENGE_DIR%"

:: Create .env file
(
echo APP_NAME="Payment Sync Challenge"
echo APP_ENV=local
echo APP_KEY=
echo APP_DEBUG=true
echo APP_URL=http://localhost:8000
echo.
echo LOG_CHANNEL=stack
echo LOG_LEVEL=debug
echo.
echo DB_CONNECTION=sqlite
echo.
echo BROADCAST_DRIVER=log
echo CACHE_DRIVER=file
echo FILESYSTEM_DISK=local
echo QUEUE_CONNECTION=sync
echo SESSION_DRIVER=file
echo SESSION_LIFETIME=120
echo.
echo MAIL_MAILER=log
echo MAIL_FROM_ADDRESS="noreply@example.com"
echo MAIL_FROM_NAME="${APP_NAME}"
) > .env

:: Generate app key
php artisan key:generate --quiet

:: Step 4: Setup database
echo [4/5] Setting up database...
type nul > database\database.sqlite
php artisan migrate:fresh --seed

if %ERRORLEVEL% NEQ 0 (
    echo [X] Migration failed
    pause
    exit /b 1
)

:: Step 5: Done
echo [5/5] Verifying setup...
echo.
echo ========================================
echo   [OK] Setup complete!
echo ========================================
echo.
echo Start the server:
echo   php artisan serve
echo.
echo Test the webhook (PowerShell):
echo   Invoke-WebRequest -Uri "http://localhost:8000/api/webhooks/payments" -Method POST -ContentType "application/json" -Body '{"event":"payment.success","order_ref":"ORD-1001","transaction_id":"txn_test","amount":25000}'
echo.
echo Check order status:
echo   Invoke-WebRequest -Uri "http://localhost:8000/api/orders/ORD-1001"
echo.

pause
