@ECHO OFF
CLS
ECHO.
ECHO  *******************************************************
ECHO  *                                                     *
ECHO  * Marknotes - PHP-CodeSniffer - Autofix syntax issues *
ECHO  *                                                     *
ECHO  *******************************************************
ECHO.
C:\Christophe\Repository\devtools\PHP_CodeSniffer\bin\phpcbf.bat c:\christophe\repository\markdown\src\marknotes\ --tab-width=4 --standard=c:\christophe\repository\ruleset.xml
