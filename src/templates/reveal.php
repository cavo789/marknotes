<!DOCTYPE HTML>
<html lang="%LANGUAGE%">

    <head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%SITE_NAME%</title>

		<!--%META_DATA%-->

        <link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/reveal.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/theme/beige.css" id="theme" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/reveal.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/marknotes.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/lib/css/zenburn.css" media="screen" >

		<!--%ADDITIONNAL_CSS%-->

    </head>

    <body>

        <div class="reveal">
            <div class="slides">
			    %CONTENT%
            </div>
        </div>

        <div id="footer">
            <a href="%VERSION_PDF%" title="%VERSION_PDF_TITLE%"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
            <a href="%VERSION_HTML%" title="%VERSION_HTML_TITLE%"><i class="fa fa-book" aria-hidden="true"></i></a>
        </div>

        <aside class="controls">
            <a class="left" href="#">&#x25C4;</a>
            <a class="right" href="#">&#x25BA;</a>
            <a class="up" href="#">&#x25B2;</a>
            <a class="down" href="#">&#x25BC;</a>
        </aside>

        <script type="text/javascript">
		    var marknotes = {};
		    marknotes.arrPluginsFct = [];
            marknotes.root='%ROOT%/';
        </script>

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/reveal/js/reveal.js"></script>
        <script src="%ROOT%/libs/reveal/lib/js/head.min.js"></script>
        <script src="%ROOT%/templates/assets/js/marknotes.js"></script>

		<!--%ADDITIONNAL_JS%-->

    </body>

</html>
