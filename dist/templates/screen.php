/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.2
* @author    : christophe@aesecure.com
* @copyright : MIT (c) 2016 - 2017
* @url       : https://github.com/cavo789/markdown#readme
* @package   : 2017-01-27T17:44:12.406Z
 */
<!DOCTYPE html>
<html lang="en">

   <head>
      
      <meta charset="utf-8"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta name="robots" content="noindex, nofollow" />
      <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" /> 
      <!--%META_CACHE%-->

      <title>%APP_NAME%</title>

      <link href="data:image/x-icon;base64,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" rel="shortcut icon" type="image/vnd.microsoft.icon"/>  

      <!--%FONT%-->

      <link media="screen" rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css" />
      <link media="screen" rel="stylesheet" type="text/css" href="libs/font-awesome/css/font-awesome.min.css" />
      <link media="screen" rel="stylesheet" type="text/css" href="libs/print-preview/print-preview.css" />
      <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-flexdatalist/jquery.flexdatalist.min.css" />
      <link media="screen" rel="stylesheet" type="text/css" href="assets/css/markdown_screen.php?imgMaxWidth=%IMG_MAXWIDTH%" />
      <link media="screen" rel="stylesheet" type="text/css" href="libs/jsTree/themes/default/style.min.css" />
      <link media="screen" rel="stylesheet" type="text/css" href="libs/DataTables/css/dataTables.bootstrap4.min.css"/>
      <link media="screen" rel="stylesheet" type="text/css" href="libs/simplemde/simplemde.min.css">

      <link media="screen" rel="stylesheet" type="text/css" href="libs/prism/prism.css"/>
      <link media="print" rel="stylesheet" type="text/css" media="print" href="assets/css/markdown_print.php?appName=%APP_NAME_64%">

      <!--%CUSTOM_CSS%-->

   </head>

   <body>

      <div class="row">

         <div class="col-sm-4 onlyscreen" id="TDM">

            <input id='search' name='search' type='text' class='flexdatalist' placeholder='%EDT_SEARCH_PLACEHOLDER%'
               alt=""accesskey=""accept=""maxlength='%EDT_SEARCH_MAXLENGTH%' data-data='index.php?task=tags' data-search-in='name'
               data-visible-properties='["name","type"]' multiple='multiple' />

            <div id="TOC" class="onlyscreen">&nbsp;</div>
            
            <div class="app_version"><a href="https://github.com/cavo789/markdown" target="_blank" title="%APP_NAME% | Download a newer version">%APP_VERSION%</a></div>

         </div>

         <div class="col-sm-8">
            <page size="A4" layout="portrait" class="container col-md-8" id="CONTENT">&nbsp;</page>
            <img class="visible-lg" id="IMG_BACKGROUND" src="assets/background.jpg"/>
         </div>

      </div>

      <footer class="onlyprint">&nbsp;</footer>

      <!-- Add libraries. Thank you to these developpers! -->
      <script type="text/javascript" src="libs/jquery/jquery.min.js"></script>
      <script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>

      <!-- Used by the search box, for auto-completion -->
      <script type="text/javascript" src="libs/jquery-flexdatalist/jquery.flexdatalist.min.js"></script>

      <!-- Needed for the "Copy note hyperlink" button, to make easier to copy the link of a note in an another one -->
      <script type="text/javascript" src="libs/clipboard/clipboard.min.js"></script>

      <!-- For the Print preview button -->
      <script type="text/javascript" src="libs/print-preview/jquery.print-preview.js"></script>

      <!-- For nice user alerts (informations, warning, ...) -->
      <script type="text/javascript" src="libs/noty/jquery.noty.packaged.min.js"></script>

      <!-- For converting plain text (emails, urls, ...) into links -->
      <script type="text/javascript" src="libs/linkify/linkify.min.js"></script>
      <script type="text/javascript" src="libs/linkify/linkify-jquery.min.js"></script>

      <!-- For highligthing content in a note : after a search, the displayed note will have the search term highlighted -->

      <script type="text/javascript" src="libs/jquery.highlight.js/jquery.highlight.js"></script>

      <!-- In notes, where there are lines of code (html, javascript, vb, ...), these lines will be colorized thanks to Prism -->
      <script type="text/javascript" src="libs/prism/prism.js" data-manual></script>

      <!-- jsTree -->
      <script type="text/javascript" src="libs/jsTree/jstree.min.js"></script>

      <!-- dataTables -->
      <script type="text/javascript" src="libs/DataTables/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="libs/DataTables/js/dataTables.bootstrap4.min.js"></script>
      
      <!-- jsPDF -->
      <script type="text/javascript" src="libs/jsPDF/jspdf.debug.js"></script>
      
      <!-- Simple Markup Editor -->
      <script src="libs/simplemde/simplemde.min.js"></script>

      <!--%ADDITIONNAL_JS%-->
      
      <script type="text/javascript">%MARKDOWN_GLOBAL_VARIABLES%</script>
      <script type="text/javascript" src="assets/js/markdown.js"></script> 
      
      <!--%CUSTOM_JS%-->
      
   </body>
</html>   