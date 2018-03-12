@ECHO OFF

CLS

SET LOG=%cd%\composer.log

ECHO Copy - Start at %DATE% - %TIME% > %LOG%
ECHO Copy - Start at %DATE% - %TIME%
ECHO.

SET VENDOR=%cd%\vendor\
SET MANUAL=%cd%\vendor_manual\
SET NODE=%cd%\node_modules\

REM "src" folder i.e. the root folder of marknotes
SET SRC=%cd%\src\

REM "libs" will contains generic libraries, used by PHP or global like jQuery and Boostrap
SET LIBS=%SRC%libs\

REM "page" points to plugins\page where specific libraries used by plugins (like dataTables)
REM will be stored
SET MARKDOWN=%SRC%marknotes\plugins\markdown\
SET PAGE=%SRC%marknotes\plugins\page\
SET TASK=%SRC%marknotes\plugins\task\

REM USED IN PHP SO COPY INTO /libs
call :fnCopyComposer
REM call :fnCopyBootstrap
REM call :fnCopyjQuery
REM call :fnCopySymfony
REM call :fnCopySlugify
REM call :fnCopydompdf
REM call :fnCopyfontAwesome
REM call :fnCopyJolicode
REM call :fnCopyNoty
REM call :fnCopyMonolog
REM call :fnCopyParsedown
REM call :fnCopyParsedownCheckbox
REM call :fnCopyMinify
REM call :fnCopyGitHubMarkdownCSS
REM call :fnCopyPHPFONT
REM call :fnCopyPHPSVG
REM call :fnCopyPHP_error
REM call :fnCopyCrawlerDetect
REM call :fnCopyAnimateCSS
REM call :fnCopyURLjs
REM call :fnIonIcons
REM call :fnAdminLTE
REM call :fnSlimScroll
REM call :fnJSONLint
REM call :fnFlySystem
REM call :fnScrollDir
REM call :fnCSSCheckboxLib
REM call :fnSweetAlert
REM call :fnCopyjs-cookie
REM call :fnCopyTracy
REM call :fnCopyMultiDownload

REM USED IN PLUGINS SO COPY INTO /plugins/page/xxx folder (i.e. where the lib is used)
REM call :fnCopyDatatables
REM call :fnCopyjsTree
REM call :fnCopyjsTreeProton
REM call :fnCopySimpleMDE
REM call :fnCopyPrism
REM call :fnCopyFlexDataList
REM call :fnCopyjQueryHighLight
REM call :fnCopyPrintPreview
REM call :fnCopyClipboardJS
REM call :fnCopyLinkify
REM call :fnCopyStoreJS
REM call :fnCopyLazySizes
REM call :fnCopyAnchor
REM call :fnCopyTimeline
REM call :fnCopyFakeLoader
REM call :fnCopyEmoji
REM call :fnCopyGoogoose
REM call :fnCopyRemark
REM call :fnCopyReveajJS
REM call :fnCopyReveajJS-Menu
REM call :fnCopyReveajJS-ElapsedTimeBar
REM call :fnCopyReveajJS-TitleFooter
REM call :fnCopyBalloon
REM call :fnCopyjqueryi18n
REM call :fnCopyCLDRPluralRuleParser
REM call :fnCopyFileSaver
REM call :fnGitHubCorners
REM call :fnUpload
REM call :fnHTML2MD
REM call :fnGuzzle
REM call :fnGoogleTranslate
REM call :fnhtmLawed
REM call :fnFinalize
GOTO END:

REM -----------------------------------------------
REM -----------------------------------------------
REM -------------- USED IN CORE / PHP -------------
REM -----------------------------------------------
REM -----------------------------------------------

::--------------------------------------------------------
::-- fnCopyComposer
::--------------------------------------------------------

:fnCopyComposer
ECHO  === composer ===
ECHO	COPY TO %LIBS%composer\
ECHO.
COPY %VENDOR%autoload.php %LIBS% >> %LOG%
XCOPY %VENDOR%composer\*.* %LIBS%composer\ /E /Y  >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyBootstrap
::--------------------------------------------------------

:fnCopyBootstrap
ECHO  === bootstrap ===
ECHO	COPY TO %LIBS%bootstrap\
ECHO.
IF NOT EXIST %LIBS%bootstrap\css MKDIR %LIBS%bootstrap\css >> %LOG%
COPY %VENDOR%twitter\bootstrap\dist\css\bootstrap.min.css %LIBS%bootstrap\css\ /Y >> %LOG%
COPY %VENDOR%twitter\bootstrap\dist\css\bootstrap.min.css.map %LIBS%bootstrap\css\ /Y >> %LOG%
IF NOT EXIST %LIBS%bootstrap\js MKDIR %LIBS%bootstrap\js >> %LOG%
COPY %VENDOR%twitter\bootstrap\dist\js\bootstrap.min.js %LIBS%bootstrap\js\ /Y >> %LOG%
XCOPY %VENDOR%twitter\bootstrap\dist\fonts\*.* %LIBS%bootstrap\fonts\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjQuery
::--------------------------------------------------------

