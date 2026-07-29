# ============================================================
#  AUTO SETUP  -  INVENTORY PC (the database host, 192.168.150.80)
#  Double-click "SETUP-INVENTORY-PC.bat" to run this.
#
#  It will:
#    1. Create the MySQL user the store PC uses
#    2. Open the firewall for MySQL (port 3306)
#    3. Start the inventory system
#
#  Before running: open XAMPP and start Apache + MySQL.
# ============================================================

$ErrorActionPreference = "Stop"

# ---- settings (change here if needed, keep in sync with the store PC) ----
$StoreIP   = "192.168.150.106"
$DbName    = "inventory_system"
$DbUser    = "inventory"
$DbPass    = "imprint2026"
# --------------------------------------------------------------------------

# Re-launch as Administrator if needed (required for the firewall step)
$admin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $admin) {
    Write-Host "Requesting Administrator rights..." -ForegroundColor Yellow
    Start-Process powershell "-ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

$scriptDir = Split-Path -Parent $PSCommandPath
$projDir   = Join-Path (Split-Path -Parent $scriptDir) "inventory-system"
if (-not (Test-Path (Join-Path $projDir "artisan"))) {
    # fallback: maybe this folder IS inside inventory-system
    $projDir = Split-Path -Parent $scriptDir
}

Write-Host "============================================" -ForegroundColor Cyan
Write-Host " INVENTORY PC SETUP" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Project folder: $projDir"
Write-Host ""

# ---- find XAMPP tools ----
$mysql = @("C:\xampp1\mysql\bin\mysql.exe","C:\xampp\mysql\bin\mysql.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
$php   = @("C:\xampp1\php\php.exe","C:\xampp\php\php.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $php) { $php = "php" }

# ---- 1. create the MySQL user ----
Write-Host "[1/3] Creating the database user for the store PC..." -ForegroundColor Green
if ($mysql) {
    $sql = "CREATE USER IF NOT EXISTS '$DbUser'@'$StoreIP' IDENTIFIED BY '$DbPass'; GRANT ALL PRIVILEGES ON $DbName.* TO '$DbUser'@'$StoreIP'; FLUSH PRIVILEGES;"
    try {
        $sql | & $mysql -u root
        Write-Host "      Done." -ForegroundColor Green
    } catch {
        Write-Host "      Could not run automatically. Make sure XAMPP MySQL is ON." -ForegroundColor Red
        Write-Host "      You can run offline-lan\create-mysql-user.sql in phpMyAdmin instead." -ForegroundColor Red
    }
} else {
    Write-Host "      Could not find mysql.exe. Run create-mysql-user.sql in phpMyAdmin instead." -ForegroundColor Red
}

# ---- 2. open the firewall ----
Write-Host "[2/3] Opening Windows Firewall for MySQL (port 3306)..." -ForegroundColor Green
New-NetFirewallRule -DisplayName "MySQL LAN 3306" -Direction Inbound -LocalPort 3306 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue | Out-Null
Write-Host "      Done." -ForegroundColor Green
Write-Host ""
Write-Host "      NOTE: If the store PC still cannot connect, open" -ForegroundColor Yellow
Write-Host "      C:\xampp1\mysql\bin\my.ini, set  bind-address=0.0.0.0," -ForegroundColor Yellow
Write-Host "      then restart MySQL in XAMPP." -ForegroundColor Yellow

# ---- 3. start the server ----
Write-Host "[3/3] Starting the inventory system..." -ForegroundColor Green
Write-Host "      Open your browser to:  http://localhost:8000" -ForegroundColor Cyan
Write-Host "      (Keep the new window open. Close it to stop.)" -ForegroundColor Cyan
Write-Host ""
Start-Process $php -ArgumentList "artisan","serve","--host=0.0.0.0","--port=8000" -WorkingDirectory $projDir

Write-Host "Setup complete." -ForegroundColor Green
Read-Host "Press Enter to close this window"
