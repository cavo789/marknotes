<!DOCTYPE html>
<html lang="%LANGUAGE%">

   <head>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="robots" content="%ROBOTS%" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%APP_NAME%</title>

        <!--%META_DATA%-->

        <!--%META_CACHE%-->

        <!--%FONT%-->

        <link media="screen" rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/font-awesome/css/font-awesome.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/print-preview/print-preview.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-flexdatalist/jquery.flexdatalist.min.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="assets/css/marknotes_screen.css" />
        <link media="screen" rel="stylesheet" type="text/css" href="libs/jsTree/themes/default/style.min.css" />

        <link media="screen" rel="stylesheet" type="text/css" href="libs/jquery-toolbar/jquery.toolbar.css" />

        <link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/assets/css/login.css" />
        <link media="print" rel="stylesheet" type="text/css" media="print" href="assets/css/marknotes_print.css">

        <!--%ADDITIONNAL_CSS%-->

   </head>

   <body>
        <div id="login-box" class="login-popup">
            <a href="#" class="close"><img src="assets/images/close_pop.png" class="btn_close" title="Close Window" alt="Close" /></a>
            <form method="post" class="signin" action="#">
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
			<br/>
            <a href="https://github.com/cavo789/marknotes" target="_blank" title="Download Marknotes on GitHub"><img src="assets/images/marknotes.png" class="logo onlyscreen" /></a>

            <div id="toolbar-app" data-toolbar="style-option" class="onlyscreen btn-toolbar btn-toolbar-default"><i class="fa fa-cog"></i></div>
            <div id="toolbar-app-options" class="hidden btn-toolbar-warning">
                <div id="icons" class="onlyscreen fa-1x">
					%ICONS%
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

	  <script>var $arrPluginsFct = [];</script>

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

      <!-- For highligthing content in a note : after a search, the displayed note will have the search term highlighted -->
      <script type="text/javascript" src="libs/jquery.highlight.js/jquery.highlight.js"></script>

      <!-- jsTree -->
      <script type="text/javascript" src="libs/jsTree/jstree.min.js"></script>

      <!-- jquery-toolbar -->
      <script type="text/javascript" src="libs/jquery-toolbar/jquery.toolbar.min.js"></script>

      <!-- Store.js -->
      <script src="libs/store/store.everything.min.js"></script>

      <!--%ADDITIONNAL_JS%-->

      <!--%MARKDOWN_GLOBAL_VARIABLES%-->
      <script type="text/javascript" src="assets/js/jstree.js"></script>
      <script type="text/javascript" src="assets/js/fullscreen.js"></script>
      <script type="text/javascript" src="assets/js/marknotes.js"></script>

      <script>initializeTasks();</script>

   </body>
</html>
