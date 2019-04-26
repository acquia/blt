@ECHO OFF
REM Running this file is equivalent to running `php blt`
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0blt
php "%BIN_TARGET%" %*
