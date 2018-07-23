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

	<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/font-awesome/css/font-awesome.min.css" </head>

	<body>

		<div class="container">
			<h1>Search optimization - Put in the cache your tags</h1>
			<input id="doit" type="button" value="Run ajax requests" />
			<hr/> %CONTENT%
		</div>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>

		<script>

			/**
			 * Process <li> one by one; the function will call itself until the
			 * last <li>.
			 */
			function execTask(li) {

				if (!li.length) return; // no more item, exit

				// get the keyword to search (f.i. "marknotes")
				var $keyword = li.text();

				// prepare the url : the URL to search.php has been set in the data-url
				// attribute. Don't fire plugins.
				var $url = li.data('url') + '&disable_plugins=1';

				$.ajax({
					beforeSend: function () {
						// Add a spinner to the <li>
						li.html($keyword + '&nbsp;<i class="fa fa-spinner fa-spin"></i>');
					},
					cache: true,
					type: 'GET',
					url: $url,
					datatype: 'json',
					success: function (data) {

						// Once the ajax request has been made i.e. once the search has been
						// successfully made and stored in the cache, get the number of files found
						var $wItems = JSON.parse(data.files).length;

						// And prepare a URL for the user for re-running the search; using the cache
						// this time
						var $see = '<a href="' + $url + '" target="_blank">' + $wItems + ' results</a>';

						// Update the <li> text
						li.html($keyword + '&nbsp;<i class="fa fa-check"></i> (' + $see + ')');

						// Then call the next one until the last
						execTask(li.next());
					}
				});

				return true;

			}

			$(document).ready(function () {

				// By clicking on the doit button, process each <li> one by one,
				// search for the first keyword (li:first)
				$("#doit").click(function () {

					// Process the first item
					execTask($('#tags li:first'));
				});

			});
		</script>

	</body>

</html>
