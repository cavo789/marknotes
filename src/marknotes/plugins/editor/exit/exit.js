	// Add a custom button to exit the editor
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->exit->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Exit',
			className: 'MN_button fa fa-sign-out tui-exit',
			event: 'fnPluginEditButtonExitClicked',
			tooltip: $.i18n('button_exit_edit_mode')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonExitClicked');

	editor.eventManager.listen('fnPluginEditButtonExitClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Exit');
		}
		/*<!-- endbuild -->*/

		var $old = $('#sourceMarkDown').text();
		var $new = editor.getMarkdown();

		if ($old !== $new) {
			swal({
				title: $.i18n('are_you_sure'),
				text: $.i18n('button_exit_not_save'),
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: $.i18n('ok'),
				cancelButtonText: $.i18n('cancel'),
				confirmButtonClass: 'btn btn-success',
				cancelButtonClass: 'btn btn-danger',
				buttonsStyling: false,
				reverseButtons: false
				}).then((result) => {
					if (result.value) {
						$('#editorMarkDown').parent().hide();

						ajaxify({
							task: 'task.export.html',
							param: filename,
							callback: 'afterDisplay($data.param)',
							target: 'CONTENT'
						});
					}
				}
			);

		} else { // if ($old !== $new) {

			// The content of the editor isn't different
			// than the previous, saved, version so we can
			// exit without warning
			$('#editorMarkDown').parent().hide();

			ajaxify({
				task: 'task.export.html',
				param: filename,
				callback: 'afterDisplay($data.param)',
				target: 'CONTENT'
			});
		}

	});
