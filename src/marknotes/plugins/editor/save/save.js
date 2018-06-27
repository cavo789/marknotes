	// Add a custom button for saving then content of the editor
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->save->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Save',
			className: 'MN_button fa fa-floppy-o tui-save',
			event: 'fnPluginEditButtonSaveClicked',
			tooltip: $.i18n('button_save')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonSaveClicked');

	editor.eventManager.listen('fnPluginEditButtonSaveClicked', function () {

		if (filename === undefined) {
			Noty({
				message: $.i18n('error_filename_missing'),
				type: 'error'
			});
			return false;
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Save');
		}
		/*<!-- endbuild -->*/

		// If LocalStorage is enabled, remove the old saved note since we've
		// just modify it.
		var $useStore = (typeof store === 'object');
		if ($useStore && (typeof fnPluginTaskOptimizeStore_Remove === 'function')) {
			fnPluginTaskOptimizeStore_Remove(filename);
		}

		var $data = {};
		$data.task = 'task.edit.save';
		$data.param = filename;
		$data.markdown = window.btoa(encodeURIComponent(JSON.stringify(editor.getMarkdown())));

		$.ajax({
			async: true,
			// GET can't be used because note's content can be
			// too big for URLs
			type: 'POST',
			url: marknotes.url,
			data: $data,
			datatype: 'json',
			success: function (data) {
				Noty({
					message: data.message,
					type: (data.status == 1 ? 'success' : 'error')
				});

				// Set the current edited content (that was just saved),
				// in the sourceMarkdown textarea so we can compare
				// that version with the edited one when exiting the form
				// and show a warning in case of difference(in exit.js plugin)
				$('#sourceMarkDown').text(editor.getMarkdown());

				var $useStore = (typeof store === 'object');
				if ($useStore) {
					// Be sure the localStorage array is up-to-date and willn't
					// contains the previous content
					fnPluginTaskOptimizeStore_Remove({
						"name": filename
					});
				}
			}
		}); // $.ajax()

	});
