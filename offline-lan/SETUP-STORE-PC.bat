@echo off
REM ============================================================
REM  Double-click this on the STORE PC (192.168.150.106).
REM  You do NOT need to start XAMPP here (no Apache, no MySQL).
REM  The inventory PC must already be set up and turned ON.
REM ============================================================
powershell -ExecutionPolicy Bypass -File "%~dp0setup-store-pc.ps1"