:fnCopyjQuery
ECHO  === jQuery ===
ECHO	COPY TO %LIBS%jquery\
ECHO.
IF NOT EXIST %LIBS%jquery\ MKDIR %LIBS%jquery\ >> %LOG%
COPY %VENDOR%components\jquery\jquery.min.js %LIBS%jquery\jquery.min.js /Y >> %LOG%
COPY %VENDOR%components\jquery\jquery.min.map %LIBS%jquery\jquery.min.map /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjs-cookie
::--------------------------------------------------------

:fnCopyjs-cookie
ECHO  === js-cookie ===
ECHO	COPY TO %LIBS%js-cookie\
ECHO.
IF NOT EXIST %LIBS%js-cookie\ MKDIR %LIBS%js-cookie\ >> %LOG%
COPY %VENDOR%js-cookie\src\js.cookie.js %LIBS%js-cookie /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyMultiDownload
::--------------------------------------------------------

:fnCopyMultiDownload
ECHO  === fnCopyMultiDownload ===
ECHO	COPY TO %LIBS%multi-download\
ECHO.
IF NOT EXIST %LIBS%multi-download\ MKDIR %LIBS%multi-download\ >> %LOG%
COPY %VENDOR%multi-download\browser.js %LIBS%multi-download\multi-download.js /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopySymfony
::--------------------------------------------------------

:fnCopySymfony
ECHO  === symfony ===
ECHO	COPY TO %LIBS%symfony\
ECHO.
XCOPY %VENDOR%symfony\*.* %LIBS%symfony\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopySlugify
::--------------------------------------------------------

:fnCopySlugify
ECHO  === slugify ===
ECHO	COPY TO %LIBS%slugify\
ECHO.
XCOPY %VENDOR%cocur\slugify\src\*.* %LIBS%slugify\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopydompdf
::--------------------------------------------------------

:fnCopydompdf
ECHO  === dompdf ===
ECHO	COPY TO %LIBS%dompdf\
ECHO.
XCOPY %VENDOR%dompdf\*.* %LIBS%dompdf\ /E /Y >> %LOG%
IF EXIST %LIBS%dompdf\dompdf\tests\ RMDIR %LIBS%dompdf\dompdf\tests\ /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyfontAwesome
::--------------------------------------------------------

:fnCopyfontAwesome
ECHO  === font-awesome ===
ECHO	COPY TO %LIBS%font-awesome\
ECHO.
IF NOT EXIST %LIBS%font-awesome\css\ MKDIR %LIBS%font-awesome\css\ >> %LOG%
COPY %VENDOR%fortawesome\font-awesome\css\font-awesome.css.map %LIBS%font-awesome\css\ /Y >> %LOG%
COPY %VENDOR%fortawesome\font-awesome\css\font-awesome.min.css %LIBS%font-awesome\css\ /Y >> %LOG%
IF NOT EXIST %LIBS%font-awesome\fonts\ MKDIR %LIBS%font-awesome\fonts\ >> %LOG%
XCOPY %VENDOR%fortawesome\font-awesome\fonts\*.* %LIBS%font-awesome\fonts\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyJolicode
::--------------------------------------------------------

:fnCopyJolicode

ECHO  === jolicode ===
ECHO	COPY TO %LIBS%jolicode\
ECHO.
XCOPY %VENDOR%jolicode\*.* %LIBS%jolicode\ /E /Y >> %LOG%
IF EXIST %LIBS%jolicode\jolitypo\tests RMDIR %LIBS%jolicode\jolitypo\tests\ /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyNoty
::--------------------------------------------------------

:fnCopyNoty

ECHO  === noty ===
ECHO	COPY TO %LIBS%noty\
ECHO.
IF NOT EXIST %LIBS%noty\ MKDIR %LIBS%noty\ >> %LOG%
COPY %VENDOR%needim\noty\js\noty\packaged\jquery.noty.packaged.min.js %LIBS%noty\jquery.noty.packaged.min.js /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyMonolog
::--------------------------------------------------------

:fnCopyMonolog

ECHO  === monolog ===
ECHO	COPY TO %LIBS%monolog\
ECHO.
XCOPY %VENDOR%monolog\monolog\src\*.* %LIBS%monolog\monolog\src\ /E /Y >> %LOG%
XCOPY %VENDOR%psr\*.* %LIBS%psr\*.* /E /Y >> %LOG%

goto:eof

::--------------------------------------------------------
::-- fnCopyParsedown
::--------------------------------------------------------

:fnCopyParsedown

ECHO  === parsedown ===
ECHO	COPY TO %LIBS%parsedown\
ECHO.
IF NOT EXIST %LIBS%parsedown\ MKDIR %LIBS%parsedown\ >> %LOG%
COPY %VENDOR%erusev\parsedown\Parsedown.php %LIBS%parsedown\ /Y >> %LOG%
COPY %VENDOR%erusev\parsedown-extra\ParsedownExtra.php %LIBS%parsedown\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyParsedownCheckbox
::--------------------------------------------------------

:fnCopyParsedownCheckbox

