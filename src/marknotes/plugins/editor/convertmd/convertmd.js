	// Add a custom button that will convert the content (HTML) to markdown
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->convertmd->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_ConvertMD',
			className: 'MN_button fa fa-flash tui-convertmd',
			event: 'fnPluginEditButtonConvertMDClicked',
			tooltip: $.i18n('button_convertMD')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonConvertMDClicked');

	editor.eventManager.listen('fnPluginEditButtonConvertMDClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - ConvertMD');
		}
		/*<!-- endbuild -->*/

		// Call the task "task.convert.fromHTML" so the content of the
		// editor can be converted (best try) to a markdown string
		var $data = {};
		$data.task = 'task.convert.fromHTML';
		$data.content = editor.getValue();

		$.ajax({
			async: true,
			type: 'POST',
			url: marknotes.url,
			data: $data,
			datatype: 'html',
			success: function (data) {
				editor.setValue(data);
			}
		}); // $.ajax()

		return true;
	});
