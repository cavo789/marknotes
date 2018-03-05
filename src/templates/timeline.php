<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<title>%SITE_NAME%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<!--%ADDITIONNAL_CSS%-->

		<link rel="stylesheet" href="%ROOT%/marknotes/plugins/task/timeline/libs/jquery-albe-timeline/style-albe-timeline.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%/templates/assets/css/ajax_loading.css" />

		<style>
		.lds-css {
			background-color: #EFEFEF !important;
		}
		</style>

	</head>

	<body>

		<h1 data-i18n="app_name">%SITE_NAME%</h1>

		<div id="divTimeline">&nbsp;</div>

		<script src="%ROOT%/libs/jquery/jquery.min.js"></script>

		<script>
		var marknotes = {};
		marknotes.arrPluginsFct = [];
		marknotes.url='index.php';
		marknotes.settings = {};
		marknotes.settings.debug = '%DEBUG%';
		marknotes.settings.language='%LANGUAGE%';
		marknotes.webroot='%ROOT%/';
		</script>

		<!--%ADDITIONNAL_JS%-->

		<script src="%ROOT%/assets/js/ajaxify.js" defer="defer"></script>
		<script src="%ROOT%/marknotes/plugins/task/timeline/libs/jquery-albe-timeline/jquery-albe-timeline.min.js" defer="defer"></script>
		<script src="%ROOT%/assets/js/marknotes.js" defer="defer"></script>
		<script src="%ROOT%/marknotes/plugins/task/timeline/timeline.js" defer="defer"></script>

	</body>

</html>
