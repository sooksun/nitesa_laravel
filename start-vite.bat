@echo off
chcp 65001 >nul
echo ========================================
echo   Starting Vite Dev Server
echo ========================================
echo.
echo This will start Vite with Hot Module Replacement (HMR)
echo Keep this window open while developing
echo.
echo Press Ctrl+C to stop
echo.

cd /d "%~dp0"
npm run dev
