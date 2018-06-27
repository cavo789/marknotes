	// Add a custom button that will insert a <encrypt> tag in
	// the content of the editor. If a text was selected, that text
	// will be put inside the <encrypt> tag
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->encrypt->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Encrypt',
			className: 'MN_button fa fa-user-secret tui-encrypt',
			event: 'fnPluginEditButtonEncryptClicked',
			tooltip: $.i18n('button_encrypt')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonEncryptClicked');

	editor.eventManager.listen('fnPluginEditButtonEncryptClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Encrypt');
		}
		/*<!-- endbuild -->*/

		var selectedText = editor.getSelectedText();

		var text = selectedText || $.i18n('confidential');

		editor.insertText('<encrypt>' + text + '</encrypt>');

		return true;
	});
