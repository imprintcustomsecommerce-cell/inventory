@echo off
REM ============================================================
REM  RUN THIS ON EITHER PC to start the inventory system.
REM  Put this file inside the "inventory-system" folder,
REM  or edit the path on the next line to point to it.
REM
REM  Leave this window OPEN while you use the system.
REM  Close it to stop the server.
REM ============================================================

cd /d "%~dp0inventory-system"
if not exist artisan cd /d "%~dp0"

echo Starting Imprint Inventory...
echo Open your browser to:  http://localhost:8000
echo (Close this window to stop.)
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
