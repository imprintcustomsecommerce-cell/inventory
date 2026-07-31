@echo off
setlocal
title Allow Store PC Access (Inventory PC)

REM ============================================================
REM  Run this ONCE on the INVENTORY PC (192.168.150.80).
REM  RIGHT-CLICK this file -> "Run as administrator".
REM  It lets other PCs on the network open the system in a browser.
REM ============================================================

REM ---- must be Administrator ----
net session >nul 2>&1
if errorlevel 1 (
  echo.
  echo  This needs Administrator.
  echo  Close this, then RIGHT-CLICK the file and choose "Run as administrator".
  echo.
  pause & exit /b
)

echo.
echo  Opening the firewall so other PCs can reach the system (port 8000)...
netsh advfirewall firewall add rule name="Imprint Inventory 8000" dir=in action=allow protocol=TCP localport=8000 >nul
echo  Done.
echo.
echo  ------------------------------------------------------------
echo  On the STORE PC (or any PC on the network), just open a browser to:
echo.
echo        http://192.168.150.80:8000
echo.
echo  Keep this Inventory PC ON with RUN.bat running.
echo  ------------------------------------------------------------
echo.
pause