ECHO  === parsedown-checkbox ===
ECHO	COPY TO %LIBS%leblanc-simon\
ECHO.
IF NOT EXIST %LIBS%leblanc-simon\parsedown-checkbox\ MKDIR %LIBS%leblanc-simon\parsedown-checkbox\ >> %LOG%
COPY %VENDOR%leblanc-simon\parsedown-checkbox\ParsedownCheckbox.php %LIBS%leblanc-simon\parsedown-checkbox\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyMinify
::--------------------------------------------------------

:fnCopyMinify
ECHO  === matthiasmullie ===
ECHO	COPY TO %LIBS%matthiasmullie\
ECHO.
XCOPY %VENDOR%matthiasmullie\*.* %LIBS% /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyGitHubMarkdownCSS
::--------------------------------------------------------

:fnCopyGitHubMarkdownCSS
ECHO  === github-markdown-css ===
ECHO	COPY TO %LIBS%github-markdown-css\
ECHO.
IF NOT EXIST %LIBS%github-markdown-css\ MKDIR %LIBS%github-markdown-css\ >> %LOG%
COPY %VENDOR%github-markdown-css\github-markdown.css %LIBS%github-markdown-css\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyPHPFONT
::--------------------------------------------------------

:fnCopyPHPFONT
ECHO  === PHP-FONT-LIB (used by dompdf) ===
ECHO	COPY TO %LIBS%php-font-lib\
ECHO.
IF NOT EXIST %LIBS%php-font-lib MKDIR %LIBS%php-font-lib >> %LOG%
XCOPY %VENDOR%php-font-lib\*.* %LIBS%php-font-lib /E /Y >> %LOG%
IF EXIST %LIBS%php-font-lib\sample-fonts RMDIR %LIBS%php-font-lib\sample-fonts /S /Q >> %LOG%
IF EXIST %LIBS%php-font-lib\tests RMDIR %LIBS%php-font-lib\tests /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyPHPSVG
::--------------------------------------------------------

:fnCopyPHPSVG
ECHO  === PHP-SVG-LIB (used by dompdf) ===
ECHO	COPY TO %LIBS%php-svg-lib\
ECHO.
IF NOT EXIST %LIBS%php-svg-lib MKDIR %LIBS%php-svg-lib >> %LOG%
XCOPY %VENDOR%php-svg-lib\*.* %LIBS%php-svg-lib /E /Y >> %LOG%
IF EXIST %LIBS%php-svg-lib\tests RMDIR %LIBS%php-svg-lib\tests /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyPHP_error
::--------------------------------------------------------

:fnCopyPHP_error
ECHO  === php_error ===
ECHO	COPY TO %LIBS%php_error\
ECHO.
IF NOT EXIST %LIBS%php_error MKDIR %LIBS%php_error >> %LOG%
COPY %VENDOR%PHP-Error\src\php_error.php %LIBS%php_error\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyTracy
::--------------------------------------------------------

:fnCopyTracy
ECHO  === Tracy ===
ECHO	COPY TO %LIBS%tracy\
ECHO.
IF NOT EXIST %LIBS%tracy MKDIR %LIBS%tracy >> %LOG%
XCOPY %VENDOR%tracy\*.* %LIBS%tracy\ /E /Y >> %LOG%
IF EXIST %LIBS%tracy\tracy\examples RMDIR %LIBS%tracy\tracy\examples /S /Q >> %LOG%
IF EXIST %LIBS%tracy\tracy\tools RMDIR %LIBS%tracy\tracy\tools /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyCrawlerDetect
::--------------------------------------------------------

:fnCopyCrawlerDetect
ECHO  === crawler-detect ===
ECHO	COPY TO %LIBS%Jaybizzle\crawler-detect\src
ECHO.
IF NOT EXIST %LIBS%Jaybizzle\crawler-detect\src MKDIR %LIBS%Jaybizzle\crawler-detect\src >> %LOG%
XCOPY %VENDOR%jaybizzle\crawler-detect\src\*.* %LIBS%Jaybizzle\crawler-detect\src /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyAnimateCSS
::--------------------------------------------------------

:fnCopyAnimateCSS
ECHO  === animate.css
ECHO	COPY TO %LIBS%animate.css
ECHO.
IF NOT EXIST %LIBS%animate.css MKDIR %LIBS%animate.css>> %LOG%
COPY %VENDOR%animate.css\animate.min.css %LIBS%animate.css /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyURLjs
::--------------------------------------------------------

:fnCopyURLjs
ECHO  === js-url ===
ECHO	COPY TO %LIBS%js-url
ECHO.
IF NOT EXIST %LIBS%js-url MKDIR %LIBS%js-url>> %LOG%
COPY %VENDOR%js-url\url.min.js %LIBS%\js-url /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnIonIcons
::--------------------------------------------------------

:fnIonIcons
ECHO  === ionicons ===
ECHO	COPY TO %LIBS%ionicons
ECHO.
IF NOT EXIST %LIBS%ionicons MKDIR %LIBS%ionicons >> %LOG%
XCOPY %VENDOR%ionicons\css\*.css %LIBS%ionicons\css\ /E /Y >> %LOG%
XCOPY %VENDOR%ionicons\fonts\*.* %LIBS%ionicons\fonts\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnAdminLTE
::--------------------------------------------------------

