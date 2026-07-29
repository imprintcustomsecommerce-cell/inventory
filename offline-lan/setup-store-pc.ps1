# ============================================================
#  AUTO SETUP  -  STORE PC (192.168.150.106)
#  Double-click "SETUP-STORE-PC.bat" to run this.
#
#  It will:
#    1. Point this PC's .env at the inventory PC's database
#    2. Start the inventory system
#
#  Before running: nothing to start here (no Apache, no MySQL needed).
#  The inventory PC (192.168.150.80) must already be set up and ON.
# ============================================================

$ErrorActionPreference = "Stop"

# ---- settings (must match the inventory PC) ----
$DbHost = "192.168.150.80"
$DbPort = "3306"
$DbName = "inventory_system"
$DbUser = "inventory"
$DbPass = "imprint2026"
# ------------------------------------------------

$scriptDir = Split-Path -Parent $PSCommandPath
$projDir   = Join-Path (Split-Path -Parent $scriptDir) "inventory-system"
if (-not (Test-Path (Join-Path $projDir "artisan"))) {
    $projDir = Split-Path -Parent $scriptDir
}
$envFile = Join-Path $projDir ".env"

Write-Host "============================================" -ForegroundColor Cyan
Write-Host " STORE PC SETUP" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Project folder: $projDir"
Write-Host ""

if (-not (Test-Path $envFile)) {
    Write-Host "ERROR: .env file not found at $envFile" -ForegroundColor Red
    Read-Host "Press Enter to close"
    exit
}

# ---- 1. update the .env database settings ----
Write-Host "[1/2] Pointing this PC at the inventory PC's database..." -ForegroundColor Green

# back up the .env once
$backup = "$envFile.backup"
if (-not (Test-Path $backup)) { Copy-Item $envFile $backup }

$c = Get-Content $envFile -Raw
$c = $c -replace '(?m)^DB_HOST=.*',     "DB_HOST=$DbHost"
$c = $c -replace '(?m)^DB_PORT=.*',     "DB_PORT=$DbPort"
$c = $c -replace '(?m)^DB_DATABASE=.*', "DB_DATABASE=$DbName"
$c = $c -replace '(?m)^DB_USERNAME=.*', "DB_USERNAME=$DbUser"
$c = $c -replace '(?m)^DB_PASSWORD=.*', "DB_PASSWORD=$DbPass"
Set-Content -Path $envFile -Value $c -NoNewline -Encoding utf8

Write-Host "      Done. (A backup was saved as .env.backup)" -ForegroundColor Green

# clear Laravel's cached config so the new settings take effect
$php = @("C:\xampp1\php\php.exe","C:\xampp\php\php.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $php) { $php = "php" }
try { & $php (Join-Path $projDir "artisan") config:clear | Out-Null } catch {}

# ---- 2. start the server ----
Write-Host "[2/2] Starting the inventory system..." -ForegroundColor Green
Write-Host "      Open your browser to:  http://localhost:8000" -ForegroundColor Cyan
Write-Host "      (Keep the new window open. Close it to stop.)" -ForegroundColor Cyan
Write-Host ""
Start-Process $php -ArgumentList "artisan","serve","--host=0.0.0.0","--port=8000" -WorkingDirectory $projDir

Write-Host "Setup complete." -ForegroundColor Green
Write-Host "If the page shows a database error, make sure the inventory PC is ON and set up." -ForegroundColor Yellow
Read-Host "Press Enter to close this window"
