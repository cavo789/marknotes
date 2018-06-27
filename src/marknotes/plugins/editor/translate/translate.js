	// Add a custom button that will call the translate task and
	// get the translated content
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->translate->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Translate',
			className: 'MN_button fa fa-book tui-translate',
			event: 'fnPluginEditButtonTranslateClicked',
			tooltip: $.i18n('button_translate')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonTranslateClicked');

	editor.eventManager.listen('fnPluginEditButtonTranslateClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Translate');
		}
		/*<!-- endbuild -->*/

		swal({
			title: $.i18n('editor_translate_alert_title'),
			type: 'question',
			input: 'select',
			inputOptions: marknotes.editor.language_to,
			inputPlaceholder:  $.i18n('editor_translate_select'),
			inputValidator: function (value) {
				return new Promise(function (resolve, reject) {
					if (value !== '') {
						// value contains the selected language
						// for instance "en" or "fr"
						var $data = {};
						$data.task = 'task.translate.run';
						$data.param = value;
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

						resolve();
					} else {
						reject($.i18n('error_please_select_a_value'));
					}
				})
			},
			showCancelButton: true,
			cancelButtonText: $.i18n('cancel'),
			confirmButtonText: $.i18n('editor_translate_doit')
		});

		return true;
	});
