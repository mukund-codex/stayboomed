@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../laravel/lumen-installer/lumen
php "%BIN_TARGET%" %*
