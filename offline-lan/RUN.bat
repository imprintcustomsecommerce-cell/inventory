@echo off
setlocal enabledelayedexpansion
title Imprint Inventory

REM ---- find PHP from XAMPP ----
set "PHP="
if exist "C:\xampp\php\php.exe"  set "PHP=C:\xampp\php\php.exe"
if exist "C:\xampp1\php\php.exe" set "PHP=C:\xampp1\php\php.exe"

if "%PHP%"=="" (
  echo.
  echo  Could not find PHP. XAMPP was not found at C:\xampp or C:\xampp1.
  echo  If XAMPP is installed somewhere else, tell us where and we will fix this file.
  echo.
  pause
  exit /b
)

REM ---- find the app folder (the one containing "artisan") ----
set "APP=%~dp0inventory-system"
if not exist "%APP%\artisan" set "APP=%~dp0"
if not exist "%APP%\artisan" (
  echo.
  echo  Could not find the app. Make sure this file sits next to the
  echo  "inventory-system" folder, or inside it.
  echo.
  pause
  exit /b
)

cd /d "%APP%"

echo.
echo  Using PHP: %PHP%
echo  App folder: %APP%
echo.
echo  Starting the inventory system...
echo  When it says "Server running", open your browser to:
echo.
echo        http://localhost:8000
echo.
echo  Keep THIS window open while you use the system. Close it to stop.
echo  ------------------------------------------------------------------
echo.

"%PHP%" artisan serve --host=0.0.0.0 --port=8000

echo.
echo  The server stopped. Read any message above for the reason.
pause
