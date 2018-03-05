<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<title>%TITLE%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/bootstrap/css/bootstrap.min.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/github-markdown-css/github-markdown.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/html.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/menu.css">

		<!--%ADDITIONNAL_CSS%-->

	</head>

	<body>

		<div class="container">
			<article id="CONTENT" class="markdown-body">
				%CONTENT%
			</article>
		</div>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>
		<script src="%ROOT%templates/assets/js/html.js"></script>
		<script src="%ROOT%assets/js/marknotes.js"></script>
		<script src="%ROOT%assets/js/ajaxify.js" defer="defer"></script>
		<script src="%ROOT%templates/assets/js/menu.js" defer="defer"></script>

		<script>
			var marknotes = {};
			marknotes.arrPluginsFct = [];
			marknotes.note = {};
			marknotes.note.url = '%ROOT%%DOCS%/%FILENAME%.html';
			marknotes.note.basename = '%BASENAME%';
			marknotes.plugins = {};
			marknotes.settings = {};
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
			marknotes.webroot='%ROOT%';
		</script>

		<!--%ADDITIONNAL_JS%-->

		<script>
			$(document).ready(function () {
				runPluginsFunctions();

				try {
					// Check on the querystring is 'fullscreen' has been specified
					// and if so, check if fullscreen=1
					if (typeof url('?fullscreen') !== "undefined") {
						$fullscreen = (url('?fullscreen') == '1');

						if ($fullscreen) {
							$('body').css('padding','0');
							$('article').css('max-width','100%').css('margin','0');
							$('.container').css('width','100%').css('margin','0').css('padding','0');
						}
					}

				} catch (err) {
					console.warn(err.message);
				}

			});
		</script>

	</body>
</html>
