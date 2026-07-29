@echo off
REM ============================================================
REM  Double-click this on the STORE PC (192.168.150.106).
REM  Start XAMPP (Apache) first.
REM  The inventory PC must already be set up and turned ON.
REM ============================================================
powershell -ExecutionPolicy Bypass -File "%~dp0setup-store-pc.ps1"