:fnAdminLTE
ECHO  === AdminLTE ===
ECHO	COPY TO %LIBS%AdminLTE
ECHO.
IF NOT EXIST %LIBS%AdminLTE MKDIR %LIBS%AdminLTE >> %LOG%
IF NOT EXIST %LIBS%AdminLTE\css MKDIR %LIBS%AdminLTE\css >> %LOG%
IF NOT EXIST %LIBS%AdminLTE\css\skins MKDIR %LIBS%AdminLTE\css\skins >> %LOG%
IF NOT EXIST %LIBS%AdminLTE\js MKDIR %LIBS%AdminLTE\js >> %LOG%
COPY %VENDOR%AdminLTE\dist\css\AdminLTE.min.css %LIBS%AdminLTE\css\  /Y >> %LOG%
COPY %VENDOR%AdminLTE\dist\css\skins\*.min.css %LIBS%AdminLTE\css\skins\ /Y >> %LOG%
DEL %LIBS%AdminLTE\css\skins\_all-skins.min.css  >> %LOG%
COPY %VENDOR%AdminLTE\dist\js\adminlte.min.js %LIBS%AdminLTE\js\  /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnSlimScroll
::--------------------------------------------------------

:fnSlimScroll
ECHO  === SlimScroll===
ECHO	COPY TO %LIBS%jQuery-slimScroll
ECHO.
IF NOT EXIST %LIBS%jQuery-slimScroll MKDIR %LIBS%jQuery-slimScroll >> %LOG%
COPY %VENDOR%jQuery-slimScroll\jquery.slimscroll.min.js %LIBS%jQuery-slimScroll\  /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnJSONLint
::--------------------------------------------------------

:fnJSONLint
ECHO  === jsonlint===
ECHO	COPY TO %LIBS%jsonlint
ECHO.
IF NOT EXIST %LIBS%jsonlint MKDIR %LIBS%jsonlint >> %LOG%
XCOPY %VENDOR%jsonlint\src\*.php %LIBS%jsonlint\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnFlySystem
::--------------------------------------------------------

:fnFlySystem
ECHO  === flysystem ===
ECHO	COPY TO %LIBS%league/flysystem
ECHO.
IF NOT EXIST %LIBS%league\flysystem MKDIR %LIBS%league\flysystem >> %LOG%
XCOPY %VENDOR%league\flysystem\src\*.php %LIBS%league\flysystem\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnScrollDir
::--------------------------------------------------------

:fnScrollDir
ECHO  === scrolldir ===
ECHO	COPY TO %LIBS%scrolldir
ECHO.
IF NOT EXIST %LIBS%scrolldir MKDIR %LIBS%scrolldir >> %LOG%
COPY %VENDOR%scrolldir\dist\scrolldir.min.js %LIBS%scrolldir\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCSSCheckboxLib
::--------------------------------------------------------

:fnCSSCheckboxLib
ECHO  === CSS-Checkbox-Library ===
ECHO	COPY TO %LIBS%CSS-Checkbox-Library
ECHO.
IF NOT EXIST %LIBS%CSS-Checkbox-Library MKDIR %LIBS%CSS-Checkbox-Library >> %LOG%
COPY %VENDOR%CSS-Checkbox-Library\dist\css\checkboxes.min.css %LIBS%CSS-Checkbox-Library\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnSweetAlert
::--------------------------------------------------------

:fnSweetAlert
ECHO  === SweetAlert ===
ECHO	COPY TO %LIBS%sweetalert2
ECHO.
IF NOT EXIST %LIBS%sweetalert2 MKDIR %LIBS%sweetalert2 >> %LOG%
COPY %VENDOR%sweetalert2\dist\sweetalert2.min.js %LIBS%sweetalert2\ /Y >> %LOG%
COPY %VENDOR%sweetalert2\dist\sweetalert2.min.css %LIBS%sweetalert2\ /Y >> %LOG%
goto:eof

REM -----------------------------------------------
REM -----------------------------------------------
REM --------------- USED IN PLUGINS ---------------
REM -----------------------------------------------
REM -----------------------------------------------

::--------------------------------------------------------
::-- fnCopyDatatables
::--------------------------------------------------------

:fnCopyDatatables
ECHO  === datatables ===
ECHO	COPY TO %PAGE%html\datatables\libs\datatables\
ECHO.
IF NOT EXIST %PAGE%html\datatables\libs\datatables\ MKDIR %PAGE%html\datatables\libs\datatables\ >> %LOG%
XCOPY %VENDOR%datatables\datatables\media\*.* %PAGE%html\datatables\libs\datatables\ /E /Y >> %LOG%
COPY %VENDOR%drmonty\datatables-buttons\js\dataTables.buttons.min.js %PAGE%html\datatables\libs\datatables\js\ /Y >> %LOG%
COPY %VENDOR%drmonty\datatables-buttons\js\buttons.html5.min.js %PAGE%html\datatables\libs\datatables\js\ /Y >> %LOG%
COPY %VENDOR%drmonty\datatables-buttons\css\buttons.dataTables.min.css %PAGE%html\datatables\libs\datatables\css\ /Y >> %LOG%
COPY %VENDOR%drmonty\datatables-buttons\css\buttons.bootstrap.min.css %PAGE%html\datatables\libs\datatables\css\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjsTree
::--------------------------------------------------------

