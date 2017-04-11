<?php
// @codingStandardsIgnoreFile
?>
<!DOCTYPE html>
<html lang="en">

   <head>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="robots" content="noindex, nofollow" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

        <meta name="author" content="MarkNotes | Notes management" />
        <meta name="designer" content="MarkNotes | Notes management" />
        <meta name="keywords" content="markdown, markotes, html, slideshow, knowledge management" />
        <meta name="description" content="MarkNotes is a knowledge management tool.  Notes are written in the Markdown language and then displayed as full functionnal html page.  Can be displayed as a slideshow also." />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta property="og:type" content="article" />
        <meta property="og:image" content="%ROOT%/assets/images/notes.jpg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="800" />
        <meta property="og:title" content="MarkNotes | Notes management" />
        <meta property="og:site_name" content="MarkNotes | Notes management" />
        <meta property="og:description" content="MarkNotes | Notes management" />

        <!--%META_CACHE%-->

        <title>%APP_NAME%</title>

		<link rel="apple-touch-icon" sizes="57x57" href="assets/images/favicons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="assets/images/favicons/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="assets/images/favicons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="assets/images/favicons/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="assets/images/favicons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="assets/images/favicons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="assets/images/favicons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="assets/images/favicons/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicons/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="assets/images/favicons/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="assets/images/favicons/android-icon-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="assets/images/favicons/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="assets/images/favicons/manifest.json">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-TileImage" content="assets/images/favicons/mstile-144x144.png">
		<meta name="theme-color" content="#ffffff">

        <!--%FONT%-->

        <link media="screen" rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/font-awesome/css/font-awesome.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/print-preview/print-preview.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-flexdatalist/jquery.flexdatalist.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="assets/css/marknotes_screen.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jsTree/themes/default/style.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/DataTables/css/dataTables.bootstrap4.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/simplemde/simplemde.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-toolbar/jquery.toolbar.css" />

        <link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/assets/css/login.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/prism/prism.css"/>
        <link media="print" rel="stylesheet" type="text/css" media="print" href="assets/css/marknotes_print.css">

        <!--%CUSTOM_CSS%-->

   </head>

   <body>

        <div id="login-box" class="login-popup">
            <a href="#" class="close"><img src="assets/images/close_pop.png" class="btn_close" title="Close Window" alt="Close" /></a>
            <form method="post" class="signin" action="index.php?action=login">
                <fieldset class="textbox">
                    <label class="username">
                    <span>%LOGIN%</span>
                    <input id="username" name="username" value="" type="text" autocomplete="on" placeholder="Username">
                </label>
                <label class="password">
                    <span>%PASSWORD%</span>
                    <input id="password" name="password" value="" type="password" placeholder="Password">
                </label>
                <button class="submit button" type="button">%SIGNIN%</button>
            </fieldset>
            </form>
        </div>

      <div class="row">

        <div class="col-sm-3 onlyscreen" id="TDM">
            <a href="https://github.com/cavo789/marknotes" target="_blank" title="Download Marknotes on GitHub"><img src="assets/images/marknotes.png" class="logo onlyscreen" /></a>

            <div id="toolbar-app" data-toolbar="style-option" class="onlyscreen btn-toolbar btn-toolbar-default"><i class="fa fa-cog"></i></div>
            <div id="toolbar-app-options" class="hidden btn-toolbar-warning">
                <div id="icons" class="onlyscreen fa-1x">
                    <a id="icon_login" data-task="login" title="%LOGINFORM%" href="#">
                        <i class="fa fa-user" aria-hidden="true"></i>
                    </a>
                    <a id="icon_sitemap" data-task="sitemap" title="%SITEMAP%" href="#">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </a>
                    <a id="icon_timeline" data-task="timeline" title="%TIMELINE%" href="#">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                    </a>
                    <a id="icon_settings_clear" data-task="clear" title="%CLEAR_CACHE%" href="#">
                        <i class="fa fa-eraser" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <input id='search' name='search' type='text' class='flexdatalist' placeholder='%EDT_SEARCH_PLACEHOLDER%'
               alt=""accesskey=""accept=""maxlength='%EDT_SEARCH_MAXLENGTH%' data-data='index.php?task=tags' data-search-in='name'
               data-visible-properties='["name","type"]' multiple='multiple' />

            <div id="TOC" class="onlyscreen">&nbsp;</div>

            <div class="app_version"><a href="%APP_WEBSITE%" target="_blank" title="%APP_NAME% | Download a newer version">%APP_VERSION%</a></div>

         </div>

         <div class="col-sm-9">
            <page size="A4" layout="portrait" class="container col-md-8" id="CONTENT">&nbsp;</page>
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

      <!-- Lazysizes (lazyload of images) -->
      <script type="text/javascript" src="libs/lazysizes/lazysizes.min.js"></script>

      <!-- jsTree -->
      <script type="text/javascript" src="libs/jsTree/jstree.min.js"></script>

      <!-- jquery-toolbar -->
      <script type="text/javascript" src="libs/jquery-toolbar/jquery.toolbar.min.js"></script>

      <!-- dataTables -->
      <script type="text/javascript" src="libs/DataTables/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="libs/DataTables/js/dataTables.bootstrap4.min.js"></script>

      <!-- jsPDF -->
      <script type="text/javascript" src="libs/jsPDF/jspdf.debug.js"></script>

      <!-- Simple Markup Editor -->
      <script src="libs/simplemde/simplemde.min.js"></script>

      <!-- Store.js -->
      <script src="libs/store/store.everything.min.js"></script>

      <!--%ADDITIONNAL_JS%-->

      <!--%MARKDOWN_GLOBAL_VARIABLES%-->
      <script type="text/javascript" src="assets/js/jstree.js"></script>
      <script type="text/javascript" src="assets/js/fullscreen.js"></script>
      <script type="text/javascript" src="assets/js/marknotes.js"></script>

      <!--%CUSTOM_JS%-->

   </body>
</html>
