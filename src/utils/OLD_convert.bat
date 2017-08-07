@ECHO OFF
CLS
ECHO.
ECHO  ===============================================================================
ECHO  = Marknotes Convert utility
ECHO  = Convert .docx and .odt files into .md ones
ECHO  =
ECHO  = Remark : only process the current folder, not recursively
ECHO  ===============================================================================
ECHO.

ECHO  Retrieve the list of supported files
CALL :GetFileList

ECHO.
ECHO  Process files
CALL :ProcessFiles

REM ECHO.
REM ECHO  Update path to images
REM CALL :PostProcess_UpdatePathImages

ECHO.
DEL convert_files.log > NUL

ECHO.
ECHO DEBUG
ECHO.
TYPE a.md

EXIT /B

REM -------------------------------------------------------------------
:GetFileList

TYPE nul>convert_files.log

REM Loop any supported extensions and export any filename into a logfile for later processing
FOR %%G IN (docx odt) DO ( 
   FOR /F "tokens=*" %%f IN ('dir /B *.%%G') DO (
      ECHO %%~ff >> convert_files.log
   )
)
goto :eof

REM -------------------------------------------------------------------
:ProcessFiles

REM pandoc -w markdown_github --extract-media=images/%%~nf -o %%~df%%~pf%%~nf.md %%~f
REM   -w markdown_github  -w indicate the target desired format. Here, convert to a markdown_github format
REM   --atx-headers       Set the heading style to #, ##, ### and not lines (see http://pandoc.org/MANUAL.html#atx-style-headers)
REM   --ascii             Convert accentuated characters in their HTML equivalent (f.i. & will be saved as &amp;)
REM   --extract-media     Images will be extracted to the mentionned folder and 
REM                          %%~nf will be initialized to the source filename but without the path and the extension (so, f.i., OriginalFileName) 
REM   -o  :               Output filename 
REM                          %%~df will be initialized to the drive letter where the original file name is stored (f.i. c:)
REM                          %%~pf will be initialized to the folder where the original file name is stored (f.i. \folder\subfolder\)
REM                          %%~nf will be initialized to the filename without the extension (f.i. OriginalFileName)
REM                          .md   static, will be the final extension
REM   %%~f                Source filename to convert  

IF NOT EXIST .files MKDIR .files

FOR /F "tokens=*" %%f IN ('type convert_files.log') DO (

   ECHO     Process %%~f and export %%~df%%~pf%%~nf.md [%%~f]

   REM The intro is a file that will be inserted by pandoc at the top of the generated .md file
   REM So, we'll use the intro to make a link with the original file
   SET INTRO="[source](%%URL%%.files/%%~nf%%~xf)"
   ECHO %INTRO% > intro.tmp
   
   pandoc -B intro.tmp -w markdown_github --ascii --atx-headers --extract-media=.images/%%~nf -o %%~df%%~pf%%~nf.md %%~f
   
   DEL intro.tmp
   
   REM Move the file in the .files folder once processed
   
REM   move %%~f %%~df%%~pf/.files/%%~nf%%~xf
   
)

goto :eof

REM -------------------------------------------------------------------
:PostProcess_UpdatePathImages

REM Add the %URL% variable so marknotes will keep absolute filename for images

FOR /F "tokens=*" %%f IN ('dir /B *.md') DO (

   REM This line will create the convert.ps1 script to be used with Powershell
   REM Get-Content will read the content of the %%f file (i.e. the newley .md file, created by pandoc after the conversion)
   REM ForEach-Object will process every single lines
   REM    $_ -replace will replace ocurrences of =".images/ by ="%URL%.images/
   REM Set-Content %%~nf.tmp will export the replaced content in a .tmp file
   REM See https://stackoverflow.com/a/23089417/1065340
   
   ECHO ^(Get-Content "%%f"^) ^| ForEach-Object { $_ -replace "="".images/", "=""%%URL%%.images/" } ^| Set-Content %%~nf.tmp>convert.ps1

   REM Run the script
   
   Powershell.exe -executionpolicy ByPass -File convert.ps1
   
   REM If the .tmp file exists, the script was correctly executed
   
   IF EXIST %%~nf.tmp (
      DEL convert.ps1
	  DEL %%f	  
	  REN %%~nf.tmp %%~nf.md
   )   

)

goto :eof