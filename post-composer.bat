@ECHO OFF

CLS

SET LOG=%cd%\composer.log

ECHO Copy - Start at %DATE% - %TIME% > %LOG%
ECHO Copy - Start at %DATE% - %TIME%
ECHO.

SET VENDOR=%cd%\vendor\
SET NODE=%cd%\node_modules\
SET LIBS=%cd%\src\libs\

ECHO Copy files from %VENDOR% to %LIBS% >> %LOG%
ECHO. >> %LOG%

COPY %VENDOR%autoload.php %LIBS%autoload.php >> %LOG%
XCOPY %VENDOR%composer\*.* %LIBS%composer\*.* /E /Y >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=bootstrap\
IF EXIST %VENDOR%twitter\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%twitter\%LIB%dist\css\bootstrap.min.css %LIBS%%LIB%css\bootstrap.min.css >> %LOG%
   COPY %VENDOR%twitter\%LIB%dist\css\bootstrap.min.css.map %LIBS%%LIB%css\bootstrap.min.css.map >> %LOG%
   COPY %VENDOR%twitter\%LIB%dist\js\bootstrap.min.js %LIBS%%LIB%js\bootstrap.min.js >> %LOG%
   XCOPY %VENDOR%twitter\%LIB%dist\fonts\*.* %LIBS%%LIB%fonts\*.* /E /Y >> %LOG%

   REM Use by autocomposer autoload and seems to be linked to bootstrap
   XCOPY %VENDOR%symfony\*.* %LIBS%symfony\*.* /E /Y >> %LOG%

   ECHO      Kill unneeded files because GitHub will complain about binary files in the repository
   DEL %VENDOR%symfony\console\Resources\bin\hiddeninput.exe >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=datatables\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%%LIB%media\css\*.* %LIBS%%LIB%css\*.* /E /Y >> %LOG%
   XCOPY %VENDOR%%LIB%%LIB%media\images\*.* %LIBS%%LIB%images\*.* /E /Y >> %LOG%
   XCOPY %VENDOR%%LIB%%LIB%media\js\*.* %LIBS%%LIB%js\*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=dompdf\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
   REM COPY %VENDOR%%%LIB%autoload.inc.php %LIBS%%LIB%autoload.inc.php >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=font-awesome\
IF EXIST %VENDOR%fortawesome\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%fortawesome\%LIB%css\font-awesome.css.map %LIBS%%LIB%css\font-awesome.css.map >> %LOG%
   COPY %VENDOR%fortawesome\%LIB%css\font-awesome.min.css %LIBS%%LIB%css\font-awesome.min.css >> %LOG%
   XCOPY %VENDOR%fortawesome\%LIB%fonts\*.* %LIBS%%LIB%fonts\*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=jquery\
IF EXIST %VENDOR%components\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%components\%LIB%jquery.min.js %LIBS%%LIB%jquery.min.js >> %LOG%
   COPY %VENDOR%components\%LIB%jquery.min.map %LIBS%%LIB%jquery.min.map >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=jolicode\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=jstree\
IF EXIST %VENDOR%vakata\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%vakata\%LIB%dist\jstree.min.js %LIBS%%LIB%jstree.min.js >> %LOG%
   XCOPY %VENDOR%vakata\%LIB%dist\themes\*.* %LIBS%%LIB%themes\*.* /E /Y >> %LOG%
   COPY %VENDOR%vakata\%LIB%demo\filebrowser\file_sprite.png %LIBS%%LIB%file_sprite.png >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=phenx\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=noty\
IF EXIST %VENDOR%needim\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%needim\%LIB%js\noty\packaged\jquery.noty.packaged.min.js %LIBS%%LIB%jquery.noty.packaged.min.js >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=monolog\

IF EXIST %VENDOR%monolog\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%monolog\%LIB%src\*.* %LIBS%%LIB%\%LIB%\src\*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=parsedown\
IF EXIST %VENDOR%erusev\%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   COPY %VENDOR%erusev\%LIB%Parsedown.php %LIBS%%LIB%Parsedown.php >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=psr\
IF EXIST %VENDOR%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
)

REM ----------------------------------------------------------------------
SET LIB=reveal.js-menu\

IF EXIST %NODE%%LIB% (
   ECHO  === %LIB% === >> %LOG%
   ECHO  === %LIB% ===
   XCOPY %NODE%%LIB%*.* %LIBS%reveal\plugin\%LIB%*.* /E /Y >> %LOG%
)
