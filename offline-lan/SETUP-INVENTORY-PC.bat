@echo off
REM ============================================================
REM  Double-click this on the INVENTORY PC (192.168.150.80).
REM  Start XAMPP (Apache + MySQL) first.
REM  It may ask for Administrator - click Yes.
REM ============================================================
powershell -ExecutionPolicy Bypass -File "%~dp0setup-inventory-pc.ps1"
