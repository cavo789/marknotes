<!DOCTYPE HTML>
<html lang="fr-fr" dir="ltr">

    <head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
		<meta name="author" content="MarkNotes | Notes management" />
		<meta name="designer" content="MarkNotes | Notes management" />
		<meta name="keywords" content="%TITLE%" />
		<meta name="description" content="%TITLE%" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta property="og:url" content="%URL_PAGE%" />
		<meta property="og:type" content="article" />
		<meta property="og:image" content="%URL_IMG%" />
		<meta property="og:image:width" content="1200" />
		<meta property="og:image:height" content="522" />
		<meta property="og:title" content="%TITLE%" />
		<meta property="og:site_name" content="%SITE_NAME%" />
		<meta property="og:description" content="%TITLE%" />

        <title>%TITLE%</title>

        <link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/reveal.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/theme/beige.css" id="theme" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/reveal.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/marknotes.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/lib/css/zenburn.css" media="screen" >

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

        <script>
            var marknotes={};
            marknotes.root='%ROOT%/';
        </script>

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/reveal/js/reveal.js"></script>
        <script src="%ROOT%/libs/reveal/lib/js/head.min.js"></script>
        <script src="%ROOT%/templates/assets/marknotes.js"></script>

    </body>

</html>