:fnCopyjsTree
ECHO  === jstree ===
ECHO	COPY TO %PAGE%html\treeview\libs\jstree\
ECHO.
IF NOT EXIST %PAGE%html\treeview\libs\jstree\ MKDIR %PAGE%html\treeview\libs\jstree\ >> %LOG%
COPY %VENDOR%vakata\jstree\dist\jstree.min.js %PAGE%html\treeview\libs\jstree\ /Y >> %LOG%
IF NOT EXIST %PAGE%html\treeview\libs\jstree\themes\ MKDIR %PAGE%html\treeview\libs\jstree\themes\ >> %LOG%
XCOPY %VENDOR%vakata\jstree\dist\themes\default\*.* %PAGE%html\treeview\libs\jstree\themes\default\ /E /Y >> %LOG%
COPY %VENDOR%vakata\jstree\demo\filebrowser\file_sprite.png %PAGE%html\treeview\libs\jstree\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjsTreeProton
::--------------------------------------------------------

:fnCopyjsTreeProton
ECHO  === jstreeProton ===
ECHO	COPY TO %PAGE%html\treeview\libs\jstree\themes
ECHO.
IF NOT EXIST %PAGE%html\treeview\libs\jstree\themes\ MKDIR %PAGE%html\treeview\libs\jstree\themes\ >> %LOG%
XCOPY %VENDOR%jstree-bootstrap-theme\dist\themes\proton\*.* %PAGE%html\treeview\libs\jstree\themes\proton\ /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopySimpleMDE
::--------------------------------------------------------

:fnCopySimpleMDE
ECHO  === simplemde-markdown-editor ===
ECHO	COPY TO %PAGE%html\editor\libs\simplemde-markdown-editor\
ECHO.
IF NOT EXIST %PAGE%html\editor\libs\simplemde-markdown-editor\ MKDIR %PAGE%html\editor\libs\simplemde-markdown-editor\ >> %LOG%
COPY %VENDOR%simplemde-markdown-editor\dist\simplemde.min.css %PAGE%html\editor\libs\simplemde-markdown-editor\ /Y >> %LOG%
COPY %VENDOR%simplemde-markdown-editor\dist\simplemde.min.js %PAGE%html\editor\libs\simplemde-markdown-editor\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyPrism
::--------------------------------------------------------

:fnCopyPrism
ECHO  === prism ===
ECHO	COPY TO %PAGE%html\prism\libs\prism\
ECHO.
IF NOT EXIST %PAGE%html\prism\libs\prism\ MKDIR %PAGE%html\prism\libs\prism\ >> %LOG%
COPY %VENDOR_MANUAL%prism\prism.css %PAGE%html\prism\libs\prism\ /Y >> %LOG%
COPY %VENDOR_MANUAL%prism\prism.js %PAGE%html\prism\libs\prism\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyFlexDataList
::--------------------------------------------------------

:fnCopyFlexDataList
ECHO  === jquery-flexdatalist ===
ECHO	COPY TO %PAGE%html\search\libs\jquery-flexdatalist\
ECHO.
IF NOT EXIST %PAGE%html\search\libs\jquery-flexdatalist\ MKDIR %PAGE%html\search\libs\jquery-flexdatalist\ >> %LOG%
COPY %VENDOR%jquery-flexdatalist\jquery.flexdatalist.min.css %PAGE%html\search\libs\jquery-flexdatalist\ /Y >> %LOG%
COPY %VENDOR%jquery-flexdatalist\jquery.flexdatalist.min.js %PAGE%html\search\libs\jquery-flexdatalist\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjQueryHighLight
::--------------------------------------------------------

:fnCopyjQueryHighLight
ECHO  === jquery-highlight ===
ECHO	COPY TO %PAGE%html\search\libs\jquery-highlight\
ECHO.
IF NOT EXIST %PAGE%html\search\libs\jquery-highlight\ MKDIR %PAGE%html\search\libs\jquery-highlight\ >> %LOG%
COPY %VENDOR%jquery-highlight\jquery.highlight.js %PAGE%html\search\libs\jquery-highlight\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyPrintPreview
::--------------------------------------------------------

:fnCopyPrintPreview
ECHO  === print-preview ===
ECHO	COPY TO %PAGE%html\print_preview\libs\\printThis\
ECHO.
IF NOT EXIST %PAGE%html\printThis\libs\print_preview\ MKDIR %PAGE%html\print_preview\libs\printThis\ >> %LOG%
COPY %VENDOR%printThis\printThis.js %PAGE%html\print_preview\libs\printThis\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyLinkify
::--------------------------------------------------------

:fnCopyLinkify
ECHO  === linkify ===
ECHO	COPY TO %PAGE%html\linkify\libs\linkify\
ECHO.
IF NOT EXIST %PAGE%html\linkify\libs\linkify\ MKDIR %PAGE%html\linkify\libs\linkify\ >> %LOG%
COPY %VENDOR%linkify-shim\linkify.min.js %PAGE%html\linkify\libs\linkify\ /Y >> %LOG%
COPY %VENDOR%linkify-shim\linkify-jquery.min.js %PAGE%html\linkify\libs\linkify\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyStoreJS
::--------------------------------------------------------

