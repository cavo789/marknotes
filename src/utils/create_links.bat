@ECHO OFF

REM --- Please adapt this location to reflect your installation; the folder where you've put marknotes source files
SET MASTER=C:\Christophe\Repository\markdown\src\

CALL :ShowInfo
:DoIt

GOTO :END

REM ---------------------------------------------------------------------------------------------------------
:ShowInfo
CLS

ECHO  ===============================================
ECHO  =                                             =
ECHO  = Marknotes                                   =
ECHO  =                                             =
ECHO  = Quickly deploy a copy of marknotes, on a    =
ECHO  = localhost, by just referencing source       =
ECHO  = files so a change in a source file will be  =
ECHO  = reflected in the copy                       =
ECHO  =                                             =
ECHO  = The added-value of this script is to avoid, =
ECHO  = when you need to have more than one         =
ECHO  = "marknotes" website on your system, to      =
ECHO  = duplicate every files.                      =
ECHO  =                                             =
ECHO  = Under Windows OS, the mklink command allow  =
ECHO  = to create a symlink (like a shortcut) and   =
ECHO  = files should only be there once on your     =
ECHO  = system.                                     =
ECHO  =                                             =
ECHO  = NOTE : THIS SCRIPT SHOULD BE EXECUTED IN A  =
ECHO  = COMMAND PROMPT BUT ONLY IF YOU'VE STARTED   =
ECHO  = THE PROMPT WITH "RUN AS AN ADMIN"           =
ECHO  =                                             =
ECHO  ===============================================

ECHO.
ECHO  This script can be used to quickly create a new local website for marknotes.
ECHO  A copy of marknotes should be present in the %MASTER%
ECHO  folder (or edit and change this script for an another location) and symbolic
ECHO  links will be created to that folder for marknotes files and folders.
ECHO.
ECHO  Only the \docs folder and the \settings.json file will be specific to the new local website.
ECHO.
ECHO  Press CTRL+C to stop this script or press the enter key to continue.
ECHO.
ECHO  (this script should be started in a Windows command prompt with admin privileges)
ECHO.
PAUSE

REM ---------------------------------------------------------------------------------------------------------
:DoIt
mklink /D assets %MASTER%assets
mklink /D languages %MASTER%languages
mklink /D libs %MASTER%libs
mklink /D marknotes %MASTER%marknotes
mklink /D templates %MASTER%templates

if not exist "cache" mkdir cache
if not exist "docs" mkdir docs
if not exist "tmp" mkdir tmp

if not exist ".htaccess.txt" copy %MASTER%.htaccess.txt .htaccess
if not exist "browserconfig.xml" mklink browserconfig.xml %MASTER%browserconfig.xml
if not exist "index.php" mklink index.php %MASTER%index.php
if not exist "router.php" mklink router.php %MASTER%router.php
if not exist "settings.json.dist" mklink settings.json.dist %MASTER%settings.json.dist

if not exist "tags.json" echo {} > tags.json

REM ---------------------------------------------------------------------------------------------------------
:END
