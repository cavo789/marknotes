<!DOCTYPE HTML>
<html lang="%LANGUAGE%">

	<head>

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<!--%META_DATA%-->

		<link href="%ROOT%/templates/jd18fr/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />

		<title>%SITE_NAME%</title>

		<link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/reveal.css" media="screen" />
		
		<link rel="stylesheet" type="text/css" href="%ROOT%/marknotes/plugins/page/html/reveal/libs/reveal.js/plugin/title-footer/title-footer.css" media="screen" />

		<link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/marknotes.css" media="screen" />

		<!--%ADDITIONNAL_CSS%-->
		<link rel="stylesheet" type="text/css" href="%ROOT%/templates/jd18fr/jd18fr.css" media="screen" id="theme">
		
	</head>

	<body>

		<div class="reveal">

			<div class="logo">
				<img src="%ROOT%/templates/jd18fr/joomladay-logo.svg" alt="JDFR 2018" height="100" width="140" />
			</div>

			<div class="slides" id="note_content">
				%CONTENT%
			</div>
			
			<footer id="title-footer"><p id="notification"></p></footer>
			
		</div>

		<aside class="controls">
			<a class="left" href="#">&#x25C4;</a>
			<a class="right" href="#">&#x25BA;</a>
			<a class="up" href="#">&#x25B2;</a>
			<a class="down" href="#">&#x25BC;</a>
		</aside>

		<script>
			var marknotes = {};
			marknotes.settings = {};
			marknotes.arrPluginsFct = [];
			marknotes.root='%ROOT%/';
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
		</script>

		<!--%ADDITIONNAL_JS%-->

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>
		<script src="%ROOT%/templates/assets/js/marknotes.js"></script>

	</body>

</html>
