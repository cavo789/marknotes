<!DOCTYPE html>
<html lang="%LANGUAGE%">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<title>%NOTE_TITLE%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/remark.css" media="screen" />

		<!--%ADDITIONNAL_CSS%-->

	</head>
	<body>

		<textarea id="note_content" readonly="readonly">%CONTENT%</textarea>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>
		<script src="%ROOT%marknotes/plugins/page/html/remark/libs/remark/remark.min.js"></script>
		<script>
			var hljs = remark.highlighter.engine

			var slideshow = remark.create({
	  			source: $('#note_content').html(),
				highlightStyle: 'monokai'
			});
		</script>

		<script>
			var marknotes = {};
			marknotes.settings = {};
			marknotes.arrPluginsFct = [];
			marknotes.root='%ROOT%';
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
		</script>

		<!--%ADDITIONNAL_JS%-->

	</body>

</html>
