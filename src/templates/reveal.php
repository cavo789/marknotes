<!DOCTYPE HTML>
<html lang="%LANGUAGE%">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<title>%NOTE_TITLE%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<!--%ADDITIONNAL_CSS%-->

		<link rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/marknotes.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/reveal.css" media="screen" id="theme">
		
		<!-- Reveal.js Title-footer plugin -->
		<link rel="stylesheet" type="text/css" href="%ROOT%/marknotes/plugins/page/html/reveal/libs/reveal.js/plugin/title-footer/title-footer.css" media="screen" />

	</head>
	<body>
		<div class="reveal">

			<div class="logo">
				<img src="%ROOT%templates/assets/images/logo-marknotes.svg" alt="marknotes" height="97" align="right" />
			</div>

			<div class="slides" id="note_content">
				%CONTENT%
			</div>
			
			<!-- Title-footer plugin -->
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
			marknotes.root='%ROOT%';
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
		</script>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>

		<!--%ADDITIONNAL_JS%-->

		<script src="%ROOT%templates/assets/js/marknotes.js" defer="defer"></script>

	</body>
</html>
