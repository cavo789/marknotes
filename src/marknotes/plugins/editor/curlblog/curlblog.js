	// Add a custom button that will retrieve the HTML of an article on the web
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->curlblog->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_CurlBlog',
			className: 'MN_button fa fa-download tui-curlblog',
			event: 'fnPluginEditButtonCurlBlogClicked',
			tooltip: $.i18n('button_curlBlog')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonCurlBlogClicked');

	editor.eventManager.listen('fnPluginEditButtonCurlBlogClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - CurlBlog');
		}
		/*<!-- endbuild -->*/

		// Call the "task.fetch.gethtml" task and specify an URL
			// A cURL action will be fired and try to retrieve the HTML content
		// of that page
		var $default_url = '';

		swal({
			html:
				'<label>'+$.i18n('fetch_specify_url')+'</label>'+
				'<input id="swal-fetch-url" class="swal2-input" '+ 'value="'+$default_url+'">',
			onOpen: function () {
				$('#swal-fetch-url').focus()
			}
		}).then(function (result) {
			$url = $('#swal-fetch-url').val();

			if (($url != null) && ($url != $default_url)) {

				// Start the ajax request and fetch the HTML of that page.
				// The gethtml task will also clean the code and only
				// keep content's HTML and not footer, navigation, comments,
				// ...
				var $data = {};
				$data.task = 'task.fetch.gethtml';
				$data.param = filename;
				$data.url = $url;

				$.ajax({
					async: true,
					type: 'GET',
					url: marknotes.url,
					data: $data,
					datatype: 'html',
					success: function (data) {
						editor.setValue(data);
					}
				}); // $.ajax()
			}
		}).catch(swal.noop);

		return true;
	});
