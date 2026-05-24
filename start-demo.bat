@echo off
setlocal

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-demo.ps1" %*
exit /b %ERRORLEVEL%
