<!DOCTYPE html>
<html lang="%LANGUAGE%">

   <head>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="robots" content="%ROBOTS%" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-language" content="%LANGUAGE%" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%APP_NAME%</title>

        <!--%META_DATA%-->
		<!--%FAVICON%-->

        <!--%META_CACHE%-->

        <!--%FONT%-->

        <link media="screen" rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/font-awesome/css/font-awesome.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/print-preview/print-preview.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-flexdatalist/jquery.flexdatalist.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="assets/css/marknotes_screen.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jsTree/themes/default/style.css" />

        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-toolbar/jquery.toolbar.css" />

        <link media="print" rel="stylesheet" type="text/css" media="print" href="assets/css/marknotes_print.css">

        <!--%ADDITIONNAL_CSS%-->

   </head>

   <body>

	   <!--%LOGIN%-->

      <div>

        <div class="onlyscreen" id="TDM">
            <a href="https://github.com/cavo789/marknotes" target="_blank" title="Download Marknotes on GitHub"><img src="assets/images/marknotes.png" class="logo" /></a>

            <div id="toolbar-app" data-toolbar="style-option" class="btn-toolbar btn-toolbar-default"><i class="fa fa-cog"></i></div>
            <div id="toolbar-app-options" class="hidden btn-toolbar-warning">
                <div id="icons" class="fa-1x">
					%ICONS%
                    <a id="icon_settings_clear" data-task="clear" title="%CLEAR_CACHE%" href="#">
                        <i class="fa fa-eraser" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <input id='search' name='search' type='text' class='flexdatalist' placeholder='%EDT_SEARCH_PLACEHOLDER%'
               alt=""accesskey=""accept=""maxlength='%EDT_SEARCH_MAXLENGTH%' data-data='index.php?task=tags' data-search-in='name'
               data-visible-properties='["name","type"]' multiple='multiple' />

            <div id="TOC">&nbsp;</div>

            <div class="app_version"><a href="%APP_WEBSITE%" target="_blank" title="%APP_NAME% | Download a newer version">%APP_VERSION%</a></div>

         </div>

         <div class="content">
            <page size="A4" layout="portrait" class="" id="CONTENT">&nbsp;</page>
         </div>

      </div>

      <footer class="onlyprint">&nbsp;</footer>

      <!-- Add libraries. Thank you to these developpers! -->
      <script type="text/javascript" src="libs/jquery/jquery.min.js"></script>
      <script type="text/javascript" src="libs/bootstrap/js/bootstrap.min.js"></script>

      <!-- Used by the search box, for auto-completion -->
      <script type="text/javascript" src="libs/jquery-flexdatalist/jquery.flexdatalist.min.js"></script>

      <!-- For the Print preview button -->
      <script type="text/javascript" src="libs/print-preview/jquery.print-preview.js"></script>

      <!-- For nice user alerts (informations, warning, ...) -->
      <script type="text/javascript" src="libs/noty/jquery.noty.packaged.min.js"></script>

      <!-- For highligthing content in a note : after a search, the displayed note will have the search term highlighted -->
      <script type="text/javascript" src="libs/jquery.highlight.js/jquery.highlight.js"></script>

      <!-- jsTree -->
      <script type="text/javascript" src="libs/jsTree/jstree.min.js"></script>

      <!-- jquery-toolbar -->
      <script type="text/javascript" src="libs/jquery-toolbar/jquery.toolbar.min.js"></script>

      <!-- Store.js -->
      <script src="libs/store/store.everything.min.js"></script>

      <script type="text/javascript">
         var marknotes = {};
		 marknotes.arrPluginsFct = [];
         marknotes.message = {};
         marknotes.plugins = {};
		 marknotes.settings = {};
		 marknotes.settings.debug='%DEBUG%';
		 marknotes.settings.language='%LANGUAGE%';
		 marknotes.treeview = {};
		 marknotes.docs='%DOCS%';
		 marknotes.webroot='%ROOT%/';
      </script>

      <!--%ADDITIONNAL_JS%-->

      <!--%MARKDOWN_GLOBAL_VARIABLES%-->
      <script type="text/javascript" src="assets/js/jstree.js"></script>
      <script type="text/javascript" src="assets/js/fullscreen.js"></script>
      <script type="text/javascript" src="assets/js/marknotes.js"></script>

      <script type="text/javascript">initializeTasks();</script>

   </body>
</html>
