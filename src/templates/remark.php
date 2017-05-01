<!DOCTYPE html>
<html lang="%LANGUAGE%">
    <head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%SITE_NAME%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/remark.css" media="screen" />

		<!--%ADDITIONNAL_CSS%-->

    </head>
    <body>

        <textarea id="note_content" readonly="readonly">%CONTENT%</textarea>

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/remark/remark-latest.min.js"></script>

		<script type="text/javascript">

			var hljs = remark.highlighter.engine

			var slideshow = remark.create({
	  			source: $('#note_content').html(),
				highlightStyle: 'monokai'
			});
		</script>

		<script type="text/javascript">
		    var marknotes = {};
			marknotes.settings = {};
		    marknotes.arrPluginsFct = [];
            marknotes.root='%ROOT%/';
	   		marknotes.settings.debug='%DEBUG%';
	   		marknotes.settings.language='%LANGUAGE%';
		</script>

		<!--%ADDITIONNAL_JS%-->

    </body>

</html>
