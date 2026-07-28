# ============================================================
#  RUN THIS ON THE INVENTORY PC (192.168.150.80) ONLY
#  It lets the store PC reach the database over the LAN.
#
#  How to run:
#    Right-click this file -> "Run with PowerShell"
#    (If it asks for Administrator, click Yes.)
# ============================================================

Write-Host "Opening MySQL port 3306 to the local network..." -ForegroundColor Cyan

New-NetFirewallRule -DisplayName "MySQL LAN 3306" `
    -Direction Inbound -LocalPort 3306 -Protocol TCP -Action Allow `
    -ErrorAction SilentlyContinue

Write-Host "Done. Port 3306 is now allowed through Windows Firewall." -ForegroundColor Green
Write-Host ""
Write-Host "Reminder: also make sure MySQL is listening on the LAN:" -ForegroundColor Yellow
Write-Host "  1. Open C:\xampp1\mysql\bin\my.ini in Notepad" -ForegroundColor Yellow
Write-Host "  2. Find the line  bind-address=127.0.0.1  and change it to  bind-address=0.0.0.0" -ForegroundColor Yellow
Write-Host "     (if there is no such line, you can skip this)" -ForegroundColor Yellow
Write-Host "  3. Restart MySQL in the XAMPP Control Panel" -ForegroundColor Yellow
Write-Host ""
Read-Host "Press Enter to close"