:fnCopyStoreJS
ECHO  === store-js ===
ECHO	COPY TO %PAGE%html\optimize\libs\store-js\
ECHO.
IF NOT EXIST %PAGE%html\optimize\libs\store-js\ MKDIR %PAGE%html\optimize\libs\store-js\ >> %LOG%
COPY %VENDOR%store.js\dist\store.everything.min.js %PAGE%html\optimize\libs\store-js\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyLazySize
::--------------------------------------------------------

:fnCopyLazySizes
ECHO  === lazysizes ===
ECHO	COPY TO %PAGE%html\optimize\libs\lazysizes\
ECHO.
IF NOT EXIST %PAGE%html\optimize\libs\lazysizes\ MKDIR %PAGE%html\optimize\libs\lazysizes\ >> %LOG%
COPY %VENDOR%lazysizes\lazysizes.min.js %PAGE%html\optimize\libs\lazysizes\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyAnchor
::--------------------------------------------------------

:fnCopyAnchor
ECHO  === anchor.js ===
ECHO	COPY TO %PAGE%html\anchor\libs\anchor-js\
ECHO.
IF NOT EXIST %PAGE%html\anchor\libs\anchor-js\ MKDIR %PAGE%html\anchor\libs\anchor-js\ >> %LOG%
COPY %VENDOR%anchorjs\anchor.min.js %PAGE%html\anchor\libs\anchor-js\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyClipboardJS
::--------------------------------------------------------

:fnCopyClipboardJS
ECHO  === clipboard.js ===
ECHO	COPY TO %PAGE%html\clipboard\libs\clipboard-js\
ECHO.
IF NOT EXIST %PAGE%html\clipboard\libs\clipboard-js\ MKDIR %PAGE%html\clipboard\libs\clipboard-js\ >> %LOG%
COPY %VENDOR%clipboard.js\dist\clipboard.min.js %PAGE%html\clipboard\libs\clipboard-js\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyTimeline
::--------------------------------------------------------

:fnCopyTimeline
ECHO  === jquery-albe-timeline ===
ECHO	COPY TO %TASK%timeline\libs\jquery-albe-timeline\
ECHO.
IF NOT EXIST %TASK%timeline\libs\jquery-albe-timeline\ MKDIR %TASK%timeline\libs\jquery-albe-timeline\ >> %LOG%
COPY %VENDOR%jquery-albe-timeline\jquery-albe-timeline.min.js %TASK%timeline\libs\jquery-albe-timeline\ /Y  >> %LOG%
REM >> %LOG%
COPY %VENDOR%jquery-albe-timeline\templates\vertical\style-albe-timeline.css %TASK%timeline\libs\jquery-albe-timeline\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyFakeLoader
::--------------------------------------------------------

:fnCopyFakeLoader
ECHO  === fakeLoader ===
ECHO	COPY TO %PAGE%html\fakeLoader\libs\fakeLoader.js\
ECHO.
IF NOT EXIST %PAGE%html\fakeLoader\libs\fakeLoader.js\ MKDIR %PAGE%html\fakeLoader\libs\fakeLoader.js\ >> %LOG%
COPY %VENDOR%fakeLoader.js\fakeLoader.min.js %PAGE%html\fakeLoader\libs\fakeLoader.js\ /Y >> %LOG%
COPY %VENDOR%fakeLoader.js\fakeLoader.css %PAGE%html\fakeLoader\libs\fakeLoader.js\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyEmoji
::--------------------------------------------------------

:fnCopyEmoji
ECHO  === emoji ===
ECHO	COPY TO %PAGE%markdown\emoji\libs\litemoji\
ECHO.
IF NOT EXIST %MARKDOWN%emoji\libs\litemoji\ MKDIR %MARKDOWN%emoji\libs\litemoji\ >> %LOG%
COPY %VENDOR%litemoji\src\LitEmoji.php %MARKDOWN%emoji\libs\litemoji\ /Y >> %LOG%
COPY %VENDOR%litemoji\src\shortcodes-array.php %MARKDOWN%emoji\libs\litemoji\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyGoogoose
::--------------------------------------------------------

:fnCopyGoogoose
ECHO  === Googoose ===
ECHO	COPY TO %PAGE%html\docx\libs\googoose\
ECHO.
IF NOT EXIST %PAGE%html\docx\libs\googoose\ MKDIR %PAGE%html\docx\libs\googoose\ >> %LOG%
COPY %VENDOR%googoose\jquery.googoose.js %PAGE%html\docx\libs\googoose\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyReveajJS
::--------------------------------------------------------

