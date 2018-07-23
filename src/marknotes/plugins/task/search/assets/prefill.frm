<!DOCTYPE html>
<html>

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<title>Search optimization - Put in the cache your tags</title>

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/bootstrap/css/bootstrap.min.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/github-markdown-css/github-markdown.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/html.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/menu.css">

	</head>

	<body>

		<div class="container">
			<h1>Search optimization - Put in the cache your tags</h1>
			<input id="doit" type="button" value="Run ajax requests"/>
			<hr/>
			%CONTENT%

		</div>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>

		<script>
			$(document).ready(function () {
 				$("#doit").click(function() {
					$("#tags li").each(function(index) {
						url =  $(this).data('url') + '&disable_plugins=1';
						console.log(index + ": " +url);
opt=$(this);
						$.ajax({
							beforeSend: function () {
								opt.text(opt.text() + " --- running");

							},
							cache: false,
							type: 'GET',
							url: url,
							datatype: 'json',
							success: function (data) {
								opt.text(opt.text() + ' === SUCCESS');
							}
						});
					});
				});

			});
		</script>

	</body>
</html>
