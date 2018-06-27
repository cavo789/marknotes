	// Add a custom button for saving then content of the editor
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->preview->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Preview',
			className: 'MN_button fa fa-eye tui-preview',
			event: 'fnPluginEditButtonPreviewClicked',
			tooltip: $.i18n('button_preview')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonPreviewClicked');

	editor.eventManager.listen('fnPluginEditButtonPreviewClicked', function () {

		if (filename === undefined) {
			Noty({
				message: $.i18n('error_filename_missing'),
				type: 'error'
			});
			return false;
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Preview');
		}
		/*<!-- endbuild -->*/

		var $data = {};
		$data.task = 'task.edit.preview';
		$data.markdown = window.btoa(encodeURIComponent(JSON.stringify(editor.getMarkdown())));

		$.ajax({
			async: true,
			// GET can't be used because note's content can be
			// too big for URLs
type: 'GET',
			url: marknotes.url,
			data: $data,
			datatype: 'json',
			success: function (data) {
alert('PREVIEW OK');
			}
		}); // $.ajax()

	});
