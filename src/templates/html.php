<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-language" content="%LANGUAGE%" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%SITE_NAME%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/libs/bootstrap/css/bootstrap.min.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/html.css" />

		<!--%ADDITIONNAL_CSS%-->

	</head>

	<body>

		<div class="container">
			<article id="top">
				%CONTENT%
			</article>
		</div>

        <div id="footer">
            <a href="%VERSION_PDF%" title="%VERSION_PDF_TITLE%"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
            <a href="%VERSION_SLIDESHOW%" title="%VERSION_SLIDESHOW_TITLE%"><i class="fa fa-desktop" aria-hidden="true"></i></a>
        </div>

		<script type="text/javascript" src="%ROOT%/libs/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="%ROOT%/libs/lazysizes/lazysizes.min.js"></script>
		<script type="text/javascript" src="%ROOT%/templates/assets/js/html.js"></script>
        <script type="text/javascript" src="%ROOT%/assets/js/marknotes.js"></script>

	</body>

	<script type="text/javascript" >
		var marknotes = {};
		marknotes.arrPluginsFct = [];
		marknotes.message = {};
		marknotes.plugins = {};
		marknotes.querystring = {};
		marknotes.settings = {};
		marknotes.settings.debug='%DEBUG%';
		marknotes.settings.language='%LANGUAGE%';
		marknotes.webroot='%ROOT%/';
	</script>

	<!--%ADDITIONNAL_JS%-->

	<!--%MARKDOWN_GLOBAL_VARIABLES%-->

	<script type="text/javascript" >
		$(document).ready(function () {
			runPluginsFunctions();

			// When displaying the html rendering, the user can add a ?fullscreen=1 on the
			// querystring to give more width to the article

			if(typeof marknotes.querystring.fullscreen !== 'undefined'){

				// Typecast fullscreen, only '1' should be interpreted
				var $isFullscreen = (marknotes.querystring.fullscreen === '1');

				if ($isFullscreen) {
					$('body').css('padding','0');
					$('article').css('max-width','100%').css('margin','0');
					$('.container').css('width','100%').css('margin','0').css('padding','0');
				}
			}
		});
	</script>

</html>