:fnCopyReveajJS
ECHO  === Reveal.js ===
ECHO	COPY TO %PAGE%html\reveal\libs\reveal.js\
ECHO.
IF NOT EXIST %PAGE%html\reveal\libs\reveal.js MKDIR %PAGE%html\reveal\libs\reveal.js >> %LOG%
XCOPY %VENDOR%reveal.js\*.* %PAGE%html\reveal\libs\reveal.js\ /E /Y >> %LOG%
IF EXIST %PAGE%html\reveal\libs\reveal.js\.git RMDIR %PAGE%html\reveal\libs\reveal.js\.git /S /Q >> %LOG%
IF EXIST %PAGE%html\reveal\libs\reveal.js\test RMDIR %PAGE%html\reveal\libs\reveal.js\test /S /Q >> %LOG%
IF EXIST %PAGE%html\reveal\libs\reveal.js\demo.html DEL %PAGE%html\reveal\libs\reveal.js\demo.html /S /Q >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyReveajJS-Menu
::--------------------------------------------------------

:fnCopyReveajJS-Menu
ECHO  === reveal.js-menu ===
ECHO	COPY TO %PAGE%html\reveal\libs\reveal.js\plugin
ECHO.
IF NOT EXIST %PAGE%html\reveal\libs\reveal.js\plugin\reveal.js-menu\ MKDIR %PAGE%html\reveal\libs\reveal.js\plugin\reveal.js-menu\ >> %LOG%
COPY %VENDOR%reveal.js-menu\menu.css %PAGE%html\reveal\libs\reveal.js\plugin\reveal.js-menu\ /Y >> %LOG%
COPY %VENDOR%reveal.js-menu\menu.js %PAGE%html\reveal\libs\reveal.js\plugin\reveal.js-menu\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyReveajJS-ElapsedTimeBar
::--------------------------------------------------------

:fnCopyReveajJS-ElapsedTimeBar
ECHO  === reveal.js-ElapsedTimeBar ===
ECHO	COPY TO %PAGE%html\reveal\libs\reveal.js\plugin
ECHO.
IF NOT EXIST %PAGE%html\reveal\libs\reveal.js\plugin\elapsed-time-bar\ MKDIR %PAGE%html\reveal\libs\reveal.js\plugin\elapsed-time-bar\ >> %LOG%
COPY %VENDOR%reveal.js-elapsed-time-bar\plugin\elapsed-time-bar\elapsed-time-bar.js %PAGE%html\reveal\libs\reveal.js\plugin\elapsed-time-bar\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyReveajJS-TitleFooter
::--------------------------------------------------------

:fnCopyReveajJS-TitleFooter
ECHO  === reveal.js-TitleFooter ===
ECHO	COPY TO %PAGE%html\reveal\libs\reveal.js\plugin
ECHO.
IF NOT EXIST %PAGE%html\reveal\libs\reveal.js\plugin\title-footer\ MKDIR %PAGE%html\reveal\libs\reveal.js\plugin\title-footer\ >> %LOG%
COPY %VENDOR%Reveal.js-Title-Footer\plugin\title-footer\title-footer.js %PAGE%html\reveal\libs\reveal.js\plugin\title-footer\ /Y >> %LOG%
COPY %VENDOR%Reveal.js-Title-Footer\plugin\title-footer\title-footer.css %PAGE%html\reveal\libs\reveal.js\plugin\title-footer\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyRemark
::--------------------------------------------------------

:fnCopyRemark

ECHO  === Remark ===
ECHO	COPY TO %PAGE%html\remark\libs\remark\
ECHO.
IF NOT EXIST %PAGE%html\remark\libs\remark MKDIR %PAGE%html\remark\libs\remark >> %LOG%
COPY %MANUAL%remark\remark.min.js %PAGE%html\remark\libs\remark /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyBalloon
::--------------------------------------------------------

:fnCopyBalloon
ECHO  === Balloon ===
ECHO	COPY TO %PAGE%html\balloon\libs\
ECHO.
IF NOT EXIST %PAGE%html\balloon\libs\ MKDIR %PAGE%html\balloon\libs\ >> %LOG%
COPY %VENDOR%balloon.css\balloon.min.css %PAGE%html\balloon\libs\ /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyjqueryi18n
::--------------------------------------------------------

:fnCopyjqueryi18n
ECHO  === jquery.i18n ===
ECHO	COPY TO %PAGE%html\i18n\libs\jquery.i18n
ECHO.
IF NOT EXIST %PAGE%html\i18n\libs\jquery.i18n MKDIR %PAGE%html\i18n\libs\jquery.i18n\ >> %LOG%
XCOPY %VENDOR%jquery.i18n\src\*.* %PAGE%html\i18n\libs\jquery.i18n /E /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyCLDRPluralRuleParser
::--------------------------------------------------------

:fnCopyCLDRPluralRuleParser
ECHO  === CLDRPluralRuleParser ===
ECHO	COPY TO %PAGE%html\i18n\libs\CLDRPluralRuleParser
ECHO.
IF NOT EXIST %PAGE%html\i18n\libs\CLDRPluralRuleParser MKDIR %PAGE%html\i18n\libs\CLDRPluralRuleParser >> %LOG%
COPY %VENDOR%CLDRPluralRuleParser\src\CLDRPluralRuleParser.js %PAGE%html\i18n\libs\CLDRPluralRuleParser /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnCopyFileSaver
::--------------------------------------------------------

