<!DOCTYPE HTML>
<html lang="%LANGUAGE%">

    <head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
		<meta name="author" content="marknotes | Notes management" />
		<meta name="designer" content="marknotes | Notes management" />
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

		<link rel="apple-touch-icon" sizes="57x57" href="%ROOT%/assets/images/favicons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="%ROOT%/assets/images/favicons/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="%ROOT%/assets/images/favicons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="%ROOT%/assets/images/favicons/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="%ROOT%/assets/images/favicons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="%ROOT%/assets/images/favicons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="%ROOT%/assets/images/favicons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="%ROOT%/assets/images/favicons/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="%ROOT%/assets/images/favicons/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-36x36.png" sizes="36x36">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-48x48.png" sizes="48x48">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-72x72.png" sizes="72x72">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-96x96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-144x144.png" sizes="144x144">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/android-icon-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="%ROOT%/assets/images/favicons/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="%ROOT%/assets/images/favicons/manifest.json">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-TileImage" content="%ROOT%/assets/images/favicons/mstile-144x144.png">
		<meta name="theme-color" content="#ffffff">

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

        <script>
            var marknotes={};
            marknotes.root='%ROOT%/';
        </script>

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/reveal/js/reveal.js"></script>
        <script src="%ROOT%/libs/reveal/lib/js/head.min.js"></script>
        <script src="%ROOT%/templates/assets/js/marknotes.js"></script>

		<!--%ADDITIONNAL_JS%-->

    </body>

</html>
