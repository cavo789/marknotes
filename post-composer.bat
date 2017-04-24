@ECHO OFF

CLS

SET LOG=%cd%\composer.log

ECHO Copy - Start at %DATE% - %TIME% > %LOG%
ECHO Copy - Start at %DATE% - %TIME%
ECHO.

SET VENDOR=%cd%\vendor\
SET LIBS=%cd%\src\libs\

ECHO Copy files from %VENDOR% to %LIBS% >> %LOG%
ECHO. >> %LOG%

COPY %VENDOR%autoload.php %LIBS%autoload.php >> %LOG%
XCOPY %VENDOR%composer\*.* %LIBS%composer\*.* /E /Y >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=psr\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
)