:fnCopyFileSaver
ECHO  === FileSaver ===
ECHO	COPY TO %PAGE%html\txt\libs\FileSaver.js
ECHO.
IF NOT EXIST %PAGE%html\txt\libs\FileSaver.js MKDIR %PAGE%html\txt\libs\FileSaver.js >> %LOG%
COPY %VENDOR%FileSaver.js\FileSaver.min.js %PAGE%html\txt\libs\FileSaver.js /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnGitHubCorners
::--------------------------------------------------------

:fnGitHubCorners
ECHO  === fnGitHubCorners ===
ECHO	COPY TO %PAGE%html\github_banner\libs\github-corners
ECHO.
IF NOT EXIST %PAGE%html\github_banner\libs\github-corners MKDIR %PAGE%html\github_banner\libs\github-corners >> %LOG%
COPY %VENDOR%github-corners\css\styles.css %PAGE%html\github_banner\libs\github-corners /Y >> %LOG%
goto:eof

::--------------------------------------------------------
::-- fnUpload
::--------------------------------------------------------

:fnUpload
ECHO  === fnUpload ===
ECHO	COPY TO %PAGE%html\upload\libs\dropzone
ECHO.
IF NOT EXIST %PAGE%html\upload\libs\dropzone MKDIR %PAGE%html\upload\libs\dropzone >> %LOG%
COPY %VENDOR%dropzone\dist\min\dropzone.min.css %PAGE%html\upload\libs\dropzone /Y >> %LOG%
COPY %VENDOR%dropzone\dist\min\dropzone.min.js %PAGE%html\upload\libs\dropzone /Y >> %LOG%

::--------------------------------------------------------
::-- fnHTML2MD
::--------------------------------------------------------

:fnHTML2MD
ECHO  === fnHTML2MD ===
ECHO	COPY TO %TASK%convert\libs\html2md
ECHO.
IF NOT EXIST %TASK%convert\libs\html2md MKDIR %TASK%convert\libs\html2md >> %LOG%
XCOPY %VENDOR%html-to-markdown\src\*.* %TASK%convert\libs\html2md /E /Y >> %LOG%

::--------------------------------------------------------
::-- fnGuzzle
::--------------------------------------------------------

:fnGuzzle
ECHO  === fnGuzzle ===
ECHO	COPY TO %TASK%fetch\libs\guzzle
ECHO.
IF NOT EXIST %TASK%fetch\libs\guzzle MKDIR %TASK%fetch\libs\guzzle >> %LOG%
XCOPY %VENDOR%guzzlehttp\guzzle\src\*.* %TASK%fetch\libs\guzzle /E /Y >> %LOG%

goto:eof

::--------------------------------------------------------
::-- fnGoogleTranslate
::--------------------------------------------------------

:fnGoogleTranslate
ECHO  === fnGoogleTranslate ===
ECHO	COPY TO %TASK%translate\libs\google-translate-php
ECHO.
IF NOT EXIST %TASK%translate\libs\google-translate-php MKDIR %TASK%translate\libs\google-translate-php >> %LOG%
XCOPY %VENDOR%stichoza\google-translate-php\src\Stichoza\GoogleTranslate\*.* %TASK%translate\libs\google-translate-php /E /Y >> %LOG%

goto:eof

::--------------------------------------------------------
::-- fnhtmLawed
::--------------------------------------------------------

:fnhtmLawed
ECHO  === fnhtmLawed ===
ECHO	COPY TO %TASK%htmlindent\libs\
ECHO.
IF NOT EXIST %TASK%htmlindent\libs MKDIR %TASK%htmlindent\libs\ >> %LOG%
COPY %VENDOR%htmlawed\src\htmLawed\htmLawed.php %TASK%htmlindent\libs\htmLawed.php /Y >> %LOG%

goto:eof
REM -----------------------------------------------
REM -----------------------------------------------
REM ---------------- FINALIZATION -----------------
REM -----------------------------------------------
REM -----------------------------------------------

::--------------------------------------------------------
::-- fnFinalize
::--------------------------------------------------------

:fnFinalize
ECHO Kill %LIBS%*.exe files because GitHub will complain about binary files in the repository
DEL %LIBS%*.exe /Q /S >> %LOG%
goto:eof

REM -- OLD --
REM -- OLD --
REM -- OLD --
REM -- OLD --
REM -- OLD --
REM -- OLD --

REM ----------------------------------------------------------------------
REM SET LIB=yaml\
REM IF EXIST %VENDOR%symfony\%LIB% (
REM	ECHO  === %LIB% === >> %LOG%
REM	ECHO  === %LIB% ===
REM	XCOPY %VENDOR%symfony\%LIB%*.* %LIBS%symfony\%LIB%*.* /E /Y >> %LOG%
REM )

REM ----------------------------------------------------------------------
REM SET LIB=psr\
REM IF EXIST %VENDOR%%LIB% (
REM	ECHO  === %LIB% === >> %LOG%
REM	ECHO  === %LIB% ===
REM	XCOPY %VENDOR%%LIB%*.* %LIBS%%LIB%*.* /E /Y >> %LOG%
REM )

:END
ECHO.
ECHO End