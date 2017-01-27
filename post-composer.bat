@ECHO OFF

CLS

SET LOG=%cd%\composer.log

ECHO Copy - Start at %DATE% - %TIME% > %LOG%
ECHO Copy - Start at %DATE% - %TIME%
ECHO.

SET VENDOR=%cd%\vendor\
SET LIBS=%cd%\libs\

ECHO Copie les fichiers depuis %VENDOR% vers %LIBS% >> %LOG%
ECHO. >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=bootstrap\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%twitter\%LIB%dist\css\bootstrap.min.css %LIBS%%LIB%css\bootstrap.min.css >> %LOG%
COPY %VENDOR%twitter\%LIB%dist\css\bootstrap.min.css.map %LIBS%%LIB%css\bootstrap.min.css.map >> %LOG%
COPY %VENDOR%twitter\%LIB%dist\js\bootstrap.min.js %LIBS%%LIB%js\bootstrap.min.js >> %LOG%
XCOPY %VENDOR%twitter\%LIB%dist\fonts\*.* %LIBS%%LIB%fonts\*.* /E /Y >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=datatables\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
XCOPY %VENDOR%%LIB%%LIB%media\css\*.* %LIBS%%LIB%css\*.* /E /Y >> %LOG%
XCOPY %VENDOR%%LIB%%LIB%media\images\*.* %LIBS%%LIB%images\*.* /E /Y >> %LOG%
XCOPY %VENDOR%%LIB%%LIB%media\js\*.* %LIBS%%LIB%js\*.* /E /Y >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=font-awesome\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%fortawesome\%LIB%css\font-awesome.css.map %LIBS%%LIB%css\font-awesome.css.map >> %LOG%
COPY %VENDOR%fortawesome\%LIB%css\font-awesome.min.css %LIBS%%LIB%css\font-awesome.min.css >> %LOG%
XCOPY %VENDOR%fortawesome\%LIB%fonts\*.* %LIBS%%LIB%fonts\*.* /E /Y >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=jquery\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%components\%LIB%jquery.min.js %LIBS%%LIB%jquery.min.js >> %LOG%
COPY %VENDOR%components\%LIB%jquery.min.map %LIBS%%LIB%jquery.min.map >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=jstree\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%vakata\%LIB%dist\jstree.min.js %LIBS%%LIB%jstree.min.js >> %LOG%
XCOPY %VENDOR%vakata\%LIB%dist\themes\*.* %LIBS%%LIB%themes\*.* /E /Y >> %LOG%
COPY %VENDOR%vakata\%LIB%demo\filebrowser\file_sprite.png %LIBS%%LIB%file_sprite.png >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=noty\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%needim\%LIB%js\noty\packaged\jquery.noty.packaged.min.js %LIBS%%LIB%jquery.noty.packaged.min.js >> %LOG%

REM ----------------------------------------------------------------------
SET LIB=parsedown\
ECHO  === %LIB% === >> %LOG%
ECHO  === %LIB% ===
COPY %VENDOR%erusev\%LIB%Parsedown.php %LIBS%%LIB%Parsedown.php >> %LOG%