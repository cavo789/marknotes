REM This script will update libraries used by marknotes by downloading the latest
REM version from their github repo or composer then copy files in the destination
REM folder (folder /libs or in /plugins/... (where the lib is used)

cls

@ECHO OFF

REM ████████████████████████████████████████████████████████████████████████
REM █ Update libraries used by marknotes                                   █ 
REM █    1. in /vendor/ update each git repositories                       █ 
REM █    2. call composer update                                           █ 
REM ████████████████████████████████████████████████████████████████████████

ECHO.
ECHO 1. Update git repositories
pushd vendor
call git_pull.bat
popd
ECHO.
ECHO 2. Composer update
ECHO.
call composer.bat update
ECHO.
ECHO END
ECHO.