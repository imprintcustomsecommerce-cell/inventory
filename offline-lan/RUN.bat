@echo off
setlocal enabledelayedexpansion
title Imprint Inventory

REM ---- find PHP from XAMPP ----
REM If your XAMPP is somewhere else, add its path on a new line below,
REM copying the pattern (point it at ...\php\php.exe).
set "PHP="
if exist "C:\xampp\php\php.exe"   set "PHP=C:\xampp\php\php.exe"
if exist "C:\xampp1\php\php.exe"  set "PHP=C:\xampp1\php\php.exe"
if exist "D:\inv\php\php.exe"     set "PHP=D:\inv\php\php.exe"
if exist "D:\xampp\php\php.exe"   set "PHP=D:\xampp\php\php.exe"

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

REM ---- make product images viewable (safe to run every time) ----
if not exist "%APP%\public\storage" (
  echo  Linking the images folder...
  "%PHP%" artisan storage:link
)

REM ---- detect this PC's current network IP (the active adapter) ----
set "LANIP="
for /f "delims=" %%i in ('powershell -NoProfile -Command "(Get-NetIPConfiguration ^| Where-Object {$_.IPv4DefaultGateway -ne $null -and $_.NetAdapter.Status -eq 'Up'} ^| Select-Object -First 1).IPv4Address.IPAddress" 2^>nul') do set "LANIP=%%i"

echo.
echo  Using PHP: %PHP%
echo  App folder: %APP%
echo.
echo  ==================================================================
echo   THIS PC (inventory) - open:   http://localhost:8000
if defined LANIP (
echo   OTHER PCs (store/phone)  -    http://%LANIP%:8000
) else (
echo   OTHER PCs: run "ipconfig" and use  http://[IPv4 Address]:8000
)
echo  ==================================================================
echo.
echo  If the OTHER PCs address stops working later, it is because this
echo  PC's IP changed - just re-open this window and use the new address
echo  shown above (or set a fixed IP so it never changes).
echo.
echo  Keep THIS window open while you use the system. Close it to stop.
echo.

"%PHP%" artisan serve --host=0.0.0.0 --port=8000

echo.
echo  The server stopped. Read any message above for the reason.
pause
