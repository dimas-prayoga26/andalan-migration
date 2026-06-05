@echo off
setlocal

cd /d "%~dp0..\.."
php artisan business-trips:lifecycle:sync %*

endlocal
