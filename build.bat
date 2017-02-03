@ECHO OFF

cls

REM ████████████████████████████████████████████████████████████████████████
REM █ Build the /dist scripts                                              █
REM █    1. ZIP the source folder                                          █
REM █    2. Beautify code (use a DOS Batch because phpcbf returns an error █
REM █       and break gulp processing)                                     █
REM █    3. Generate the /dist folder (minify css, js, ...)                █ 
REM ████████████████████████████████████████████████████████████████████████

call gulp backup

REM check the quality of the code
REM PHP Code Sniffer
call gulp phpcs

REM PHP Mess Detector
call gulp phpmd

setlocal
ECHO.
SET /P CONTINUE=Do you want to continue with linting (Y/[N]) ?
IF /I "%CONTINUE%" NEQ "Y" GOTO END

call gulp lint

setlocal
ECHO.
SET /P CONTINUE=Do you want to continue with code beautifier (Y/[N]) ?
IF /I "%CONTINUE%" NEQ "Y" GOTO END

call gulp phpcbf
call gulp jscbf
call gulp csscbf
call gulp jsoncbf

ECHO.
SET /P CONTINUE=Do you want to continue with /dist generation (Y/[N]) ?
IF /I "%CONTINUE%" NEQ "Y" GOTO END

ECHO.
ECHO  === This last step can be fired alone, run "gulp dist" ===
ECHO.

call gulp delete
call gulp copy
call gulp dist

:END
endlocal


REM Now, continue with the normal build process

REM call gulp build

:END
endlocal